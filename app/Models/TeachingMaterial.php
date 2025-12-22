<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TeachingMaterial extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'material_type',
        'grade_level',
        'duration',
        'tags',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'is_public',
        'is_active',
        'download_count',
        'view_count'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        return null;
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('material_type', $type);
    }

    public function getFileTypeIconAttribute()
    {
        $icons = [
            'lesson_plan' => '📋',
            'worksheet' => '📝', 
            'presentation' => '📊',
            'reference' => '📚',
            'assignment' => '📋',
            'video' => '🎥',
            'audio' => '🎵'
        ];
        
        return $icons[$this->material_type] ?? '📄';
    }

    public function getFileTypeColorAttribute()
    {
        $colors = [
            'lesson_plan' => 'bg-blue-600',
            'worksheet' => 'bg-green-600',
            'presentation' => 'bg-purple-600',
            'reference' => 'bg-indigo-600',
            'assignment' => 'bg-amber-600',
            'video' => 'bg-red-600',
            'audio' => 'bg-pink-600'
        ];
        
        return $colors[$this->material_type] ?? 'bg-gray-600';
    }
}