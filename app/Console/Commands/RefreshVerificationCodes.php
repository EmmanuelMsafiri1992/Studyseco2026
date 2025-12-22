<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use Illuminate\Console\Command;

class RefreshVerificationCodes extends Command
{
    protected $signature = 'verification:refresh {enrollmentId}';
    protected $description = 'Refresh verification codes for an enrollment';

    public function handle()
    {
        $enrollmentId = $this->argument('enrollmentId');
        $enrollment = Enrollment::find($enrollmentId);
        
        if (!$enrollment) {
            $this->error("Enrollment not found: {$enrollmentId}");
            return;
        }

        $emailToken = $enrollment->generateEmailVerificationCode();
        $phoneCode = $enrollment->generatePhoneVerificationCode();
        $enrollment->save();

        $this->info("New verification codes generated for enrollment {$enrollmentId}:");
        $this->line("Email token: {$enrollment->email_verification_token}");
        $this->line("Phone code: {$enrollment->phone_verification_code}");
        $this->line("Expires at: {$enrollment->verification_expires_at}");
    }
}