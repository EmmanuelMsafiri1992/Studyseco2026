<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'payment_method',
        'amount',
        'reference_number',
        'proof_screenshot',
        'status',
        'access_duration_days',
        'access_expires_at',
        'admin_notes',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'access_expires_at' => 'date',
        'verified_at' => 'datetime',
        'access_duration_days' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Get formatted payment method name
    public function getFormattedPaymentMethodAttribute(): string
    {
        return match($this->payment_method) {
            'tnm_mpamba' => 'TNM Mpamba',
            'airtel_money' => 'Airtel Money',
            'bank_transfer' => 'Bank Transfer',
            default => ucwords(str_replace('_', ' ', $this->payment_method))
        };
    }

    // Get formatted amount in MWK
    public function getFormattedAmountAttribute(): string
    {
        return 'MWK ' . number_format($this->amount, 2);
    }

    // Get status badge color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'amber',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    // Check if payment is pending
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Check if payment is approved
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Check if payment is rejected
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // Check if access is still valid
    public function hasValidAccess(): bool
    {
        return $this->isApproved() && 
               $this->access_expires_at && 
               $this->access_expires_at->isFuture();
    }

    // Scope for pending payments
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for approved payments
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Get days remaining for access
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->hasValidAccess()) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->access_expires_at);
    }
}
