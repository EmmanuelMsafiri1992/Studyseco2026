<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'community_post_id', 'type'
    ];

    /**
     * Get the user who made this reaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post this reaction belongs to.
     */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class);
    }
}