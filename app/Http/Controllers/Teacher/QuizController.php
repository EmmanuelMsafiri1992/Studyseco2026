<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $query = Quiz::with(['subject', 'questions'])
            ->where('teacher_id', auth()->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $quizzes = $query->latest()->paginate(10);
        $subjects = Subject::all();

        return Inertia::render('Teacher/Quizzes/Index', [
            'quizzes' => $quizzes,
            'subjects' => $subjects,
            'filters' => $request->only(['status', 'subject_id', 'search'])
        ]);
    }

    public function create()
    {
        $subjects = Subject::all();

        return Inertia::render('Teacher/Quizzes/Create', [
            'subjects' => $subjects
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'total_points' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0',
            'show_correct_answers' => 'boolean',
            'shuffle_questions' => 'boolean',
            'max_attempts' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        $validated['teacher_id'] = auth()->id();

        $quiz = Quiz::create($validated);

        return redirect()->route('teacher.quizzes.edit', $quiz->id)
            ->with('success', 'Quiz created successfully. Now add questions.');
    }

    public function edit(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $quiz->load('questions', 'subject');
        $subjects = Subject::all();

        return Inertia::render('Teacher/Quizzes/Edit', [
            'quiz' => $quiz,
            'subjects' => $subjects
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'total_points' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0',
            'show_correct_answers' => 'boolean',
            'shuffle_questions' => 'boolean',
            'max_attempts' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        $quiz->update($validated);

        return back()->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return redirect()->route('teacher.quizzes.index')
            ->with('success', 'Quiz deleted successfully.');
    }

    public function show(Quiz $quiz)
    {
        $this->authorize('view', $quiz);

        $quiz->load(['subject', 'questions', 'attempts.student']);

        $stats = [
            'total_attempts' => $quiz->attempts()->where('status', 'completed')->count(),
            'average_score' => $quiz->getAverageScore(),
            'pass_rate' => $this->calculatePassRate($quiz),
            'total_students' => $quiz->attempts()->distinct('student_id')->count(),
        ];

        $recentAttempts = $quiz->attempts()
            ->with('student')
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Teacher/Quizzes/Show', [
            'quiz' => $quiz,
            'stats' => $stats,
            'recentAttempts' => $recentAttempts
        ]);
    }

    public function addQuestion(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
        ]);

        $validated['quiz_id'] = $quiz->id;
        $validated['order'] = $quiz->questions()->max('order') + 1;

        QuizQuestion::create($validated);

        return back()->with('success', 'Question added successfully.');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
        ]);

        $question->update($validated);

        return back()->with('success', 'Question updated successfully.');
    }

    public function deleteQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $this->authorize('update', $quiz);

        $question->delete();

        // Reorder remaining questions
        $quiz->questions()->where('order', '>', $question->order)
            ->decrement('order');

        return back()->with('success', 'Question deleted successfully.');
    }

    public function results(Quiz $quiz)
    {
        $this->authorize('view', $quiz);

        $attempts = $quiz->attempts()
            ->with(['student', 'quizAnswers.question'])
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return Inertia::render('Teacher/Quizzes/Results', [
            'quiz' => $quiz,
            'attempts' => $attempts
        ]);
    }

    private function calculatePassRate(Quiz $quiz): float
    {
        $totalCompleted = $quiz->attempts()->where('status', 'completed')->count();

        if ($totalCompleted === 0) {
            return 0;
        }

        $passed = $quiz->attempts()
            ->where('status', 'completed')
            ->where('score', '>=', $quiz->passing_score)
            ->count();

        return round(($passed / $totalCompleted) * 100, 2);
    }
}
