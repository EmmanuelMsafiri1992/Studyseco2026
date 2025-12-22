<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with('subject')
            ->withCount(['sessions' => function($q) {
                $q->where('status', 'completed');
            }])
            ->latest()
            ->get()
            ->map(function($game) {
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'description' => $game->description,
                    'subject' => $game->subject,
                    'game_type' => $game->game_type,
                    'game_type_label' => $game->getGameTypeLabel(),
                    'max_score' => $game->max_score,
                    'time_limit' => $game->time_limit,
                    'is_active' => $game->is_active,
                    'total_plays' => $game->sessions_count,
                    'average_score' => $game->getAverageScore(),
                ];
            });

        return Inertia::render('Admin/Games/Index', compact('games'));
    }

    public function create()
    {
        $subjects = Subject::all();

        return Inertia::render('Admin/Games/Create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'game_type' => 'required|in:trivia,math_challenge,word_puzzle,memory',
            'game_config' => 'nullable|array',
            'max_score' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        Game::create($validated);

        return redirect()->route('admin.games.index')
            ->with('success', 'Game created successfully!');
    }

    public function edit(Game $game)
    {
        $game->load('subject');
        $subjects = Subject::all();

        return Inertia::render('Admin/Games/Edit', compact('game', 'subjects'));
    }

    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'game_type' => 'required|in:trivia,math_challenge,word_puzzle,memory',
            'game_config' => 'nullable|array',
            'max_score' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $game->update($validated);

        return redirect()->route('admin.games.index')
            ->with('success', 'Game updated successfully!');
    }

    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('admin.games.index')
            ->with('success', 'Game deleted successfully!');
    }

    public function leaderboard(Game $game)
    {
        $game->load('subject');
        $leaderboard = $game->getLeaderboard(50);

        return Inertia::render('Admin/Games/Leaderboard', compact('game', 'leaderboard'));
    }
}
