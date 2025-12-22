<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'certificate_type',
        'pdf_path',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    /**
     * Get the student who received this certificate.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the teacher who issued this certificate.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the subject this certificate is for.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get formatted certificate type.
     */
    public function getTypeLabel(): string
    {
        return match($this->certificate_type) {
            'completion' => 'Certificate of Completion',
            'achievement' => 'Certificate of Achievement',
            'excellence' => 'Certificate of Excellence',
            default => 'Certificate',
        };
    }
}
