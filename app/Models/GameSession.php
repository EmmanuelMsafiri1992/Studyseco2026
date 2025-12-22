<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSession extends Model
{
    protected $fillable = [
        'game_id',
        'student_id',
        'started_at',
        'completed_at',
        'score',
        'duration_seconds',
        'session_data',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'session_data' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Complete the game session
    public function complete(int $score, array $sessionData = [])
    {
        $duration = now()->diffInSeconds($this->started_at);

        $this->update([
            'completed_at' => now(),
            'score' => $score,
            'duration_seconds' => $duration,
            'session_data' => $sessionData,
            'status' => 'completed',
        ]);

        // Update or create game score record
        $this->updateGameScore();
    }

    // Update the game score leaderboard
    protected function updateGameScore()
    {
        $gameScore = GameScore::firstOrCreate([
            'game_id' => $this->game_id,
            'student_id' => $this->student_id,
        ], [
            'high_score' => 0,
            'total_plays' => 0,
            'total_time_played' => 0,
        ]);

        // Update high score if this is better
        if ($this->score > $gameScore->high_score) {
            $gameScore->high_score = $this->score;
        }

        $gameScore->total_plays += 1;
        $gameScore->total_time_played += $this->duration_seconds ?? 0;
        $gameScore->last_played_at = now();
        $gameScore->save();
    }

    // Get time spent in human-readable format
    public function getTimeSpent(): string
    {
        if (!$this->duration_seconds) return '0s';

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }
}
