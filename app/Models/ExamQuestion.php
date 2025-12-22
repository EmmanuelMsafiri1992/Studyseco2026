<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $fillable = [
        'mock_exam_id',
        'question',
        'options',
        'correct_answer',
        'marks',
        'topic',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function mockExam(): BelongsTo
    {
        return $this->belongsTo(MockExam::class);
    }

    public function checkAnswer(string $answer): bool
    {
        return trim(strtolower($answer)) === trim(strtolower($this->correct_answer));
    }
}
