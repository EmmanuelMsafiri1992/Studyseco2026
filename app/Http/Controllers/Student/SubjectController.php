<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Enrollment;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'Please complete your enrollment first.');
        }

        if ($enrollment->status !== 'approved' && !$enrollment->is_trial) {
            return redirect()->route('student.enrollment.show')
                ->with('error', 'Your enrollment is pending approval.');
        }

        $enrolledSubjectIds = $enrollment->selected_subjects ?: [];
        $enrolledSubjects = Subject::whereIn('id', $enrolledSubjectIds)
            ->where('is_active', true)
            ->orderBy('department')
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'description' => $subject->description,
                    'department' => $subject->department,
                    'grade_level' => $subject->grade_level,
                    'topics_count' => $subject->topics()->count(),
                    'icon' => match($subject->name) {
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
                    },
                ];
            });

        return Inertia::render('Student/Subjects/Index', [
            'subjects' => $enrolledSubjects,
            'enrollment' => [
                'status' => $enrollment->status,
                'is_trial' => $enrollment->is_trial,
                'access_expires_at' => $enrollment->access_expires_at,
                'access_days_remaining' => $enrollment->access_days_remaining,
            ],
            'stats' => [
                'total_subjects' => count($enrolledSubjectIds),
                'departments' => $enrolledSubjects->groupBy('department')->count(),
            ]
        ]);
    }
}