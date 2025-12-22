<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'started_at',
        'completed_at',
        'score',
        'total_points',
        'percentage',
        'status',
        'answers',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function complete(int $score, int $totalPoints): void
    {
        $this->score = $score;
        $this->total_points = $totalPoints;
        $this->percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
        $this->completed_at = now();
        $this->status = 'completed';
        $this->save();
    }

    public function isPassing(): bool
    {
        return $this->score >= $this->quiz->passing_score;
    }

    public function getGrade(): string
    {
        if (!$this->percentage) return 'N/A';

        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'F';
    }

    public function getTimeSpent(): ?int
    {
        if (!$this->completed_at) return null;

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}
