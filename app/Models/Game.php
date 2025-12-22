<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'name',
        'description',
        'subject_id',
        'game_type',
        'game_config',
        'max_score',
        'time_limit',
        'is_active',
    ];

    protected $casts = [
        'game_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    // Scope for active games
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get average score for this game
    public function getAverageScore()
    {
        return $this->sessions()
            ->where('status', 'completed')
            ->avg('score') ?? 0;
    }

    // Get total plays
    public function getTotalPlays()
    {
        return $this->sessions()
            ->where('status', 'completed')
            ->count();
    }

    // Get leaderboard for this game
    public function getLeaderboard($limit = 10)
    {
        return $this->scores()
            ->with('student')
            ->orderBy('high_score', 'desc')
            ->limit($limit)
            ->get();
    }

    // Get game type label
    public function getGameTypeLabel(): string
    {
        return match($this->game_type) {
            'trivia' => 'Trivia Quiz',
            'math_challenge' => 'Math Challenge',
            'word_puzzle' => 'Word Puzzle',
            'memory' => 'Memory Game',
            default => 'Game',
        };
    }
}
