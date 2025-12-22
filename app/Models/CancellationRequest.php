<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CancellationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'cancellation_type',
        'reason',
        'status',
        'requested_at',
        'cancellation_deadline',
        'processed_at',
        'processed_by',
        'admin_notes',
        'refund_amount',
        'refund_status',
        'refund_processed_at',
        'original_payment_data'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'cancellation_deadline' => 'datetime',
        'processed_at' => 'datetime',
        'refund_processed_at' => 'datetime',
        'original_payment_data' => 'array',
        'refund_amount' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Check if cancellation is within 14-day policy
    public function isWithinCancellationPolicy(): bool
    {
        if (!$this->enrollment) {
            return false;
        }

        // Check if enrollment was created within 14 days
        $enrollmentDate = $this->enrollment->created_at;
        $fourteenDaysLater = $enrollmentDate->addDays(14);
        
        return now()->lessThanOrEqualTo($fourteenDaysLater);
    }

    // Calculate refund amount based on 14-day policy
    public function calculateRefundAmount(): float
    {
        if (!$this->isWithinCancellationPolicy()) {
            return 0.0;
        }

        if (!$this->enrollment) {
            return 0.0;
        }

        // Get total paid amount from enrollment payments
        $totalPaid = $this->enrollment->payments()
            ->where('status', 'verified')
            ->sum('amount');

        return $totalPaid;
    }

    // Set cancellation deadline based on enrollment date
    public function setCancellationDeadline(): void
    {
        if ($this->enrollment) {
            $this->cancellation_deadline = $this->enrollment->created_at->addDays(14);
            $this->save();
        }
    }

    // Check if deadline has passed
    public function isDeadlinePassed(): bool
    {
        return $this->cancellation_deadline && now()->greaterThan($this->cancellation_deadline);
    }

    // Get days remaining for cancellation
    public function getDaysRemainingForCancellation(): int
    {
        if (!$this->cancellation_deadline) {
            return 0;
        }

        if ($this->isDeadlinePassed()) {
            return 0;
        }

        return now()->diffInDays($this->cancellation_deadline, false);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWithinDeadline($query)
    {
        return $query->where('cancellation_deadline', '>=', now())
                    ->orWhereNull('cancellation_deadline');
    }

    public function scopeExpired($query)
    {
        return $query->where('cancellation_deadline', '<', now());
    }
}
