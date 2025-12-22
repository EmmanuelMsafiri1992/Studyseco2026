<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MockExam;
use App\Models\ExamQuestion;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MockExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = MockExam::with('subject')
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status !== null, fn($q) => $q->where('is_published', $request->status))
            ->latest()
            ->paginate(15);

        $subjects = Subject::all();

        return Inertia::render('Admin/MockExams/Index', compact('exams', 'subjects'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return Inertia::render('Admin/MockExams/Create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:1',
            'is_published' => 'boolean',
        ]);

        MockExam::create($validated);

        return redirect()->route('admin.mock-exams.index')->with('success', 'Mock exam created!');
    }

    public function edit(MockExam $mockExam)
    {
        $mockExam->load('questions', 'subject');
        $subjects = Subject::all();
        return Inertia::render('Admin/MockExams/Edit', compact('mockExam', 'subjects'));
    }

    public function update(Request $request, MockExam $mockExam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:1',
            'is_published' => 'boolean',
        ]);

        $mockExam->update($validated);

        return redirect()->route('admin.mock-exams.index')->with('success', 'Mock exam updated!');
    }

    public function destroy(MockExam $mockExam)
    {
        $mockExam->delete();
        return redirect()->route('admin.mock-exams.index')->with('success', 'Mock exam deleted!');
    }

    public function addQuestion(Request $request, MockExam $mockExam)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'marks' => 'required|integer|min:1',
            'topic' => 'nullable|string',
        ]);

        $validated['order'] = $mockExam->questions()->max('order') + 1;
        $mockExam->questions()->create($validated);

        return back()->with('success', 'Question added!');
    }

    public function updateQuestion(Request $request, MockExam $mockExam, ExamQuestion $question)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'marks' => 'required|integer|min:1',
            'topic' => 'nullable|string',
        ]);

        $question->update($validated);
        return back()->with('success', 'Question updated!');
    }

    public function deleteQuestion(MockExam $mockExam, ExamQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Question deleted!');
    }

    public function results(MockExam $mockExam)
    {
        $attempts = $mockExam->attempts()
            ->with('student')
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/MockExams/Results', compact('mockExam', 'attempts'));
    }
}
