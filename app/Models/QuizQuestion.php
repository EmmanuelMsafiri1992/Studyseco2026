<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'question',
        'type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function checkAnswer(string $studentAnswer): bool
    {
        // Normalize answers for comparison
        $correctAnswer = strtolower(trim($this->correct_answer));
        $studentAnswer = strtolower(trim($studentAnswer));

        return $correctAnswer === $studentAnswer;
    }

    public function getOptionsArray(): array
    {
        if ($this->type === 'true_false') {
            return ['True', 'False'];
        }

        return $this->options ?? [];
    }
}
