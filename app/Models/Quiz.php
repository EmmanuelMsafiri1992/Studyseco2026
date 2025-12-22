<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'subject_id',
        'teacher_id',
        'title',
        'description',
        'duration_minutes',
        'total_points',
        'passing_score',
        'show_correct_answers',
        'shuffle_questions',
        'max_attempts',
        'status',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'show_correct_answers' => 'boolean',
        'shuffle_questions' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('available_from')
                    ->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_until')
                    ->orWhere('available_until', '>=', now());
            });
    }

    // Helpers
    public function isAvailable(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->available_from && now()->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && now()->gt($this->available_until)) {
            return false;
        }

        return true;
    }

    public function canAttempt(User $user): bool
    {
        $attemptCount = $this->attempts()
            ->where('student_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return $attemptCount < $this->max_attempts;
    }

    public function getUserAttempts(User $user)
    {
        return $this->attempts()
            ->where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBestScore(User $user)
    {
        return $this->attempts()
            ->where('student_id', $user->id)
            ->where('status', 'completed')
            ->max('score');
    }

    public function getAverageScore()
    {
        return $this->attempts()
            ->where('status', 'completed')
            ->avg('score');
    }
}
