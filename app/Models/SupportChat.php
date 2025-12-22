<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'agent_id',
        'status',
        'priority',
        'category',
        'user_name',
        'user_email',
        'user_info',
        'started_at',
        'assigned_at',
        'closed_at',
        'queue_position',
        'initial_message',
        'tags',
        'resolution_summary',
        'satisfaction_rating',
        'satisfaction_feedback',
    ];

    protected $casts = [
        'user_info' => 'array',
        'tags' => 'array',
        'started_at' => 'datetime',
        'assigned_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user associated with this chat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agent handling this chat.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get all messages for this chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportChatMessage::class)->orderBy('created_at');
    }

    /**
     * Get recent messages for this chat.
     */
    public function recentMessages(): HasMany
    {
        return $this->hasMany(SupportChatMessage::class)
                    ->orderBy('created_at', 'desc')
                    ->limit(50);
    }

    /**
     * Assign agent to this chat.
     */
    public function assignAgent(User $agent)
    {
        $this->update([
            'agent_id' => $agent->id,
            'status' => 'active',
            'assigned_at' => now(),
            'queue_position' => null,
        ]);

        // Add system message
        $this->messages()->create([
            'message' => "Agent {$agent->name} has joined the chat.",
            'message_type' => 'system',
            'sender_type' => 'system',
        ]);
    }

    /**
     * Close the chat.
     */
    public function close($resolutionSummary = null)
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'resolution_summary' => $resolutionSummary,
        ]);

        // Add system message
        $this->messages()->create([
            'message' => 'Chat has been closed.',
            'message_type' => 'system',
            'sender_type' => 'system',
        ]);
    }

    /**
     * Get display name for the user.
     */
    public function getUserDisplayName()
    {
        return $this->user ? $this->user->name : ($this->user_name ?? 'Guest');
    }

    /**
     * Update queue position.
     */
    public function updateQueuePosition($position)
    {
        $this->update(['queue_position' => $position]);
    }

    /**
     * Scope for waiting chats.
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope for active chats.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for assigned to specific agent.
     */
    public function scopeAssignedTo($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }
}
