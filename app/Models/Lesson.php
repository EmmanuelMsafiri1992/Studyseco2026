<?php

namespace App\Models;

use App\Services\CloudStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'title',
        'description',
        'video_path',
        'video_filename',
        'duration_minutes',
        'notes',
        'order_index',
        'is_published',
        'storage_disk',
        'original_video_path',
        'transcoding_status',
        'master_playlist_path',
        'transcoding_progress',
        'transcoding_started_at',
        'transcoding_completed_at',
        'source_width',
        'source_height',
        'source_bitrate',
        'duration_seconds',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'transcoding_started_at' => 'datetime',
        'transcoding_completed_at' => 'datetime',
        'source_width' => 'integer',
        'source_height' => 'integer',
        'source_bitrate' => 'integer',
        'duration_seconds' => 'integer',
        'transcoding_progress' => 'integer',
    ];

    protected $appends = [
        'video_url',
        'formatted_duration',
        'hls_url',
        'available_qualities',
        'is_transcoding',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function subject()
    {
        return $this->hasOneThrough(Subject::class, Topic::class, 'id', 'id', 'topic_id', 'subject_id');
    }

    /**
     * Get the video qualities relationship.
     */
    public function videoQualities(): HasMany
    {
        return $this->hasMany(VideoQuality::class);
    }

    /**
     * Get the original video URL (for non-HLS playback).
     */
    public function getVideoUrlAttribute()
    {
        if (!$this->video_path) {
            return null;
        }

        $disk = $this->storage_disk ?? 'public';

        if ($disk === 's3' || $disk === 'gcs') {
            // Get cloud storage URL from storage service
            $storageService = new CloudStorageService();
            return $storageService->url($this->video_path);
        }

        return Storage::url($this->video_path);
    }

    /**
     * Get the HLS master playlist URL for adaptive streaming.
     */
    public function getHlsUrlAttribute(): ?string
    {
        if (!$this->master_playlist_path) {
            return null;
        }

        $disk = $this->storage_disk ?? 'public';

        if ($disk === 's3' || $disk === 'gcs') {
            $storageService = new CloudStorageService();
            return $storageService->url($this->master_playlist_path);
        }

        return Storage::disk($disk)->url($this->master_playlist_path);
    }

    /**
     * Get the available video qualities.
     */
    public function getAvailableQualitiesAttribute(): array
    {
        return $this->videoQualities()
            ->where('status', 'completed')
            ->orderByDesc('height')
            ->get()
            ->map(function ($quality) {
                return [
                    'quality' => $quality->quality,
                    'width' => $quality->width,
                    'height' => $quality->height,
                    'bitrate' => $quality->bitrate,
                    'url' => $quality->hls_url ?? $quality->url,
                ];
            })
            ->toArray();
    }

    /**
     * Check if video is currently transcoding.
     */
    public function getIsTranscodingAttribute(): bool
    {
        return in_array($this->transcoding_status, ['pending', 'processing']);
    }

    /**
     * Get the best available playback URL (HLS preferred).
     */
    public function getBestPlaybackUrl(): ?string
    {
        // Prefer HLS if available
        if ($this->master_playlist_path && $this->transcoding_status === 'completed') {
            return $this->hls_url;
        }

        // Fall back to original video
        return $this->video_url;
    }

    /**
     * Get the transcoding status details.
     */
    public function getTranscodingStatusDetails(): array
    {
        $qualities = $this->videoQualities;

        return [
            'status' => $this->transcoding_status ?? 'none',
            'progress' => $this->transcoding_progress ?? 0,
            'started_at' => $this->transcoding_started_at?->toIso8601String(),
            'completed_at' => $this->transcoding_completed_at?->toIso8601String(),
            'qualities' => $qualities->map(function ($q) {
                return [
                    'quality' => $q->quality,
                    'status' => $q->status,
                    'error' => $q->error_message,
                ];
            })->toArray(),
            'source' => [
                'width' => $this->source_width,
                'height' => $this->source_height,
                'bitrate' => $this->source_bitrate,
                'duration' => $this->duration_seconds,
            ],
        ];
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_minutes) {
            return 'No duration set';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }
}
