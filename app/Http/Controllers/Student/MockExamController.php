<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MockExam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MockExamController extends Controller
{
    public function index()
    {
        $exams = MockExam::published()
            ->with('subject')
            ->withCount('questions')
            ->get()
            ->map(function($exam) {
                $exam->last_attempt = ExamAttempt::where('mock_exam_id', $exam->id)
                    ->where('student_id', auth()->id())
                    ->where('status', 'completed')
                    ->latest()
                    ->first();
                return $exam;
            });

        return Inertia::render('Student/MockExams/Index', compact('exams'));
    }

    public function show(MockExam $mockExam)
    {
        $mockExam->load('subject');
        $mockExam->loadCount('questions');

        $lastAttempt = ExamAttempt::where('mock_exam_id', $mockExam->id)
            ->where('student_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->first();

        return Inertia::render('Student/MockExams/Show', compact('mockExam', 'lastAttempt'));
    }

    public function start(MockExam $mockExam)
    {
        $attempt = ExamAttempt::create([
            'mock_exam_id' => $mockExam->id,
            'student_id' => auth()->id(),
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('student.mock-exams.take', $attempt);
    }

    public function take(ExamAttempt $attempt)
    {
        if ($attempt->student_id !== auth()->id()) abort(403);
        if ($attempt->status !== 'in_progress') abort(403);

        $exam = $attempt->mockExam()->with('questions')->first();
        $timeRemaining = ($exam->duration_minutes * 60) - now()->diffInSeconds($attempt->started_at);

        if ($timeRemaining <= 0) {
            $attempt->update(['status' => 'time_expired']);
            return redirect()->route('student.mock-exams.result', $attempt);
        }

        $questions = $exam->questions->map(function($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'options' => $q->options,
                'marks' => $q->marks,
                'topic' => $q->topic
            ];
        });

        return Inertia::render('Student/MockExams/Take', compact('attempt', 'exam', 'questions', 'timeRemaining'));
    }

    public function submit(Request $request, ExamAttempt $attempt)
    {
        if ($attempt->student_id !== auth()->id()) abort(403);
        if ($attempt->status !== 'in_progress') abort(403);

        $validated = $request->validate(['answers' => 'required|array']);

        $mockExam = $attempt->mockExam()->with('questions')->first();
        $score = 0;
        $topicScores = [];

        foreach ($mockExam->questions as $question) {
            $answer = $validated['answers'][$question->id] ?? '';
            $isCorrect = $question->checkAnswer($answer);

            if ($isCorrect) {
                $score += $question->marks;
            } else {
                if ($question->topic) {
                    $topicScores[$question->topic] = ($topicScores[$question->topic] ?? 0) + 1;
                }
            }
        }

        $percentage = ($score / $mockExam->total_marks) * 100;
        $weakAreas = array_keys(array_filter($topicScores, fn($v) => $v >= 2));

        $attempt->update([
            'completed_at' => now(),
            'score' => $score,
            'percentage' => $percentage,
            'answers' => $validated['answers'],
            'weak_areas' => $weakAreas,
            'status' => 'completed',
        ]);

        return redirect()->route('student.mock-exams.result', $attempt);
    }

    public function result(ExamAttempt $attempt)
    {
        if ($attempt->student_id !== auth()->id()) abort(403);

        $attempt->load('mockExam.subject');
        $exam = $attempt->mockExam;
        $isPassing = $attempt->isPassing();
        $answers = $attempt->answers;

        return Inertia::render('Student/MockExams/Result', compact('attempt', 'exam', 'isPassing', 'answers'));
    }
}
