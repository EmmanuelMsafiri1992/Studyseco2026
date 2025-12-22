<?php

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptPolicy
{
    /**
     * Determine if the user can view the quiz attempt.
     */
    public function view(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->student_id;
    }

    /**
     * Determine if the user can update the quiz attempt.
     */
    public function update(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->student_id && $attempt->status === 'in_progress';
    }
}
