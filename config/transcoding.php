<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FFmpeg Configuration
    |--------------------------------------------------------------------------
    |
    | Paths to FFmpeg and FFprobe executables. On Windows, these should be
    | the full paths to the .exe files.
    |
    */
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    /*
    |--------------------------------------------------------------------------
    | Quality Presets
    |--------------------------------------------------------------------------
    |
    | Video quality presets for transcoding. Each preset defines the
    | resolution, video bitrate, and audio bitrate.
    |
    */
    'qualities' => [
        '240p' => [
            'width' => 426,
            'height' => 240,
            'video_bitrate' => 400,  // kbps
            'audio_bitrate' => 64,   // kbps
            'max_rate' => 500,       // kbps
            'buffer_size' => 800,    // kbps
        ],
        '480p' => [
            'width' => 854,
            'height' => 480,
            'video_bitrate' => 1000, // kbps
            'audio_bitrate' => 96,   // kbps
            'max_rate' => 1200,      // kbps
            'buffer_size' => 2000,   // kbps
        ],
        '720p' => [
            'width' => 1280,
            'height' => 720,
            'video_bitrate' => 2500, // kbps
            'audio_bitrate' => 128,  // kbps
            'max_rate' => 3000,      // kbps
            'buffer_size' => 5000,   // kbps
        ],
        '1080p' => [
            'width' => 1920,
            'height' => 1080,
            'video_bitrate' => 5000, // kbps
            'audio_bitrate' => 192,  // kbps
            'max_rate' => 6000,      // kbps
            'buffer_size' => 10000,  // kbps
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HLS Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for HLS (HTTP Live Streaming) generation.
    |
    */
    'hls' => [
        'segment_duration' => 6,        // seconds per segment
        'playlist_type' => 'vod',       // vod or event
        'segment_filename_pattern' => 'segment_%03d.ts',
        'playlist_filename' => 'playlist.m3u8',
        'master_playlist_filename' => 'master.m3u8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the transcoding process.
    |
    */
    'processing' => [
        'max_concurrent_jobs' => 2,             // Maximum concurrent transcoding jobs
        'timeout' => 3600,                       // Maximum time for a single transcode (seconds)
        'temp_directory' => storage_path('app/transcoding_temp'),
        'output_directory' => 'lessons/hls',     // Relative to storage disk
        'cleanup_temp_files' => true,            // Clean up temp files after processing
        'retry_attempts' => 3,                   // Number of retry attempts on failure
        'retry_delay' => 60,                     // Delay between retries (seconds)
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Encoding Settings
    |--------------------------------------------------------------------------
    |
    | FFmpeg encoding parameters for optimal quality and compatibility.
    |
    */
    'encoding' => [
        'video_codec' => 'libx264',
        'audio_codec' => 'aac',
        'preset' => 'medium',            // ultrafast, superfast, veryfast, faster, fast, medium, slow, slower, veryslow
        'profile' => 'main',             // baseline, main, high
        'level' => '3.1',                // H.264 level
        'pixel_format' => 'yuv420p',     // For maximum compatibility
        'audio_sample_rate' => 44100,
        'audio_channels' => 2,
        'keyframe_interval' => 48,       // GOP size (keyframe every 2 seconds at 24fps)
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure where transcoded videos are stored.
    |
    */
    'storage' => [
        'disk' => env('TRANSCODING_STORAGE_DISK', 'public'),
        'preserve_original' => true,     // Keep the original video file
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Source Requirements
    |--------------------------------------------------------------------------
    |
    | Minimum source video requirements for transcoding to be triggered.
    |
    */
    'minimum_source' => [
        'width' => 320,                  // Minimum source width
        'height' => 240,                 // Minimum source height
        'duration' => 10,                // Minimum duration in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Transcoding
    |--------------------------------------------------------------------------
    |
    | Whether to automatically start transcoding when a video is uploaded.
    |
    */
    'auto_transcode' => env('AUTO_TRANSCODE_VIDEOS', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue settings for transcoding jobs.
    |
    */
    'queue' => [
        'connection' => env('TRANSCODING_QUEUE_CONNECTION', 'database'),
        'name' => env('TRANSCODING_QUEUE_NAME', 'transcoding'),
    ],
];
