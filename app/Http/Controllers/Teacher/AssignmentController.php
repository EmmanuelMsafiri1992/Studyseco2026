<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user();

        if ($teacher->role !== 'teacher') {
            abort(403, 'Only teachers can access assignments.');
        }

        $query = Assignment::with(['subject', 'submissions'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $assignments = $query->paginate(15);

        // Get subjects for filter
        $subjects = Subject::where('is_active', true)->get();

        return Inertia::render('Teacher/Assignments/Index', [
            'assignments' => $assignments,
            'subjects' => $subjects,
            'filters' => $request->only(['status', 'subject_id', 'search'])
        ]);
    }

    public function create()
    {
        $teacher = auth()->user();

        if ($teacher->role !== 'teacher') {
            abort(403, 'Only teachers can create assignments.');
        }

        $subjects = Subject::where('is_active', true)->get();

        return Inertia::render('Teacher/Assignments/Create', [
            'subjects' => $subjects
        ]);
    }

    public function store(Request $request)
    {
        $teacher = auth()->user();

        if ($teacher->role !== 'teacher') {
            abort(403, 'Only teachers can create assignments.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'total_points' => 'required|integer|min:1|max:1000',
            'due_date' => 'required|date|after:now',
            'status' => 'required|in:draft,published',
            'allow_late_submissions' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,zip|max:10240'
        ]);

        $validated['teacher_id'] = $teacher->id;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('assignments', 'public');
            $validated['attachment_url'] = $path;
        }

        $assignment = Assignment::create($validated);

        return redirect()->route('teacher.assignments.show', $assignment)
            ->with('success', 'Assignment created successfully!');
    }

    public function show(Assignment $assignment)
    {
        $teacher = auth()->user();

        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'You can only view your own assignments.');
        }

        $assignment->load(['subject', 'submissions.student', 'submissions.gradedBy']);

        $stats = [
            'total_submissions' => $assignment->submissions()->count(),
            'pending_grading' => $assignment->submissions()->where('status', 'pending')->count(),
            'graded' => $assignment->submissions()->where('status', 'graded')->count(),
            'average_score' => $assignment->submissions()->where('status', 'graded')->avg('score')
        ];

        return Inertia::render('Teacher/Assignments/Show', [
            'assignment' => $assignment,
            'stats' => $stats
        ]);
    }

    public function edit(Assignment $assignment)
    {
        $teacher = auth()->user();

        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'You can only edit your own assignments.');
        }

        $subjects = Subject::where('is_active', true)->get();

        return Inertia::render('Teacher/Assignments/Edit', [
            'assignment' => $assignment,
            'subjects' => $subjects
        ]);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $teacher = auth()->user();

        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'You can only update your own assignments.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'total_points' => 'required|integer|min:1|max:1000',
            'due_date' => 'required|date',
            'status' => 'required|in:draft,published,closed',
            'allow_late_submissions' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,zip|max:10240'
        ]);

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($assignment->attachment_url && Storage::exists('public/' . $assignment->attachment_url)) {
                Storage::delete('public/' . $assignment->attachment_url);
            }

            $path = $request->file('attachment')->store('assignments', 'public');
            $validated['attachment_url'] = $path;
        }

        $assignment->update($validated);

        return redirect()->route('teacher.assignments.show', $assignment)
            ->with('success', 'Assignment updated successfully!');
    }

    public function destroy(Assignment $assignment)
    {
        $teacher = auth()->user();

        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'You can only delete your own assignments.');
        }

        // Delete attachment if exists
        if ($assignment->attachment_url && Storage::exists('public/' . $assignment->attachment_url)) {
            Storage::delete('public/' . $assignment->attachment_url);
        }

        $assignment->delete();

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'Assignment deleted successfully!');
    }

    public function submissions(Assignment $assignment)
    {
        $teacher = auth()->user();

        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'You can only view submissions for your own assignments.');
        }

        $submissions = $assignment->submissions()
            ->with(['student'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        return Inertia::render('Teacher/Assignments/Submissions', [
            'assignment' => $assignment,
            'submissions' => $submissions
        ]);
    }

    public function gradeSubmission(Request $request, AssignmentSubmission $submission)
    {
        $teacher = auth()->user();

        if ($submission->assignment->teacher_id !== $teacher->id) {
            abort(403, 'You can only grade submissions for your own assignments.');
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:' . $submission->assignment->total_points,
            'teacher_feedback' => 'nullable|string'
        ]);

        $submission->update([
            'score' => $validated['score'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
            'status' => 'graded',
            'graded_by' => $teacher->id,
            'graded_at' => now()
        ]);

        return back()->with('success', 'Submission graded successfully!');
    }
}
