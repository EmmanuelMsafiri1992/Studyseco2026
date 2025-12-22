<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class EnrollmentPayment extends Model
{
    protected $fillable = [
        'enrollment_id',
        'payment_method_id',
        'reference_number',
        'amount',
        'currency',
        'payment_proof_path',
        'payment_details',
        'payment_type',
        'additional_data',
        'extension_months',
        'status',
        'admin_notes',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'additional_data' => 'array',
        'payment_details' => 'array'
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who owns the enrollment
     */
    public function user()
    {
        return $this->enrollment?->user;
    }

    /**
     * Get access duration in days
     */
    public function getAccessDurationDaysAttribute()
    {
        // For extension payments, calculate based on extension_months
        if ($this->extension_months) {
            return $this->extension_months * 30;
        }
        
        // For subject increase payments, default to 30 days
        if ($this->payment_type === 'subject_increase') {
            return 30;
        }
        
        // Default fallback
        return 30;
    }

    /**
     * Get formatted amount with proper calculation for subject increases
     */
    public function getCalculatedAmountAttribute()
    {
        // If amount is 0 and this is a subject increase, calculate it
        if ($this->amount == 0 && $this->payment_type === 'subject_increase') {
            $additionalData = $this->additional_data;
            
            // Handle both array and string format
            if (is_string($additionalData)) {
                $additionalData = json_decode($additionalData, true);
            }
            
            if (is_array($additionalData) && isset($additionalData['subject_count'])) {
                $subjectCount = (int) $additionalData['subject_count'];
                
                // Get price per subject from enrollment first
                $enrollment = $this->enrollment;
                if ($enrollment && $enrollment->price_per_subject > 0) {
                    return $subjectCount * $enrollment->price_per_subject;
                }
                
                // Fallback pricing based on currency
                $pricePerSubject = match($this->currency) {
                    'MWK' => 50000,
                    'ZAR' => 350,
                    'USD' => 25,
                    default => 50000
                };
                
                return $subjectCount * $pricePerSubject;
            }
        }
        
        return $this->amount;
    }
}
