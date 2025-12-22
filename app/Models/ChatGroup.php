<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'subject_id',
        'created_by',
        'max_members',
        'allowed_topics',
        'is_active',
        'is_moderated',
        'moderator_settings',
        'last_activity_at',
    ];

    protected $casts = [
        'allowed_topics' => 'array',
        'is_active' => 'boolean',
        'is_moderated' => 'boolean',
        'moderator_settings' => 'array',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the subject that owns the chat group.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user that created the chat group.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The users that belong to the chat group.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withPivot(['role', 'joined_at', 'last_read_at', 'is_muted', 'notification_settings'])
                    ->withTimestamps();
    }

    /**
     * Get all messages for the chat group.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get recent messages for the chat group.
     */
    public function recentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)
                    ->where('is_deleted', false)
                    ->orderBy('created_at', 'desc')
                    ->limit(50);
    }

    /**
     * Get pinned messages for the chat group.
     */
    public function pinnedMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)
                    ->where('is_pinned', true)
                    ->where('is_deleted', false)
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get moderators of the chat group.
     */
    public function moderators(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'moderator');
    }

    /**
     * Get admins of the chat group.
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Check if user is a member of the group.
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is a moderator of the group.
     */
    public function isModerator(User $user): bool
    {
        return $this->users()
                   ->where('user_id', $user->id)
                   ->wherePivot('role', 'moderator')
                   ->exists();
    }

    /**
     * Check if user is an admin of the group.
     */
    public function isAdmin(User $user): bool
    {
        return $this->users()
                   ->where('user_id', $user->id)
                   ->wherePivot('role', 'admin')
                   ->exists();
    }

    /**
     * Add user to the group.
     */
    public function addMember(User $user, $role = 'member')
    {
        if (!$this->hasMember($user) && $this->users()->count() < $this->max_members) {
            $this->users()->attach($user->id, [
                'role' => $role,
                'joined_at' => now()
            ]);
        }
    }

    /**
     * Remove user from the group.
     */
    public function removeMember(User $user)
    {
        $this->users()->detach($user->id);
    }

    /**
     * Get unread message count for user.
     */
    public function getUnreadCountForUser(User $user): int
    {
        $membership = $this->users()->where('user_id', $user->id)->first();
        
        if (!$membership) {
            return 0;
        }

        $lastReadAt = $membership->pivot->last_read_at ?? $membership->pivot->joined_at;
        
        return $this->messages()
                   ->where('created_at', '>', $lastReadAt)
                   ->where('user_id', '!=', $user->id)
                   ->where('is_deleted', false)
                   ->count();
    }

    /**
     * Mark messages as read for user.
     */
    public function markAsReadForUser(User $user)
    {
        $this->users()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);
    }

    /**
     * Update last activity.
     */
    public function updateActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Scope for active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for subject-based groups.
     */
    public function scopeSubjectBased($query)
    {
        return $query->where('type', 'subject')->whereNotNull('subject_id');
    }

    /**
     * Create subject-based chat group.
     */
    public static function createSubjectGroup(Subject $subject, User $creator)
    {
        return self::create([
            'name' => $subject->name . ' Discussion',
            'description' => 'Discussion group for ' . $subject->name,
            'type' => 'subject',
            'subject_id' => $subject->id,
            'created_by' => $creator->id,
            'max_members' => 100,
            'is_active' => true,
            'is_moderated' => true,
        ]);
    }
}