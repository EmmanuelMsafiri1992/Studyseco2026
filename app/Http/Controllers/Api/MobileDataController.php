<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Department;
use App\Models\Quiz;
use App\Models\Assignment;
use Illuminate\Http\Request;

class MobileDataController extends Controller
{
    // Student Dashboard
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Get active enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->where('access_expires_at', '>', now())
                    ->orWhere(function ($q) {
                        $q->where('is_trial', true)
                            ->where('trial_expires_at', '>', now());
                    });
            })
            ->latest()
            ->first();

        // Get enrolled subject IDs
        $subjectIds = $enrollment ? ($enrollment->selected_subjects ?? []) : [];

        // Get subjects with progress
        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('is_active', true)
            ->withCount('topics', 'lessons')
            ->get()
            ->map(function ($subject) use ($user) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'cover_image' => $subject->cover_image ? asset('storage/' . $subject->cover_image) : null,
                    'topics_count' => $subject->topics_count,
                    'lessons_count' => $subject->lessons_count,
                    'progress' => 0, // TODO: Calculate actual progress
                ];
            });

        // Recent activity placeholder
        $recentActivity = [];

        // Stats
        $stats = [
            'enrolled_subjects' => count($subjectIds),
            'completed_lessons' => 0,
            'quiz_average' => 0,
            'study_time_hours' => 0,
        ];

        return response()->json([
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->profile_photo_url,
                ],
                'enrollment' => $enrollment ? [
                    'status' => $enrollment->status,
                    'is_trial' => $enrollment->is_trial,
                    'days_remaining' => $enrollment->is_trial
                        ? $enrollment->trial_days_remaining
                        : $enrollment->access_days_remaining,
                    'expires_at' => $enrollment->is_trial
                        ? $enrollment->trial_expires_at?->toIso8601String()
                        : $enrollment->access_expires_at?->toIso8601String(),
                ] : null,
                'subjects' => $subjects,
                'stats' => $stats,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }

    // Get all subjects
    public function subjects(Request $request)
    {
        $subjects = Subject::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'description' => $subject->description,
                    'department' => $subject->department,
                    'grade_level' => $subject->grade_level,
                    'cover_image' => $subject->cover_image ? asset('storage/' . $subject->cover_image) : null,
                    'is_compulsory' => $subject->is_compulsory,
                    'topics_count' => $subject->topics()->count(),
                    'lessons_count' => $subject->lessons()->count(),
                ];
            });

        return response()->json([
            'data' => $subjects,
        ]);
    }

    // Get single subject with topics
    public function subjectDetail(Request $request, $id)
    {
        $subject = Subject::with(['topics' => function ($query) {
            $query->orderBy('order_index');
        }, 'topics.lessons' => function ($query) {
            $query->where('is_published', true)->orderBy('order_index');
        }])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'department' => $subject->department,
                'grade_level' => $subject->grade_level,
                'cover_image' => $subject->cover_image ? asset('storage/' . $subject->cover_image) : null,
                'is_compulsory' => $subject->is_compulsory,
                'topics' => $subject->topics->map(function ($topic) {
                    return [
                        'id' => $topic->id,
                        'name' => $topic->name,
                        'description' => $topic->description,
                        'order' => $topic->order_index,
                        'lessons' => $topic->lessons->map(function ($lesson) {
                            return [
                                'id' => $lesson->id,
                                'title' => $lesson->title,
                                'description' => $lesson->description,
                                'duration_minutes' => $lesson->duration_minutes,
                                'video_url' => $lesson->video_url,
                                'order' => $lesson->order_index,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    // Get student's enrolled subjects
    public function studentSubjects(Request $request)
    {
        $user = $request->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->where('access_expires_at', '>', now())
                    ->orWhere(function ($q) {
                        $q->where('is_trial', true)
                            ->where('trial_expires_at', '>', now());
                    });
            })
            ->latest()
            ->first();

        $subjectIds = $enrollment ? ($enrollment->selected_subjects ?? []) : [];

        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('is_active', true)
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'description' => $subject->description,
                    'cover_image' => $subject->cover_image ? asset('storage/' . $subject->cover_image) : null,
                    'topics_count' => $subject->topics()->count(),
                    'lessons_count' => $subject->lessons()->count(),
                    'progress' => 0,
                ];
            });

        return response()->json([
            'data' => $subjects,
        ]);
    }

    // Get departments
    public function departments()
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'description' => $dept->description,
                ];
            });

        return response()->json([
            'data' => $departments,
        ]);
    }

    // Get notifications
    public function notifications(Request $request)
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $perPage = 20;

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'data' => $notification->data,
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            }),
            'unread_count' => $unreadCount,
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
        ]);
    }

    // Mark notification as read
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }

    // Mark all notifications as read
    public function markAllNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    // Delete notification
    public function deleteNotification(Request $request, $id)
    {
        $user = $request->user();
        $user->notifications()->where('id', $id)->delete();

        return response()->json([
            'message' => 'Notification deleted.',
        ]);
    }
}
