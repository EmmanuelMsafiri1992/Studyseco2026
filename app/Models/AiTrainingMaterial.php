<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTrainingMaterial extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'subject_id',
        'grade_level',
        'syllabus',
        'file_path',
        'file_type',
        'file_size',
        'metadata',
        'uploaded_by',
        'is_processed',
        'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_processed' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $size = $this->file_size;
        
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        } else {
            return round($size / 1073741824, 2) . ' GB';
        }
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_processed ? 'green' : 'yellow';
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'book' => '📚',
            'past_paper' => '📄',
            'notes' => '📝',
            'questions' => '❓',
            'answers' => '✅',
            default => '📁'
        };
    }

    public function getProcessingStatusTextAttribute(): string
    {
        return $this->is_processed ? 'Ready for AI' : 'Pending Processing';
    }
}