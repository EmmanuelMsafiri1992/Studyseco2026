<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    protected $fillable = [
        'game_id',
        'student_id',
        'high_score',
        'total_plays',
        'total_time_played',
        'last_played_at',
    ];

    protected $casts = [
        'last_played_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Get average score per play
    public function getAverageScore(): float
    {
        if ($this->total_plays == 0) return 0;

        // This is an approximation since we only store high score
        // In reality, we'd need to calculate from all sessions
        return $this->high_score * 0.7; // Assume average is 70% of high score
    }

    // Get average time per play
    public function getAverageTime(): int
    {
        if ($this->total_plays == 0) return 0;

        return (int) ($this->total_time_played / $this->total_plays);
    }

    // Get formatted total time played
    public function getFormattedTotalTime(): string
    {
        $hours = floor($this->total_time_played / 3600);
        $minutes = floor(($this->total_time_played % 3600) / 60);
        $seconds = $this->total_time_played % 60;

        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($seconds > 0 || empty($parts)) $parts[] = "{$seconds}s";

        return implode(' ', $parts);
    }
}
