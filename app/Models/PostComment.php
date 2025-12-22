<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'community_post_id', 'parent_id', 'content', 
        'attachments', 'is_solution', 'is_pinned', 'likes_count'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_solution' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the user who made this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post this comment belongs to.
     */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    /**
     * Get replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_id')->with('user');
    }

    /**
     * Mark this comment as the solution.
     */
    public function markAsSolution()
    {
        $this->update(['is_solution' => true]);
        $this->communityPost->markAsSolved();
    }

    /**
     * Scope for top-level comments.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for solution comments.
     */
    public function scopeSolutions($query)
    {
        return $query->where('is_solution', true);
    }
}