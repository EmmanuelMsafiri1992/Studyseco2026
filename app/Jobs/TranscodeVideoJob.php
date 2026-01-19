<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Models\VideoQuality;
use App\Services\VideoTranscodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class TranscodeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries;

    /**
     * The maximum number of seconds the job should run.
     */
    public int $timeout;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff;

    protected Lesson $lesson;

    /**
     * Create a new job instance.
     */
    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
        $this->tries = config('transcoding.processing.retry_attempts', 3);
        $this->timeout = config('transcoding.processing.timeout', 3600);
        $this->backoff = config('transcoding.processing.retry_delay', 60);

        // Set the queue connection and name from config
        $this->onConnection(config('transcoding.queue.connection', 'database'));
        $this->onQueue(config('transcoding.queue.name', 'transcoding'));
    }

    /**
     * Execute the job.
     */
    public function handle(VideoTranscodingService $transcodingService): void
    {
        Log::info("Starting video transcoding for lesson", ['lesson_id' => $this->lesson->id]);

        try {
            // Check if FFmpeg is available
            if (!$transcodingService->isFFmpegAvailable()) {
                throw new Exception("FFmpeg is not available on this system");
            }

            // Get source video path
            $sourcePath = $this->lesson->original_video_path ?? $this->lesson->video_path;

            if (!$sourcePath) {
                throw new Exception("No video path found for lesson");
            }

            // Update lesson status
            $this->lesson->update([
                'transcoding_status' => 'processing',
                'transcoding_started_at' => now(),
                'transcoding_progress' => 0,
            ]);

            // Get video information
            $videoInfo = $transcodingService->getVideoInfo($sourcePath);

            Log::info("Video info retrieved", [
                'lesson_id' => $this->lesson->id,
                'video_info' => $videoInfo,
            ]);

            // Update lesson with source video information
            $this->lesson->update([
                'source_width' => $videoInfo['width'],
                'source_height' => $videoInfo['height'],
                'source_bitrate' => $videoInfo['bitrate'],
                'duration_seconds' => $videoInfo['duration_seconds'],
                'duration_minutes' => max(1, ceil($videoInfo['duration'] / 60)),
            ]);

            // Determine which qualities to generate
            $qualitiesToGenerate = $transcodingService->determineQualitiesToGenerate(
                $videoInfo['width'],
                $videoInfo['height']
            );

            if (empty($qualitiesToGenerate)) {
                Log::warning("No qualities to generate for lesson", [
                    'lesson_id' => $this->lesson->id,
                    'source_resolution' => "{$videoInfo['width']}x{$videoInfo['height']}",
                ]);
                $this->lesson->update([
                    'transcoding_status' => 'completed',
                    'transcoding_completed_at' => now(),
                    'transcoding_progress' => 100,
                ]);
                return;
            }

            // Create quality records
            $transcodingService->createQualityRecords($this->lesson, $qualitiesToGenerate);

            // Process each quality
            $totalQualities = count($qualitiesToGenerate);
            $processedQualities = 0;

            foreach ($qualitiesToGenerate as $qualityName => $settings) {
                try {
                    $this->processQuality(
                        $transcodingService,
                        $sourcePath,
                        $qualityName,
                        $settings
                    );
                    $processedQualities++;
                } catch (Exception $e) {
                    Log::error("Failed to process quality", [
                        'lesson_id' => $this->lesson->id,
                        'quality' => $qualityName,
                        'error' => $e->getMessage(),
                    ]);

                    // Mark quality as failed
                    $quality = VideoQuality::where('lesson_id', $this->lesson->id)
                        ->where('quality', $qualityName)
                        ->first();

                    if ($quality) {
                        $quality->markAsFailed($e->getMessage());
                    }
                }

                // Update overall progress
                $progress = intval(($processedQualities / $totalQualities) * 100);
                $this->lesson->update(['transcoding_progress' => $progress]);
            }

            // Generate master playlist if at least one quality succeeded
            $completedQualities = $this->lesson->videoQualities()->where('status', 'completed')->count();

            if ($completedQualities > 0) {
                try {
                    $masterPlaylistPath = $transcodingService->generateMasterPlaylist($this->lesson);
                    $this->lesson->update(['master_playlist_path' => $masterPlaylistPath]);
                } catch (Exception $e) {
                    Log::error("Failed to generate master playlist", [
                        'lesson_id' => $this->lesson->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Determine final status
            $failedQualities = $this->lesson->videoQualities()->where('status', 'failed')->count();
            $finalStatus = $failedQualities === $totalQualities ? 'failed' : 'completed';

            $this->lesson->update([
                'transcoding_status' => $finalStatus,
                'transcoding_completed_at' => now(),
                'transcoding_progress' => 100,
            ]);

            Log::info("Video transcoding completed", [
                'lesson_id' => $this->lesson->id,
                'status' => $finalStatus,
                'completed_qualities' => $completedQualities,
                'failed_qualities' => $failedQualities,
            ]);

        } catch (Exception $e) {
            Log::error("Video transcoding job failed", [
                'lesson_id' => $this->lesson->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->lesson->update([
                'transcoding_status' => 'failed',
                'transcoding_completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Process a single quality variant.
     */
    protected function processQuality(
        VideoTranscodingService $transcodingService,
        string $sourcePath,
        string $qualityName,
        array $settings
    ): void {
        Log::info("Processing quality", [
            'lesson_id' => $this->lesson->id,
            'quality' => $qualityName,
        ]);

        // Get quality record
        $quality = VideoQuality::where('lesson_id', $this->lesson->id)
            ->where('quality', $qualityName)
            ->first();

        if (!$quality) {
            throw new Exception("Quality record not found for {$qualityName}");
        }

        // Mark as processing
        $quality->markAsProcessing();

        // Determine output directory
        $hlsOutputDir = $transcodingService->getHlsOutputDirectory($this->lesson, $qualityName);

        // Generate HLS playlist and segments
        $result = $transcodingService->generateHlsPlaylist(
            $sourcePath,
            $hlsOutputDir,
            $qualityName,
            $settings
        );

        // Update quality record with results
        $quality->markAsCompleted([
            'hls_playlist_path' => $result['playlist_path'],
            'file_size' => $result['total_size'],
        ]);

        Log::info("Quality processing completed", [
            'lesson_id' => $this->lesson->id,
            'quality' => $qualityName,
            'segments' => $result['segment_count'],
            'size' => $result['total_size'],
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("TranscodeVideoJob permanently failed", [
            'lesson_id' => $this->lesson->id,
            'error' => $exception->getMessage(),
        ]);

        $this->lesson->update([
            'transcoding_status' => 'failed',
            'transcoding_completed_at' => now(),
        ]);

        // Mark all pending qualities as failed
        VideoQuality::where('lesson_id', $this->lesson->id)
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'failed',
                'error_message' => 'Job failed: ' . $exception->getMessage(),
            ]);
    }
}
