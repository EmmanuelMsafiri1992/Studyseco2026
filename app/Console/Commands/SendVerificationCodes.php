<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Notifications\VerificationCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendVerificationCodes extends Command
{
    protected $signature = 'verification:send {enrollmentId}';
    protected $description = 'Send verification codes for an enrollment';

    public function handle()
    {
        $enrollmentId = $this->argument('enrollmentId');
        $enrollment = Enrollment::find($enrollmentId);
        
        if (!$enrollment) {
            $this->error("Enrollment not found: {$enrollmentId}");
            return;
        }

        // Send email verification
        try {
            Notification::route('mail', $enrollment->email)
                ->notify(new VerificationCode($enrollment->email_verification_token, 'email'));
            $this->info("Email verification sent to: {$enrollment->email}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        }

        // Send phone verification
        try {
            Notification::route('mail', $enrollment->email)
                ->notify(new VerificationCode($enrollment->phone_verification_code, 'phone'));
            $this->info("Phone verification sent (via email for testing)");
        } catch (\Exception $e) {
            $this->error("Failed to send phone code: " . $e->getMessage());
        }
        
        $this->line("Codes:");
        $this->line("Email: {$enrollment->email_verification_token}");
        $this->line("Phone: {$enrollment->phone_verification_code}");
    }
}