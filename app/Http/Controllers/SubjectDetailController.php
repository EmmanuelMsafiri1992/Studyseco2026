<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Subject;
use App\Models\Enrollment;

class SubjectDetailController extends Controller
{
    public function show($subjectId)
    {
        $user = auth()->user();
        
        // Check if user has enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'Please complete your enrollment first.');
        }

        // Get the subject with topics and lessons
        $subject = Subject::with(['topics.lessons' => function($query) {
            $query->where('is_published', true)->orderBy('order_index');
        }])
        ->where('id', $subjectId)
        ->where('is_active', true)
        ->first();

        if (!$subject) {
            abort(404, 'Subject not found');
        }

        // Check if user is enrolled in this subject
        $enrolledSubjectIds = $enrollment->selected_subjects ?: [];
        $isEnrolled = in_array($subjectId, $enrolledSubjectIds);

        if (!$isEnrolled && !$enrollment->is_trial) {
            return redirect()->route('student.subjects.index')
                ->with('error', 'You are not enrolled in this subject.');
        }

        // Get subject icon
        $icon = match($subject->name) {
            'English' => '📚',
            'Chichewa' => '📖',
            'Mathematics' => '📐',
            'Life Skills' => '🧠',
            'Biology' => '🧬',
            'Physical Science' => '⚗️',
            'Chemistry' => '🧪',
            'Physics' => '⚡',
            'Geography' => '🗺️',
            'History' => '🏛️',
            'Social Studies' => '🌍',
            'Bible Knowledge' => '✝️',
            'Agriculture' => '🌾',
            'Home Economics' => '🏠',
            'Technical Drawing' => '📏',
            'Business Studies' => '💼',
            'French' => '🇫🇷',
            'Computer Studies' => '💻',
            default => '📚'
        };

        // Calculate statistics
        $totalTopics = $subject->topics->count();
        $totalLessons = $subject->topics->sum(function($topic) {
            return $topic->lessons->count();
        });
        $totalDuration = $subject->topics->sum(function($topic) {
            return $topic->lessons->sum('duration_minutes');
        });

        // Get related subjects (same department)
        $relatedSubjects = Subject::where('department', $subject->department)
            ->where('id', '!=', $subject->id)
            ->where('is_active', true)
            ->whereIn('id', $enrolledSubjectIds)
            ->limit(3)
            ->get()
            ->map(function ($relatedSubject) {
                return [
                    'id' => $relatedSubject->id,
                    'name' => $relatedSubject->name,
                    'description' => $relatedSubject->description,
                    'icon' => match($relatedSubject->name) {
                        'English' => '📚',
                        'Chichewa' => '📖',
                        'Mathematics' => '📐',
                        'Life Skills' => '🧠',
                        'Biology' => '🧬',
                        'Physical Science' => '⚗️',
                        'Chemistry' => '🧪',
                        'Physics' => '⚡',
                        'Geography' => '🗺️',
                        'History' => '🏛️',
                        'Social Studies' => '🌍',
                        'Bible Knowledge' => '✝️',
                        'Agriculture' => '🌾',
                        'Home Economics' => '🏠',
                        'Technical Drawing' => '📏',
                        'Business Studies' => '💼',
                        'French' => '🇫🇷',
                        'Computer Studies' => '💻',
                        default => '📚'
                    }
                ];
            });

        $subjectData = [
            'id' => $subject->id,
            'name' => $subject->name,
            'description' => $subject->description,
            'icon' => $icon,
            'department' => $subject->department,
            'grade_level' => $subject->grade_level,
            'detailed_description' => $subject->description,
            'topics_count' => $totalTopics,
            'lessons_count' => $totalLessons,
            'total_duration' => $totalDuration,
            'topics' => $subject->topics->map(function($topic) {
                return [
                    'id' => $topic->id,
                    'name' => $topic->name,
                    'description' => $topic->description,
                    'order_index' => $topic->order_index,
                    'lessons_count' => $topic->lessons->count(),
                    'lessons' => $topic->lessons->map(function($lesson) {
                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'description' => $lesson->description,
                            'duration_minutes' => $lesson->duration_minutes,
                            'video_path' => $lesson->video_path,
                            'notes' => $lesson->notes,
                            'order_index' => $lesson->order_index
                        ];
                    })
                ];
            })
        ];

        return Inertia::render('Student/Subjects/Detail', [
            'subject' => $subjectData,
            'relatedSubjects' => $relatedSubjects,
            'enrollment' => [
                'status' => $enrollment->status,
                'is_trial' => $enrollment->is_trial,
                'access_expires_at' => $enrollment->access_expires_at,
                'access_days_remaining' => $enrollment->access_days_remaining,
            ]
        ]);
    }
}
