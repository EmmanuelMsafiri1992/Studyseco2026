<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\VideoQuality;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class VideoTranscodingService
{
    protected string $ffmpegPath;
    protected string $ffprobePath;
    protected array $qualities;
    protected array $hlsSettings;
    protected array $processingSettings;
    protected array $encodingSettings;

    public function __construct()
    {
        $this->ffmpegPath = config('transcoding.ffmpeg_path', '/usr/bin/ffmpeg');
        $this->ffprobePath = config('transcoding.ffprobe_path', '/usr/bin/ffprobe');
        $this->qualities = config('transcoding.qualities', []);
        $this->hlsSettings = config('transcoding.hls', []);
        $this->processingSettings = config('transcoding.processing', []);
        $this->encodingSettings = config('transcoding.encoding', []);
    }

    /**
     * Get video information using ffprobe.
     */
    public function getVideoInfo(string $videoPath): array
    {
        $fullPath = $this->getFullPath($videoPath);

        if (!file_exists($fullPath)) {
            throw new Exception("Video file not found: {$fullPath}");
        }

        $command = sprintf(
            '"%s" -v quiet -print_format json -show_format -show_streams "%s"',
            $this->ffprobePath,
            $fullPath
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("FFprobe failed with return code: {$returnCode}");
        }

        $jsonOutput = implode("\n", $output);
        $data = json_decode($jsonOutput, true);

        if (!$data) {
            throw new Exception("Failed to parse FFprobe output");
        }

        $videoStream = null;
        $audioStream = null;

        foreach ($data['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video' && !$videoStream) {
                $videoStream = $stream;
            }
            if ($stream['codec_type'] === 'audio' && !$audioStream) {
                $audioStream = $stream;
            }
        }

        if (!$videoStream) {
            throw new Exception("No video stream found in file");
        }

        $duration = floatval($data['format']['duration'] ?? 0);
        $bitrate = intval($data['format']['bit_rate'] ?? 0) / 1000; // Convert to kbps

        return [
            'width' => intval($videoStream['width'] ?? 0),
            'height' => intval($videoStream['height'] ?? 0),
            'duration' => $duration,
            'duration_seconds' => intval($duration),
            'bitrate' => intval($bitrate),
            'codec' => $videoStream['codec_name'] ?? 'unknown',
            'fps' => $this->parseFps($videoStream['r_frame_rate'] ?? '24/1'),
            'has_audio' => $audioStream !== null,
            'file_size' => intval($data['format']['size'] ?? 0),
        ];
    }

    /**
     * Parse frame rate from FFprobe format (e.g., "24/1" or "30000/1001")
     */
    protected function parseFps(string $frameRate): float
    {
        $parts = explode('/', $frameRate);
        if (count($parts) === 2 && $parts[1] > 0) {
            return round($parts[0] / $parts[1], 2);
        }
        return floatval($frameRate) ?: 24;
    }

    /**
     * Determine which qualities to generate based on source resolution.
     */
    public function determineQualitiesToGenerate(int $sourceWidth, int $sourceHeight): array
    {
        $qualitesToGenerate = [];

        foreach ($this->qualities as $qualityName => $settings) {
            // Only generate qualities that are equal to or smaller than source
            if ($settings['height'] <= $sourceHeight) {
                $qualitesToGenerate[$qualityName] = $settings;
            }
        }

        // Always include at least 240p if source is at least 240p
        if (empty($qualitesToGenerate) && $sourceHeight >= 240) {
            $qualitesToGenerate['240p'] = $this->qualities['240p'];
        }

        return $qualitesToGenerate;
    }

    /**
     * Transcode video to a specific quality.
     */
    public function transcodeToQuality(
        string $sourcePath,
        string $outputPath,
        string $quality,
        array $qualitySettings,
        callable $progressCallback = null
    ): bool {
        $fullSourcePath = $this->getFullPath($sourcePath);
        $fullOutputPath = $this->getFullPath($outputPath);

        // Ensure output directory exists
        $outputDir = dirname($fullOutputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $command = $this->buildTranscodeCommand($fullSourcePath, $fullOutputPath, $qualitySettings);

        Log::info("Transcoding command for {$quality}", ['command' => $command]);

        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new Exception("Failed to start FFmpeg process");
        }

        fclose($pipes[0]);

        // Read output
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            Log::error("FFmpeg transcoding failed", [
                'return_code' => $returnCode,
                'stderr' => $stderr,
                'quality' => $quality,
            ]);
            throw new Exception("FFmpeg transcoding failed: {$stderr}");
        }

        return file_exists($fullOutputPath);
    }

    /**
     * Generate HLS playlist and segments for a quality.
     */
    public function generateHlsPlaylist(
        string $sourcePath,
        string $outputDirectory,
        string $quality,
        array $qualitySettings
    ): array {
        $fullSourcePath = $this->getFullPath($sourcePath);
        $fullOutputDir = $this->getFullPath($outputDirectory);

        // Ensure output directory exists
        if (!is_dir($fullOutputDir)) {
            mkdir($fullOutputDir, 0755, true);
        }

        $playlistPath = $fullOutputDir . '/' . $this->hlsSettings['playlist_filename'];
        $segmentPattern = $fullOutputDir . '/' . $this->hlsSettings['segment_filename_pattern'];

        $command = $this->buildHlsCommand(
            $fullSourcePath,
            $playlistPath,
            $segmentPattern,
            $qualitySettings
        );

        Log::info("HLS generation command for {$quality}", ['command' => $command]);

        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new Exception("Failed to start FFmpeg process for HLS");
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            Log::error("FFmpeg HLS generation failed", [
                'return_code' => $returnCode,
                'stderr' => $stderr,
                'quality' => $quality,
            ]);
            throw new Exception("FFmpeg HLS generation failed: {$stderr}");
        }

        // Calculate total size of generated files
        $totalSize = 0;
        foreach (glob($fullOutputDir . '/*.ts') as $segment) {
            $totalSize += filesize($segment);
        }
        if (file_exists($playlistPath)) {
            $totalSize += filesize($playlistPath);
        }

        return [
            'playlist_path' => $outputDirectory . '/' . $this->hlsSettings['playlist_filename'],
            'segment_count' => count(glob($fullOutputDir . '/*.ts')),
            'total_size' => $totalSize,
        ];
    }

    /**
     * Generate master HLS playlist that references all quality variants.
     */
    public function generateMasterPlaylist(Lesson $lesson): string
    {
        $qualities = $lesson->videoQualities()->where('status', 'completed')->get();

        if ($qualities->isEmpty()) {
            throw new Exception("No completed qualities found for lesson");
        }

        $masterContent = "#EXTM3U\n";
        $masterContent .= "#EXT-X-VERSION:3\n\n";

        foreach ($qualities as $quality) {
            if (!$quality->hls_playlist_path) {
                continue;
            }

            $bandwidth = ($quality->bitrate + ($this->qualities[$quality->quality]['audio_bitrate'] ?? 128)) * 1000;
            $resolution = "{$quality->width}x{$quality->height}";

            // Get relative path to quality playlist
            $playlistRelativePath = basename(dirname($quality->hls_playlist_path)) . '/' . basename($quality->hls_playlist_path);

            $masterContent .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$resolution},NAME=\"{$quality->quality}\"\n";
            $masterContent .= "{$playlistRelativePath}\n\n";
        }

        // Determine master playlist path
        $basePath = $this->processingSettings['output_directory'] . '/' . $lesson->id;
        $masterPlaylistPath = $basePath . '/' . $this->hlsSettings['master_playlist_filename'];

        // Store the master playlist
        $disk = config('transcoding.storage.disk', 'public');
        Storage::disk($disk)->put($masterPlaylistPath, $masterContent);

        return $masterPlaylistPath;
    }

    /**
     * Build the FFmpeg transcode command.
     */
    protected function buildTranscodeCommand(string $input, string $output, array $settings): string
    {
        $videoCodec = $this->encodingSettings['video_codec'];
        $audioCodec = $this->encodingSettings['audio_codec'];
        $preset = $this->encodingSettings['preset'];
        $profile = $this->encodingSettings['profile'];
        $level = $this->encodingSettings['level'];
        $pixelFormat = $this->encodingSettings['pixel_format'];
        $audioSampleRate = $this->encodingSettings['audio_sample_rate'];
        $audioChannels = $this->encodingSettings['audio_channels'];

        $scaleFilter = "scale={$settings['width']}:{$settings['height']}:force_original_aspect_ratio=decrease,pad={$settings['width']}:{$settings['height']}:(ow-iw)/2:(oh-ih)/2";

        return sprintf(
            '"%s" -y -i "%s" -c:v %s -preset %s -profile:v %s -level %s -pix_fmt %s ' .
            '-vf "%s" -b:v %dk -maxrate %dk -bufsize %dk ' .
            '-c:a %s -b:a %dk -ar %d -ac %d ' .
            '-movflags +faststart "%s"',
            $this->ffmpegPath,
            $input,
            $videoCodec,
            $preset,
            $profile,
            $level,
            $pixelFormat,
            $scaleFilter,
            $settings['video_bitrate'],
            $settings['max_rate'],
            $settings['buffer_size'],
            $audioCodec,
            $settings['audio_bitrate'],
            $audioSampleRate,
            $audioChannels,
            $output
        );
    }

    /**
     * Build the FFmpeg HLS command.
     */
    protected function buildHlsCommand(string $input, string $playlistPath, string $segmentPattern, array $settings): string
    {
        $videoCodec = $this->encodingSettings['video_codec'];
        $audioCodec = $this->encodingSettings['audio_codec'];
        $preset = $this->encodingSettings['preset'];
        $profile = $this->encodingSettings['profile'];
        $level = $this->encodingSettings['level'];
        $pixelFormat = $this->encodingSettings['pixel_format'];
        $audioSampleRate = $this->encodingSettings['audio_sample_rate'];
        $audioChannels = $this->encodingSettings['audio_channels'];
        $segmentDuration = $this->hlsSettings['segment_duration'];
        $keyframeInterval = $this->encodingSettings['keyframe_interval'];

        $scaleFilter = "scale={$settings['width']}:{$settings['height']}:force_original_aspect_ratio=decrease,pad={$settings['width']}:{$settings['height']}:(ow-iw)/2:(oh-ih)/2";

        return sprintf(
            '"%s" -y -i "%s" -c:v %s -preset %s -profile:v %s -level %s -pix_fmt %s ' .
            '-vf "%s" -b:v %dk -maxrate %dk -bufsize %dk ' .
            '-c:a %s -b:a %dk -ar %d -ac %d ' .
            '-g %d -keyint_min %d -sc_threshold 0 ' .
            '-f hls -hls_time %d -hls_playlist_type vod -hls_segment_filename "%s" "%s"',
            $this->ffmpegPath,
            $input,
            $videoCodec,
            $preset,
            $profile,
            $level,
            $pixelFormat,
            $scaleFilter,
            $settings['video_bitrate'],
            $settings['max_rate'],
            $settings['buffer_size'],
            $audioCodec,
            $settings['audio_bitrate'],
            $audioSampleRate,
            $audioChannels,
            $keyframeInterval,
            $keyframeInterval,
            $segmentDuration,
            $segmentPattern,
            $playlistPath
        );
    }

    /**
     * Get the full filesystem path for a storage path.
     */
    protected function getFullPath(string $path): string
    {
        $disk = config('transcoding.storage.disk', 'public');

        // If it's already an absolute path, return it
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) || strpos($path, '/') === 0) {
            return $path;
        }

        return Storage::disk($disk)->path($path);
    }

    /**
     * Create video quality records for a lesson.
     */
    public function createQualityRecords(Lesson $lesson, array $qualitiesToGenerate): void
    {
        foreach ($qualitiesToGenerate as $qualityName => $settings) {
            VideoQuality::updateOrCreate(
                [
                    'lesson_id' => $lesson->id,
                    'quality' => $qualityName,
                ],
                [
                    'width' => $settings['width'],
                    'height' => $settings['height'],
                    'bitrate' => $settings['video_bitrate'],
                    'status' => 'pending',
                    'storage_disk' => config('transcoding.storage.disk', 'public'),
                ]
            );
        }
    }

    /**
     * Clean up temporary transcoding files.
     */
    public function cleanupTempFiles(string $tempDir): void
    {
        if (!config('transcoding.processing.cleanup_temp_files', true)) {
            return;
        }

        $fullPath = $this->getFullPath($tempDir);

        if (is_dir($fullPath)) {
            $files = glob($fullPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($fullPath);
        }
    }

    /**
     * Check if FFmpeg is available.
     */
    public function isFFmpegAvailable(): bool
    {
        $command = sprintf('"%s" -version', $this->ffmpegPath);
        exec($command, $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Check if FFprobe is available.
     */
    public function isFFprobeAvailable(): bool
    {
        $command = sprintf('"%s" -version', $this->ffprobePath);
        exec($command, $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Get the processing temp directory for a lesson.
     */
    public function getTempDirectory(Lesson $lesson): string
    {
        return $this->processingSettings['temp_directory'] . '/' . $lesson->id . '_' . Str::random(8);
    }

    /**
     * Get the output directory for HLS files.
     */
    public function getHlsOutputDirectory(Lesson $lesson, string $quality): string
    {
        return $this->processingSettings['output_directory'] . '/' . $lesson->id . '/' . $quality;
    }
}
