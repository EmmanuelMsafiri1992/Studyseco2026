<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'content', 'attachments', 'tags', 
        'subject_id', 'priority', 'is_anonymous', 'is_pinned', 'is_solved', 
        'is_featured', 'allows_comments', 'views_count', 'likes_count', 
        'comments_count', 'shares_count', 'poll_options', 'poll_votes', 
        'poll_expires_at', 'metadata'
    ];

    protected $casts = [
        'attachments' => 'array',
        'tags' => 'array',
        'is_anonymous' => 'boolean',
        'is_pinned' => 'boolean',
        'is_solved' => 'boolean',
        'is_featured' => 'boolean',
        'allows_comments' => 'boolean',
        'poll_options' => 'array',
        'poll_votes' => 'array',
        'poll_expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user who created this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject this post is related to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get all comments for this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->whereNull('parent_id')->with('replies');
    }

    /**
     * Get all reactions for this post.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    /**
     * Get shared resources for this post.
     */
    public function sharedResources(): HasMany
    {
        return $this->hasMany(SharedResource::class);
    }

    /**
     * Check if user has reacted to this post.
     */
    public function hasReaction(User $user, string $type = 'like'): bool
    {
        return $this->reactions()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->exists();
    }

    /**
     * Toggle reaction on this post.
     */
    public function toggleReaction(User $user, string $type = 'like')
    {
        $reaction = $this->reactions()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        if ($reaction) {
            $reaction->delete();
            $this->decrement('likes_count');
        } else {
            $this->reactions()->create([
                'user_id' => $user->id,
                'type' => $type
            ]);
            $this->increment('likes_count');
        }
    }

    /**
     * Increment view count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Mark as solved (for questions).
     */
    public function markAsSolved()
    {
        $this->update(['is_solved' => true]);
    }

    /**
     * Scope for specific post types.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for pinned posts.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope for posts by subject.
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope for recent posts.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get trending posts (high engagement recently).
     */
    public function scopeTrending($query, $days = 3)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
            ->orderBy('likes_count', 'desc')
            ->orderBy('comments_count', 'desc')
            ->orderBy('views_count', 'desc');
    }
}