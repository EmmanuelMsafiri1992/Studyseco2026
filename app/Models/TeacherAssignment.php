<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TeacherAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'student_id',
        'subject_id',
        'assignment_type',
        'status',
        'priority_score',
        'assignment_criteria',
        'assigned_date',
        'start_date',
        'end_date',
        'assigned_by',
        'assignment_notes',
        'performance_metrics',
        'is_active',
        'last_interaction_at'
    ];

    protected $casts = [
        'assignment_criteria' => 'array',
        'performance_metrics' => 'array',
        'assigned_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'last_interaction_at' => 'datetime'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-700',
            'inactive' => 'bg-slate-100 text-slate-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'completed' => 'bg-blue-100 text-blue-700',
            default => 'bg-slate-100 text-slate-700'
        };
    }

    public function getAssignmentTypeIcon(): string
    {
        return match($this->assignment_type) {
            'automatic' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
            'manual' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'requested' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5',
            default => 'M13 10V3L4 14h7v7l9-11h-7z'
        };
    }

    public function getDurationInDays(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        return $this->start_date->diffInDays($this->end_date);
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getProgressPercentage(): int
    {
        if (!$this->performance_metrics || !isset($this->performance_metrics['completed_lessons'])) {
            return 0;
        }
        
        $completed = $this->performance_metrics['completed_lessons'] ?? 0;
        $total = $this->performance_metrics['total_lessons'] ?? 1;
        
        return min(100, round(($completed / $total) * 100));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}
