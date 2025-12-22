<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user();

        // Get student's enrolled subjects
        $enrollment = Enrollment::where('user_id', $student->id)
            ->orWhere('email', $student->email)
            ->first();

        if (!$enrollment || !$enrollment->selected_subjects) {
            return Inertia::render('Student/Assignments/Index', [
                'assignments' => [],
                'message' => 'You need to be enrolled to view assignments.'
            ]);
        }

        $subjectIds = $enrollment->selected_subjects;

        $query = Assignment::with(['subject', 'teacher'])
            ->whereIn('subject_id', $subjectIds)
            ->where('status', 'published')
            ->orderBy('due_date', 'asc');

        // Filter by status
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'pending':
                    $query->whereDoesntHave('submissions', function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    });
                    break;
                case 'submitted':
                    $query->whereHas('submissions', function ($q) use ($student) {
                        $q->where('student_id', $student->id)
                          ->where('status', 'pending');
                    });
                    break;
                case 'graded':
                    $query->whereHas('submissions', function ($q) use ($student) {
                        $q->where('student_id', $student->id)
                          ->where('status', 'graded');
                    });
                    break;
            }
        }

        $assignments = $query->get()->map(function ($assignment) use ($student) {
            $submission = $assignment->submissions()
                ->where('student_id', $student->id)
                ->first();

            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'subject' => $assignment->subject,
                'teacher' => $assignment->teacher,
                'total_points' => $assignment->total_points,
                'due_date' => $assignment->due_date,
                'status' => $assignment->status,
                'allow_late_submissions' => $assignment->allow_late_submissions,
                'attachment_url' => $assignment->attachment_url ? asset('storage/' . $assignment->attachment_url) : null,
                'my_submission' => $submission,
                'is_overdue' => $assignment->due_date < now() && !$submission
            ];
        });

        return Inertia::render('Student/Assignments/Index', [
            'assignments' => $assignments,
            'filters' => $request->only(['filter'])
        ]);
    }

    public function show(Assignment $assignment)
    {
        $student = auth()->user();

        if ($assignment->status !== 'published') {
            abort(404, 'Assignment not found.');
        }

        $assignment->load(['subject', 'teacher']);

        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        return Inertia::render('Student/Assignments/Show', [
            'assignment' => $assignment,
            'submission' => $submission,
            'can_submit' => !$submission && ($assignment->due_date > now() || $assignment->allow_late_submissions)
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $student = auth()->user();

        // Check if already submitted
        $existingSubmission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return back()->withErrors(['error' => 'You have already submitted this assignment.']);
        }

        // Check if deadline passed and late submissions not allowed
        if ($assignment->due_date < now() && !$assignment->allow_late_submissions) {
            return back()->withErrors(['error' => 'The deadline for this assignment has passed.']);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,zip,jpg,png|max:10240'
        ]);

        $submissionData = [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => $validated['content'] ?? null,
            'submitted_at' => now(),
            'status' => 'pending'
        ];

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('submissions', 'public');
            $submissionData['attachment_url'] = $path;
        }

        AssignmentSubmission::create($submissionData);

        return redirect()->route('student.assignments.index')
            ->with('success', 'Assignment submitted successfully!');
    }

    public function viewSubmission(Assignment $assignment)
    {
        $student = auth()->user();

        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->with(['gradedBy'])
            ->first();

        if (!$submission) {
            return redirect()->route('student.assignments.show', $assignment);
        }

        return Inertia::render('Student/Assignments/ViewSubmission', [
            'assignment' => $assignment->load(['subject', 'teacher']),
            'submission' => $submission
        ]);
    }
}
