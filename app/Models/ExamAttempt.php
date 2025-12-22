<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttempt extends Model
{
    protected $fillable = [
        'mock_exam_id',
        'student_id',
        'started_at',
        'completed_at',
        'score',
        'percentage',
        'answers',
        'weak_areas',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
        'weak_areas' => 'array',
    ];

    public function mockExam(): BelongsTo
    {
        return $this->belongsTo(MockExam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function complete(int $score, float $percentage, array $weakAreas)
    {
        $this->update([
            'completed_at' => now(),
            'score' => $score,
            'percentage' => $percentage,
            'weak_areas' => $weakAreas,
            'status' => 'completed',
        ]);
    }

    public function isPassing(): bool
    {
        return $this->score >= $this->mockExam->passing_marks;
    }

    public function getGrade(): string
    {
        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'F';
    }
}
