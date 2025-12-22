<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameScore;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        // Get student's enrolled subjects
        $enrollment = Enrollment::where('user_id', $student->id)
            ->orWhere('email', $student->email)
            ->first();

        $enrolledSubjectIds = $enrollment && $enrollment->selected_subjects
            ? $enrollment->selected_subjects
            : [];

        $games = Game::with('subject')
            ->active()
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->get()
            ->map(function($game) use ($student) {
                $myScore = GameScore::where('game_id', $game->id)
                    ->where('student_id', $student->id)
                    ->first();

                $lastSession = GameSession::where('game_id', $game->id)
                    ->where('student_id', $student->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'description' => $game->description,
                    'subject' => $game->subject,
                    'game_type' => $game->game_type,
                    'game_type_label' => $game->getGameTypeLabel(),
                    'max_score' => $game->max_score,
                    'time_limit' => $game->time_limit,
                    'my_high_score' => $myScore->high_score ?? 0,
                    'my_total_plays' => $myScore->total_plays ?? 0,
                    'last_played' => $myScore->last_played_at ?? null,
                    'last_session' => $lastSession,
                ];
            });

        return Inertia::render('Student/Games/Index', compact('games'));
    }

    public function show(Game $game)
    {
        if (!$game->is_active) {
            return redirect()->route('student.games.index')
                ->with('error', 'This game is not available.');
        }

        $game->load('subject');
        $student = auth()->user();

        // Get student's stats for this game
        $myScore = GameScore::where('game_id', $game->id)
            ->where('student_id', $student->id)
            ->first();

        // Get leaderboard
        $leaderboard = $game->getLeaderboard(10);

        // Get recent sessions
        $recentSessions = GameSession::where('game_id', $game->id)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Student/Games/Show', [
            'game' => $game,
            'myScore' => $myScore,
            'leaderboard' => $leaderboard,
            'recentSessions' => $recentSessions,
        ]);
    }

    public function start(Game $game)
    {
        if (!$game->is_active) {
            return back()->with('error', 'This game is not available.');
        }

        // Create new game session
        $session = GameSession::create([
            'game_id' => $game->id,
            'student_id' => auth()->id(),
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('student.games.play', $session->id);
    }

    public function play(GameSession $session)
    {
        if ($session->student_id !== auth()->id()) abort(403);
        if ($session->status !== 'in_progress') {
            return redirect()->route('student.games.result', $session->id);
        }

        $game = $session->game()->with('subject')->first();

        return Inertia::render('Student/Games/Play', [
            'session' => $session,
            'game' => $game,
            'config' => $game->game_config,
        ]);
    }

    public function submit(Request $request, GameSession $session)
    {
        if ($session->student_id !== auth()->id()) abort(403);
        if ($session->status !== 'in_progress') {
            return back()->with('error', 'This session has already been completed.');
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0',
            'session_data' => 'nullable|array',
        ]);

        $session->complete($validated['score'], $validated['session_data'] ?? []);

        return redirect()->route('student.games.result', $session->id)
            ->with('success', 'Game completed!');
    }

    public function result(GameSession $session)
    {
        if ($session->student_id !== auth()->id()) abort(403);

        $session->load(['game.subject']);
        $game = $session->game;

        // Get updated leaderboard position
        $myScore = GameScore::where('game_id', $game->id)
            ->where('student_id', auth()->id())
            ->first();

        $rank = GameScore::where('game_id', $game->id)
            ->where('high_score', '>', $myScore->high_score ?? 0)
            ->count() + 1;

        return Inertia::render('Student/Games/Result', [
            'session' => $session,
            'game' => $game,
            'myScore' => $myScore,
            'rank' => $rank,
            'isNewHighScore' => $session->score >= ($myScore->high_score ?? 0),
        ]);
    }

    public function leaderboard(Game $game)
    {
        $game->load('subject');
        $leaderboard = $game->getLeaderboard(50);

        // Get my rank
        $myScore = GameScore::where('game_id', $game->id)
            ->where('student_id', auth()->id())
            ->first();

        $myRank = null;
        if ($myScore) {
            $myRank = GameScore::where('game_id', $game->id)
                ->where('high_score', '>', $myScore->high_score)
                ->count() + 1;
        }

        return Inertia::render('Student/Games/Leaderboard', [
            'game' => $game,
            'leaderboard' => $leaderboard,
            'myScore' => $myScore,
            'myRank' => $myRank,
        ]);
    }
}
