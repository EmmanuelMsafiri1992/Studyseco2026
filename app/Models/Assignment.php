<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Assignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'instructions',
        'assignment_type',
        'attachments',
        'due_date',
        'max_points',
        'allow_late_submission',
        'late_submission_deadline',
        'late_penalty_percentage',
        'status',
        'assignment_settings'
    ];

    protected $casts = [
        'attachments' => 'array',
        'assignment_settings' => 'array',
        'due_date' => 'datetime',
        'late_submission_deadline' => 'datetime',
        'allow_late_submission' => 'boolean',
        'late_penalty_percentage' => 'integer',
        'max_points' => 'integer'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_student', 'assignment_id', 'student_id')
            ->withPivot(['assigned_at', 'is_active', 'individual_instructions', 'individual_due_date'])
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function activeSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class)->where('status', '!=', 'draft');
    }

    public function gradedSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class)->where('status', 'graded');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeDueSoon($query, $days = 3)
    {
        return $query->where('due_date', '<=', Carbon::now()->addDays($days))
                     ->where('due_date', '>=', Carbon::now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
                     ->where('status', 'published');
    }

    // Helper methods
    public function isDue(): bool
    {
        return $this->due_date < Carbon::now();
    }

    public function isDueSoon($days = 3): bool
    {
        return $this->due_date <= Carbon::now()->addDays($days) && $this->due_date >= Carbon::now();
    }

    public function canAcceptSubmissions(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if (!$this->isDue()) {
            return true;
        }

        return $this->allow_late_submission && 
               ($this->late_submission_deadline === null || $this->late_submission_deadline > Carbon::now());
    }

    public function getSubmissionForStudent($studentId): ?AssignmentSubmission
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->latest('attempt_number')
            ->first();
    }

    public function getSubmissionStats(): array
    {
        $totalAssigned = $this->students()->where('is_active', true)->count();
        $submitted = $this->submissions()->where('status', '!=', 'draft')->count();
        $graded = $this->submissions()->where('status', 'graded')->count();
        
        return [
            'total_assigned' => $totalAssigned,
            'submitted' => $submitted,
            'graded' => $graded,
            'pending_grading' => $submitted - $graded,
            'not_submitted' => $totalAssigned - $submitted,
            'submission_rate' => $totalAssigned > 0 ? round(($submitted / $totalAssigned) * 100, 2) : 0,
        ];
    }

    public function getTypeIcon(): string
    {
        $icons = [
            'homework' => '📝',
            'project' => '🏗️',
            'quiz' => '❓',
            'essay' => '📄',
            'research' => '🔍',
            'practice' => '💪'
        ];

        return $icons[$this->assignment_type] ?? '📋';
    }

    public function getTypeColor(): string
    {
        $colors = [
            'homework' => 'bg-blue-600',
            'project' => 'bg-purple-600',
            'quiz' => 'bg-green-600',
            'essay' => 'bg-indigo-600',
            'research' => 'bg-amber-600',
            'practice' => 'bg-red-600'
        ];

        return $colors[$this->assignment_type] ?? 'bg-gray-600';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'published' => 'bg-green-100 text-green-800',
            'draft' => 'bg-gray-100 text-gray-700',
            'closed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700'
        };
    }
}