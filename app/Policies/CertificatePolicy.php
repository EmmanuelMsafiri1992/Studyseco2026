<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    /**
     * Determine if the user can view the certificate.
     */
    public function view(User $user, Certificate $certificate): bool
    {
        // Teachers can view certificates they issued, students can view their own
        return $user->id === $certificate->teacher_id || $user->id === $certificate->student_id;
    }

    /**
     * Determine if the user can delete the certificate.
     */
    public function delete(User $user, Certificate $certificate): bool
    {
        // Only the teacher who issued it can delete
        return $user->id === $certificate->teacher_id;
    }
}
