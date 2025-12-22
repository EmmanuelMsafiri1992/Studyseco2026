<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Achievement::with(['user', 'awardedBy'])
            ->where('is_public', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('achieved_date')
            ->orderBy('display_order');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $achievements = $query->paginate(12);

        // Get role-based statistics
        $stats = $this->getRoleBasedStats($user);

        // Get achievement types for filtering
        $types = Achievement::distinct()->pluck('type');
        $levels = Achievement::distinct()->pluck('level');

        return Inertia::render('Achievements/Index', [
            'achievements' => $achievements,
            'stats' => $stats,
            'types' => $types,
            'levels' => $levels,
            'filters' => $request->only(['type', 'level', 'search']),
            'userRole' => $user ? $user->role : null,
            'personalAchievements' => $user ? $this->getPersonalAchievements($user) : null
        ]);
    }

    private function getRoleBasedStats($user)
    {
        $baseStats = [
            'total_achievements' => Achievement::where('is_public', true)->count(),
            'featured_achievements' => Achievement::where('is_public', true)->where('is_featured', true)->count(),
            'total_points' => Achievement::where('is_public', true)->sum('points'),
            'recent_achievements' => Achievement::where('is_public', true)
                ->where('achieved_date', '>=', now()->subDays(7))
                ->count()
        ];

        if (!$user) {
            return $baseStats;
        }

        if ($user->role === 'student') {
            // Students see their completed subjects and personal achievements
            $baseStats['my_achievements'] = Achievement::where('user_id', $user->id)->count();
            $baseStats['my_points'] = Achievement::where('user_id', $user->id)->sum('points');
            $baseStats['subjects_completed'] = $this->getCompletedSubjectsCount($user);
            $baseStats['completion_percentage'] = $this->getCompletionPercentage($user);
        } elseif ($user->role === 'teacher') {
            // Teachers see total support completed and students they've helped
            $baseStats['students_supported'] = User::whereHas('achievements', function ($query) use ($user) {
                $query->where('awarded_by', $user->id);
            })->count();
            $baseStats['total_support_completed'] = Achievement::where('awarded_by', $user->id)->count();
            $baseStats['support_points_awarded'] = Achievement::where('awarded_by', $user->id)->sum('points');
        } elseif (in_array($user->role, ['admin', 'super_admin'])) {
            // Admins see total support completed and system-wide metrics
            $baseStats['total_support_completed'] = Achievement::count();
            $baseStats['total_users'] = User::count();
            $baseStats['active_teachers'] = User::where('role', 'teacher')->count();
            $baseStats['active_students'] = User::where('role', 'student')->count();
        }

        return $baseStats;
    }

    private function getPersonalAchievements($user)
    {
        return Achievement::where('user_id', $user->id)
            ->with(['awardedBy'])
            ->orderByDesc('achieved_date')
            ->limit(5)
            ->get();
    }

    private function getCompletedSubjectsCount($user)
    {
        // Get completed subjects from enrollments
        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();
            
        if (!$enrollment || !$enrollment->selected_subjects) {
            return 0;
        }
        
        return count($enrollment->selected_subjects);
    }

    private function getCompletionPercentage($user)
    {
        $totalSubjects = \DB::table('subjects')->count();
        $completedSubjects = $this->getCompletedSubjectsCount($user);
        
        return $totalSubjects > 0 ? round(($completedSubjects / $totalSubjects) * 100, 2) : 0;
    }

    public function show(Achievement $achievement)
    {
        if (!$achievement->is_public) {
            abort(404);
        }

        $achievement->load(['user', 'awardedBy']);
        
        // Get related achievements
        $relatedAchievements = Achievement::where('is_public', true)
            ->where('id', '!=', $achievement->id)
            ->where(function ($query) use ($achievement) {
                $query->where('type', $achievement->type)
                      ->orWhere('category', $achievement->category)
                      ->orWhere('user_id', $achievement->user_id);
            })
            ->with(['user', 'awardedBy'])
            ->limit(6)
            ->get();

        return Inertia::render('Achievements/Show', [
            'achievement' => $achievement,
            'related' => $relatedAchievements
        ]);
    }

    public function leaderboard()
    {
        // Get top students by achievement points
        $topStudents = User::whereHas('achievements', function ($query) {
                $query->where('is_public', true);
            })
            ->withSum(['achievements' => function ($query) {
                $query->where('is_public', true);
            }], 'points')
            ->withCount(['achievements' => function ($query) {
                $query->where('is_public', true);
            }])
            ->orderByDesc('achievements_sum_points')
            ->take(20)
            ->get();

        // Get achievement distribution
        $achievementTypes = Achievement::where('is_public', true)
            ->selectRaw('type, count(*) as count, sum(points) as total_points')
            ->groupBy('type')
            ->get();

        // Get recent achievements
        $recentAchievements = Achievement::where('is_public', true)
            ->with(['user', 'awardedBy'])
            ->orderByDesc('achieved_date')
            ->take(10)
            ->get();

        return Inertia::render('Achievements/Leaderboard', [
            'topStudents' => $topStudents,
            'achievementTypes' => $achievementTypes,
            'recentAchievements' => $recentAchievements
        ]);
    }
}
