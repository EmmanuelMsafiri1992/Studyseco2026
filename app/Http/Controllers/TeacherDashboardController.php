<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();
        
        // Ensure user is a teacher
        if ($teacher->role !== 'teacher') {
            abort(403, 'Access denied. Teacher access only.');
        }

        // Get assigned students
        $assignedStudents = Enrollment::where('assigned_tutor_id', $teacher->id)
            ->where('status', 'approved')
            ->with(['user', 'subject'])
            ->orderByDesc('tutor_assigned_at')
            ->get();

        // Calculate statistics
        $stats = [
            'total_assigned' => $assignedStudents->count(),
            'active_students' => $assignedStudents->where('access_expires_at', '>', now())->count(),
            'expired_students' => $assignedStudents->where('access_expires_at', '<=', now())->count(),
            'trial_students' => $assignedStudents->where('is_trial', true)->count(),
            'recent_assignments' => $assignedStudents->where('tutor_assigned_at', '>=', now()->subDays(7))->count(),
            'subjects_covered' => $assignedStudents->flatMap(function ($enrollment) {
                return $enrollment->selected_subjects ?: [];
            })->unique()->count()
        ];

        // Group students by status
        $activeStudents = $assignedStudents->filter(fn($e) => $e->access_expires_at > now());
        $expiredStudents = $assignedStudents->filter(fn($e) => $e->access_expires_at <= now());
        
        // Recent activities (mock data - in real implementation you'd have activity logs)
        $recentActivities = collect([
            [
                'id' => 1,
                'type' => 'assignment',
                'message' => 'New student assigned to you',
                'student' => $assignedStudents->first()?->user?->name ?? 'Student',
                'timestamp' => now()->subHours(2),
                'icon' => '👨‍🎓'
            ],
            [
                'id' => 2,
                'type' => 'question',
                'message' => 'Student asked question about Mathematics',
                'student' => $assignedStudents->skip(1)->first()?->user?->name ?? 'Student',
                'timestamp' => now()->subHours(5),
                'icon' => '❓'
            ]
        ])->take(5);

        return Inertia::render('Teacher/Dashboard', [
            'stats' => $stats,
            'activeStudents' => $activeStudents->values(),
            'expiredStudents' => $expiredStudents->values(),
            'recentActivities' => $recentActivities,
            'teacher' => $teacher
        ]);
    }

    public function students()
    {
        $teacher = auth()->user();
        
        if ($teacher->role !== 'teacher') {
            abort(403, 'Access denied. Teacher access only.');
        }

        $students = Enrollment::where('assigned_tutor_id', $teacher->id)
            ->where('status', 'approved')
            ->with(['user'])
            ->paginate(20);

        return Inertia::render('Teacher/Students/Index', [
            'students' => $students,
            'teacher' => $teacher
        ]);
    }

    public function studentDetail(Enrollment $enrollment)
    {
        $teacher = auth()->user();
        
        // Ensure this student is assigned to the teacher
        if ($enrollment->assigned_tutor_id !== $teacher->id) {
            abort(403, 'You can only view students assigned to you.');
        }

        $enrollment->load(['user', 'payments', 'extensions']);

        // Get student's learning progress (mock data)
        $progress = [
            'subjects_enrolled' => count($enrollment->selected_subjects ?: []),
            'lessons_completed' => rand(5, 25),
            'total_lessons' => rand(30, 50),
            'last_activity' => now()->subDays(rand(1, 7)),
            'completion_percentage' => rand(20, 85)
        ];

        // Recent student activities
        $studentActivities = collect([
            [
                'type' => 'lesson_completed',
                'title' => 'Completed Mathematics Lesson 3.2',
                'timestamp' => now()->subHours(3),
                'icon' => '✅'
            ],
            [
                'type' => 'quiz_attempted',
                'title' => 'Attempted Chemistry Quiz - Score: 85%',
                'timestamp' => now()->subHours(8),
                'icon' => '📝'
            ],
            [
                'type' => 'ai_chat',
                'title' => 'Asked AI tutor about algebra equations',
                'timestamp' => now()->subDay(),
                'icon' => '🤖'
            ]
        ]);

        return Inertia::render('Teacher/Students/Detail', [
            'enrollment' => $enrollment,
            'progress' => $progress,
            'activities' => $studentActivities,
            'teacher' => $teacher
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000'
        ]);

        $teacher = auth()->user();
        $enrollment = Enrollment::findOrFail($request->enrollment_id);

        // Ensure this student is assigned to the teacher
        if ($enrollment->assigned_tutor_id !== $teacher->id) {
            abort(403, 'You can only message students assigned to you.');
        }

        // In a real implementation, you'd save this to a messages table
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully to ' . ($enrollment->user?->name ?? $enrollment->name)
        ]);
    }

    public function bookSession(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'session_date' => 'required|date|after:now',
            'duration' => 'required|integer|min:30|max:180',
            'topic' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $teacher = auth()->user();
        $enrollment = Enrollment::findOrFail($request->enrollment_id);

        // Ensure this student is assigned to the teacher
        if ($enrollment->assigned_tutor_id !== $teacher->id) {
            abort(403, 'You can only book sessions with students assigned to you.');
        }

        // In a real implementation, you'd save this to a sessions table
        
        return response()->json([
            'success' => true,
            'message' => 'Tutoring session booked successfully with ' . ($enrollment->user?->name ?? $enrollment->name)
        ]);
    }
}