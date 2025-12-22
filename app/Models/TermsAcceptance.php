<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TermsAcceptance extends Model
{
    protected $fillable = [
        'user_id',
        'terms_type',
        'terms_version',
        'accepted_at',
        'ip_address',
        'user_agent',
        'terms_data'
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'terms_data' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Check if user has accepted specific terms type and version
    public static function hasAccepted(int $userId, string $termsType = 'general', string $version = '1.0'): bool
    {
        return self::where('user_id', $userId)
                   ->where('terms_type', $termsType)
                   ->where('terms_version', $version)
                   ->exists();
    }

    // Record terms acceptance
    public static function recordAcceptance(int $userId, string $termsType = 'general', string $version = '1.0', array $termsData = null): self
    {
        return self::create([
            'user_id' => $userId,
            'terms_type' => $termsType,
            'terms_version' => $version,
            'accepted_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'terms_data' => $termsData
        ]);
    }

    // Get latest acceptance for user and terms type
    public static function getLatestAcceptance(int $userId, string $termsType = 'general'): ?self
    {
        return self::where('user_id', $userId)
                   ->where('terms_type', $termsType)
                   ->latest('accepted_at')
                   ->first();
    }
}
