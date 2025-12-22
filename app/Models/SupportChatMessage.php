<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_chat_id',
        'user_id',
        'message',
        'message_type',
        'attachments',
        'sender_type',
        'is_read_by_user',
        'is_read_by_agent',
        'read_by_user_at',
        'read_by_agent_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read_by_user' => 'boolean',
        'is_read_by_agent' => 'boolean',
        'read_by_user_at' => 'datetime',
        'read_by_agent_at' => 'datetime',
    ];

    /**
     * Get the support chat that owns this message.
     */
    public function supportChat(): BelongsTo
    {
        return $this->belongsTo(SupportChat::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark message as read by user.
     */
    public function markAsReadByUser()
    {
        if (!$this->is_read_by_user) {
            $this->update([
                'is_read_by_user' => true,
                'read_by_user_at' => now(),
            ]);
        }
    }

    /**
     * Mark message as read by agent.
     */
    public function markAsReadByAgent()
    {
        if (!$this->is_read_by_agent) {
            $this->update([
                'is_read_by_agent' => true,
                'read_by_agent_at' => now(),
            ]);
        }
    }

    /**
     * Get sender display name.
     */
    public function getSenderName()
    {
        if ($this->sender_type === 'system') {
            return 'System';
        }

        if ($this->user) {
            return $this->user->name;
        }

        // For guest users
        $chat = $this->supportChat;
        return $chat ? $chat->getUserDisplayName() : 'Guest';
    }

    /**
     * Scope for unread messages by user.
     */
    public function scopeUnreadByUser($query)
    {
        return $query->where('is_read_by_user', false);
    }

    /**
     * Scope for unread messages by agent.
     */
    public function scopeUnreadByAgent($query)
    {
        return $query->where('is_read_by_agent', false);
    }
}
