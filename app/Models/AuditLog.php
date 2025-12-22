<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo('model');
    }

    public function getEventIconAttribute(): string
    {
        return match($this->event) {
            'created' => '➕',
            'updated' => '✏️',
            'deleted' => '🗑️',
            'login' => '🔑',
            'logout' => '🚪',
            'payment' => '💰',
            'enrollment' => '📝',
            'access_granted' => '✅',
            'access_revoked' => '❌',
            'ai_training_upload' => '🤖',
            'ai_training_process' => '⚙️',
            'ai_training_bulk_process' => '🔄',
            'ai_training_delete' => '🗑️',
            default => '📋'
        };
    }

    public function getEventColorAttribute(): string
    {
        return match($this->event) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'login' => 'emerald',
            'logout' => 'slate',
            'payment' => 'yellow',
            'enrollment' => 'indigo',
            'access_granted' => 'green',
            'access_revoked' => 'red',
            'ai_training_upload' => 'purple',
            'ai_training_process' => 'blue',
            'ai_training_bulk_process' => 'indigo',
            'ai_training_delete' => 'red',
            default => 'gray'
        };
    }
}