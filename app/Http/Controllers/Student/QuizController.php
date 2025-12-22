<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get enrolled subjects from the enrollment's selected_subjects JSON field
        $enrollment = $user->enrollments()
            ->where('status', 'approved')
            ->first();

        $enrolledSubjectIds = $enrollment && $enrollment->selected_subjects
            ? $enrollment->selected_subjects
            : [];

        $quizzes = Quiz::with(['subject', 'teacher'])
            ->available()
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->get()
            ->map(function ($quiz) use ($user) {
                $attempts = $quiz->getUserAttempts($user);
                $bestScore = $quiz->getBestScore($user);

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'subject' => $quiz->subject,
                    'teacher' => $quiz->teacher,
                    'duration_minutes' => $quiz->duration_minutes,
                    'total_points' => $quiz->total_points,
                    'passing_score' => $quiz->passing_score,
                    'max_attempts' => $quiz->max_attempts,
                    'attempts_used' => $attempts->where('status', 'completed')->count(),
                    'can_attempt' => $quiz->canAttempt($user),
                    'best_score' => $bestScore,
                    'last_attempt' => $attempts->first(),
                ];
            });

        return Inertia::render('Student/Quizzes/Index', [
            'quizzes' => $quizzes
        ]);
    }

    public function show(Quiz $quiz)
    {
        $user = auth()->user();

        if (!$quiz->isAvailable()) {
            return redirect()->route('student.quizzes.index')
                ->with('error', 'This quiz is not available.');
        }

        $quiz->load(['subject', 'teacher', 'questions']);

        $attempts = $quiz->getUserAttempts($user);
        $canAttempt = $quiz->canAttempt($user);

        // Check for in-progress attempt
        $inProgressAttempt = $attempts->where('status', 'in_progress')->first();

        return Inertia::render('Student/Quizzes/Show', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'canAttempt' => $canAttempt,
            'inProgressAttempt' => $inProgressAttempt,
            'bestScore' => $quiz->getBestScore($user)
        ]);
    }

    public function start(Quiz $quiz)
    {
        $user = auth()->user();

        if (!$quiz->isAvailable()) {
            return back()->with('error', 'This quiz is not available.');
        }

        if (!$quiz->canAttempt($user)) {
            return back()->with('error', 'You have reached the maximum number of attempts.');
        }

        // Check for existing in-progress attempt
        $existingAttempt = $quiz->attempts()
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('student.quizzes.take', $existingAttempt->id);
        }

        // Create new attempt
        $attemptNumber = $quiz->attempts()
            ->where('student_id', $user->id)
            ->max('attempt_number') + 1;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
            'total_points' => $quiz->total_points,
            'status' => 'in_progress'
        ]);

        return redirect()->route('student.quizzes.take', $attempt->id);
    }

    public function take(QuizAttempt $attempt)
    {
        $this->authorize('view', $attempt);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quizzes.result', $attempt->id);
        }

        $attempt->load(['quiz.questions', 'quiz.subject']);

        $questions = $attempt->quiz->questions;

        if ($attempt->quiz->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        // Hide correct answers during quiz
        $questions = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'options' => $question->getOptionsArray(),
                'points' => $question->points,
            ];
        });

        return Inertia::render('Student/Quizzes/Take', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $questions,
            'timeRemaining' => $this->getTimeRemaining($attempt)
        ]);
    }

    public function submit(Request $request, QuizAttempt $attempt)
    {
        $this->authorize('update', $attempt);

        if ($attempt->status !== 'in_progress') {
            return back()->with('error', 'This attempt has already been completed.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.answer' => 'required|string',
        ]);

        $quiz = $attempt->quiz()->with('questions')->first();
        $score = 0;

        foreach ($validated['answers'] as $answerData) {
            $question = $quiz->questions->firstWhere('id', $answerData['question_id']);

            if (!$question) continue;

            $isCorrect = $question->checkAnswer($answerData['answer']);
            $pointsEarned = $isCorrect ? $question->points : 0;
            $score += $pointsEarned;

            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $question->id,
                'student_answer' => $answerData['answer'],
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);
        }

        $attempt->complete($score, $quiz->total_points);
        $attempt->update(['answers' => $validated['answers']]);

        return redirect()->route('student.quizzes.result', $attempt->id)
            ->with('success', 'Quiz submitted successfully!');
    }

    public function result(QuizAttempt $attempt)
    {
        $this->authorize('view', $attempt);

        $attempt->load([
            'quiz.questions',
            'quiz.subject',
            'quiz.teacher',
            'quizAnswers.question'
        ]);

        $showCorrectAnswers = $attempt->quiz->show_correct_answers;

        return Inertia::render('Student/Quizzes/Result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'answers' => $attempt->quizAnswers,
            'showCorrectAnswers' => $showCorrectAnswers,
            'isPassing' => $attempt->isPassing(),
            'grade' => $attempt->getGrade(),
            'timeSpent' => $attempt->getTimeSpent()
        ]);
    }

    private function getTimeRemaining(QuizAttempt $attempt): int
    {
        $durationMinutes = $attempt->quiz->duration_minutes;
        $elapsedMinutes = $attempt->started_at->diffInMinutes(now());
        $remainingMinutes = max(0, $durationMinutes - $elapsedMinutes);

        return $remainingMinutes * 60; // Return in seconds
    }
}
