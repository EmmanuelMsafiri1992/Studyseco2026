<?php

namespace App\Models;

use App\Services\CloudStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VideoQuality extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'quality',
        'width',
        'height',
        'bitrate',
        'video_path',
        'hls_playlist_path',
        'file_size',
        'storage_disk',
        'status',
        'error_message',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'bitrate' => 'integer',
        'file_size' => 'integer',
    ];

    protected $appends = [
        'url',
        'hls_url',
        'formatted_file_size',
    ];

    /**
     * Get the lesson that owns this quality variant.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the video URL attribute.
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->video_path) {
            return null;
        }

        $disk = $this->storage_disk ?? 'public';

        if ($disk === 's3' || $disk === 'gcs') {
            $storageService = new CloudStorageService();
            return $storageService->url($this->video_path);
        }

        return Storage::disk($disk)->url($this->video_path);
    }

    /**
     * Get the HLS playlist URL attribute.
     */
    public function getHlsUrlAttribute(): ?string
    {
        if (!$this->hls_playlist_path) {
            return null;
        }

        $disk = $this->storage_disk ?? 'public';

        if ($disk === 's3' || $disk === 'gcs') {
            $storageService = new CloudStorageService();
            return $storageService->url($this->hls_playlist_path);
        }

        return Storage::disk($disk)->url($this->hls_playlist_path);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope to get only completed qualities.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get qualities by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if this quality is ready for playback.
     */
    public function isReady(): bool
    {
        return $this->status === 'completed' && ($this->video_path || $this->hls_playlist_path);
    }

    /**
     * Mark this quality as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark this quality as completed.
     */
    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge(['status' => 'completed', 'error_message' => null], $data));
    }

    /**
     * Mark this quality as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
