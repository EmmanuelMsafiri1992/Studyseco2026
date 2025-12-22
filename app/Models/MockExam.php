<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockExam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'duration_minutes',
        'total_marks',
        'passing_marks',
        'weak_area_feedback',
        'is_published',
    ];

    protected $casts = [
        'weak_area_feedback' => 'array',
        'is_published' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getAverageScore()
    {
        return $this->attempts()
            ->where('status', 'completed')
            ->avg('score') ?? 0;
    }

    public function getPassRate()
    {
        $total = $this->attempts()->where('status', 'completed')->count();
        if ($total == 0) return 0;

        $passed = $this->attempts()
            ->where('status', 'completed')
            ->where('score', '>=', $this->passing_marks)
            ->count();

        return ($passed / $total) * 100;
    }
}
