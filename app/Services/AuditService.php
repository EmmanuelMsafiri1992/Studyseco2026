<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $eventType,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        $user = Auth::user();
        
        return AuditLog::create([
            'user_id' => $user?->id,
            'event' => $eventType,
            'model_type' => $auditable ? get_class($auditable) : null,
            'model_id' => $auditable?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }

    public static function logLogin($user): AuditLog
    {
        return self::log(
            'login',
            $user,
            description: "User {$user->name} ({$user->email}) logged in"
        );
    }

    public static function logLogout($user): AuditLog
    {
        return self::log(
            'logout',
            $user,
            description: "User {$user->name} ({$user->email}) logged out"
        );
    }

    public static function logPayment($payment, $eventType = 'created'): AuditLog
    {
        return self::log(
            'payment',
            $payment,
            description: "Payment {$eventType}: {$payment->currency} {$payment->amount} - {$payment->status}"
        );
    }

    public static function logEnrollment($enrollment, $eventType = 'created'): AuditLog
    {
        return self::log(
            'enrollment',
            $enrollment,
            description: "Enrollment {$eventType}: {$enrollment->name} ({$enrollment->email}) - {$enrollment->status}"
        );
    }

    public static function logAccess($user, $granted = true): AuditLog
    {
        $eventType = $granted ? 'access_granted' : 'access_revoked';
        return self::log(
            $eventType,
            $user,
            description: "Access " . ($granted ? 'granted to' : 'revoked from') . " {$user->name} ({$user->email})"
        );
    }

    private static function generateDescription(string $eventType, ?Model $auditable, $user): string
    {
        $userName = $user ? "{$user->name} ({$user->email})" : 'System';
        $auditableName = $auditable ? class_basename($auditable) . " #{$auditable->id}" : 'resource';
        
        return match($eventType) {
            'created' => "{$userName} created {$auditableName}",
            'updated' => "{$userName} updated {$auditableName}",
            'deleted' => "{$userName} deleted {$auditableName}",
            'login' => "{$userName} logged in",
            'logout' => "{$userName} logged out",
            default => "{$userName} performed {$eventType} on {$auditableName}"
        };
    }
}