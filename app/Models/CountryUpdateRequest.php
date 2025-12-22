<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryUpdateRequest extends Model
{
    protected $fillable = [
        'user_id',
        'current_country',
        'new_country',
        'new_phone',
        'reason',
        'verification_document_path',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approve($adminId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes
        ]);

        // Update user's enrollment country
        $enrollment = $this->user->enrollments()->first();
        if ($enrollment) {
            $enrollment->update([
                'country' => $this->new_country,
                'phone' => $this->new_phone
            ]);
        }
    }

    public function reject($adminId, $notes)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'rejected_at' => now(),
            'admin_notes' => $notes
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}