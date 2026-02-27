<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Services\HeyGenService;
use App\Services\CloudStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class GenerateHeyGenVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     * HeyGen videos can take several minutes to generate.
     */
    public int $timeout = 1800; // 30 minutes

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    protected Lesson $lesson;

    /**
     * Create a new job instance.
     */
    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
        $this->onConnection('database');
        $this->onQueue('heygen');
    }

    /**
     * Execute the job.
     */
    public function handle(HeyGenService $heygenService): void
    {
        Log::info('HeyGen: Starting video generation', ['lesson_id' => $this->lesson->id]);

        try {
            // Validate required fields
            if (empty($this->lesson->heygen_script)) {
                throw new Exception('No script provided for HeyGen video');
            }

            if (empty($this->lesson->heygen_avatar_id)) {
                throw new Exception('No avatar selected for HeyGen video');
            }

            if (empty($this->lesson->heygen_voice_id)) {
                throw new Exception('No voice selected for HeyGen video');
            }

            // Check if we already have a video_id (resuming from previous attempt)
            if ($this->lesson->heygen_video_id && $this->lesson->heygen_status === 'processing') {
                // Just poll for status
                $this->pollForCompletion($heygenService);
                return;
            }

            // Update status to processing
            $this->lesson->update([
                'heygen_status' => 'processing',
                'heygen_started_at' => now(),
                'heygen_error' => null,
            ]);

            // Generate video
            $result = $heygenService->generateVideo(
                $this->lesson->heygen_script,
                $this->lesson->heygen_avatar_id,
                $this->lesson->heygen_voice_id,
                [
                    'dimension' => [
                        'width' => 1920,
                        'height' => 1080,
                    ],
                    'test' => $this->lesson->heygen_test_mode ?? false,
                ]
            );

            // Save video_id
            $this->lesson->update([
                'heygen_video_id' => $result['video_id'],
            ]);

            Log::info('HeyGen: Video generation started', [
                'lesson_id' => $this->lesson->id,
                'video_id' => $result['video_id'],
            ]);

            // Poll for completion
            $this->pollForCompletion($heygenService);

        } catch (Exception $e) {
            Log::error('HeyGen: Video generation failed', [
                'lesson_id' => $this->lesson->id,
                'error' => $e->getMessage(),
            ]);

            $this->lesson->update([
                'heygen_status' => 'failed',
                'heygen_error' => $e->getMessage(),
                'heygen_completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Poll HeyGen API for video completion.
     */
    protected function pollForCompletion(HeyGenService $heygenService): void
    {
        $maxAttempts = 120; // 120 * 15 seconds = 30 minutes max
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;

            $status = $heygenService->getVideoStatus($this->lesson->heygen_video_id);

            Log::info('HeyGen: Polling status', [
                'lesson_id' => $this->lesson->id,
                'attempt' => $attempt,
                'status' => $status['status'],
            ]);

            switch ($status['status']) {
                case 'completed':
                    $this->handleVideoCompleted($status);
                    return;

                case 'failed':
                    throw new Exception($status['error'] ?? 'Video generation failed');

                case 'processing':
                case 'pending':
                    // Wait 15 seconds before next poll
                    sleep(15);
                    break;

                default:
                    Log::warning('HeyGen: Unknown status', [
                        'lesson_id' => $this->lesson->id,
                        'status' => $status['status'],
                    ]);
                    sleep(15);
            }
        }

        throw new Exception('Video generation timed out after 30 minutes');
    }

    /**
     * Handle completed video - download and store.
     */
    protected function handleVideoCompleted(array $status): void
    {
        Log::info('HeyGen: Video completed, downloading', [
            'lesson_id' => $this->lesson->id,
            'video_url' => $status['video_url'],
        ]);

        // Download video from HeyGen
        $videoUrl = $status['video_url'];
        $response = Http::timeout(300)->get($videoUrl);

        if ($response->failed()) {
            throw new Exception('Failed to download video from HeyGen');
        }

        $videoContent = $response->body();

        // Generate filename
        $filename = 'heygen_' . $this->lesson->id . '_' . Str::random(8) . '.mp4';
        $storagePath = 'lessons/videos/' . $filename;

        // Store video
        $storageService = new CloudStorageService();

        if ($storageService->isEnabled()) {
            // Store to cloud (S3/GCS)
            Storage::disk('s3')->put($storagePath, $videoContent);
            $storageDisk = 's3';
        } else {
            // Store locally
            Storage::disk('public')->put($storagePath, $videoContent);
            $storageDisk = 'public';
        }

        // Update lesson with video path
        $this->lesson->update([
            'video_path' => $storagePath,
            'video_filename' => $filename,
            'storage_disk' => $storageDisk,
            'heygen_status' => 'completed',
            'heygen_completed_at' => now(),
            'duration_minutes' => $status['duration'] ? ceil($status['duration'] / 60) : null,
        ]);

        Log::info('HeyGen: Video saved successfully', [
            'lesson_id' => $this->lesson->id,
            'path' => $storagePath,
            'duration' => $status['duration'],
        ]);

        // Dispatch transcoding job if auto-transcode is enabled
        if (config('transcoding.auto_transcode', true)) {
            $this->lesson->update([
                'original_video_path' => $storagePath,
                'transcoding_status' => 'pending',
                'transcoding_progress' => 0,
            ]);

            TranscodeVideoJob::dispatch($this->lesson->fresh());

            Log::info('HeyGen: Transcoding job dispatched', [
                'lesson_id' => $this->lesson->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('HeyGen: Job permanently failed', [
            'lesson_id' => $this->lesson->id,
            'error' => $exception->getMessage(),
        ]);

        $this->lesson->update([
            'heygen_status' => 'failed',
            'heygen_error' => $exception->getMessage(),
            'heygen_completed_at' => now(),
        ]);
    }
}
