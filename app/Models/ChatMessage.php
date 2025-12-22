<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_group_id',
        'user_id',
        'message',
        'message_type',
        'attachments',
        'reply_to',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
        'is_pinned',
        'reactions',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'is_pinned' => 'boolean',
        'reactions' => 'array',
    ];

    /**
     * Get the chat group that owns the message.
     */
    public function chatGroup(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the message this message is replying to.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to');
    }

    /**
     * Get replies to this message.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'reply_to');
    }

    /**
     * Mark message as edited
     */
    public function markAsEdited()
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now()
        ]);
    }

    /**
     * Soft delete message
     */
    public function softDelete()
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now()
        ]);
    }

    /**
     * Toggle pin status
     */
    public function togglePin()
    {
        $this->update(['is_pinned' => !$this->is_pinned]);
    }

    /**
     * Add reaction to message
     */
    public function addReaction($emoji, $userId)
    {
        $reactions = $this->reactions ?? [];
        
        if (!isset($reactions[$emoji])) {
            $reactions[$emoji] = [];
        }
        
        if (!in_array($userId, $reactions[$emoji])) {
            $reactions[$emoji][] = $userId;
        }
        
        $this->update(['reactions' => $reactions]);
    }

    /**
     * Remove reaction from message
     */
    public function removeReaction($emoji, $userId)
    {
        $reactions = $this->reactions ?? [];
        
        if (isset($reactions[$emoji])) {
            $reactions[$emoji] = array_filter($reactions[$emoji], function($id) use ($userId) {
                return $id !== $userId;
            });
            
            if (empty($reactions[$emoji])) {
                unset($reactions[$emoji]);
            }
        }
        
        $this->update(['reactions' => $reactions]);
    }

    /**
     * Scope for non-deleted messages
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope for pinned messages
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}