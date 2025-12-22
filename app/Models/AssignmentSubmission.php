<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'attachments',
        'status',
        'submitted_at',
        'grade',
        'points_earned',
        'teacher_feedback',
        'grading_rubric',
        'is_late',
        'attempt_number',
        'graded_at',
        'graded_by'
    ];

    protected $casts = [
        'attachments' => 'array',
        'grading_rubric' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'grade' => 'decimal:2',
        'points_earned' => 'integer',
        'attempt_number' => 'integer'
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeOnTime($query)
    {
        return $query->where('is_late', false);
    }

    // Helper methods
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']);
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft' && $this->assignment->canAcceptSubmissions();
    }

    public function submit(): bool
    {
        if (!$this->canBeEdited()) {
            return false;
        }

        $this->status = 'submitted';
        $this->submitted_at = Carbon::now();
        
        // Check if submission is late
        if ($this->assignment->due_date < Carbon::now()) {
            $this->is_late = true;
        }

        return $this->save();
    }

    public function grade(float $grade, int $pointsEarned, ?string $feedback = null, ?array $rubric = null, ?int $gradedBy = null): bool
    {
        if (!$this->isSubmitted()) {
            return false;
        }

        $this->grade = $grade;
        $this->points_earned = $pointsEarned;
        $this->teacher_feedback = $feedback;
        $this->grading_rubric = $rubric;
        $this->graded_by = $gradedBy ?? auth()->id();
        $this->graded_at = Carbon::now();
        $this->status = 'graded';

        return $this->save();
    }

    public function getAttachmentUrls(): array
    {
        if (!$this->attachments) {
            return [];
        }

        return array_map(function ($path) {
            return Storage::url($path);
        }, $this->attachments);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-700',
            'submitted' => 'bg-blue-100 text-blue-800',
            'graded' => 'bg-green-100 text-green-800',
            'returned' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-700'
        };
    }

    public function getGradePercentage(): ?float
    {
        if (!$this->points_earned || !$this->assignment->max_points) {
            return null;
        }

        return round(($this->points_earned / $this->assignment->max_points) * 100, 2);
    }

    public function getGradeColor(): string
    {
        $percentage = $this->getGradePercentage();
        
        if ($percentage === null) {
            return 'text-gray-500';
        }

        if ($percentage >= 90) return 'text-green-600';
        if ($percentage >= 80) return 'text-blue-600';
        if ($percentage >= 70) return 'text-yellow-600';
        if ($percentage >= 60) return 'text-orange-600';
        return 'text-red-600';
    }

    public function getLatePenalty(): int
    {
        if (!$this->is_late || !$this->assignment->late_penalty_percentage) {
            return 0;
        }

        return round(($this->points_earned ?? 0) * ($this->assignment->late_penalty_percentage / 100));
    }

    public function getFinalPoints(): int
    {
        return ($this->points_earned ?? 0) - $this->getLatePenalty();
    }
}