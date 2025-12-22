<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\WelcomeEmail;
use Illuminate\Console\Command;

class ApproveEnrollment extends Command
{
    protected $signature = 'enrollment:approve {enrollmentId}';
    protected $description = 'Approve an enrollment and create user account';

    public function handle()
    {
        $enrollmentId = $this->argument('enrollmentId');
        $enrollment = Enrollment::find($enrollmentId);
        
        if (!$enrollment) {
            $this->error("Enrollment not found: {$enrollmentId}");
            return;
        }

        if ($enrollment->status !== 'pending') {
            $this->error("Enrollment {$enrollmentId} is not pending (current status: {$enrollment->status})");
            return;
        }

        // Simulate admin approval
        $enrollment->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => 1, // Assume admin user ID 1
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'verification_expires_at' => null
        ]);

        // Update payment status
        $enrollment->payments()->update(['status' => 'verified']);

        // Create user account
        $user = User::where('email', $enrollment->email)->first();
        $tempPassword = null;
        
        if (!$user) {
            $tempPassword = 'StudySeco' . rand(1000, 9999);
            $user = User::create([
                'name' => $enrollment->name,
                'email' => $enrollment->email,
                'password' => bcrypt($tempPassword),
                'role' => 'student',
                'email_verified_at' => now()
            ]);
        } else {
            // For existing users, generate new temporary password
            $tempPassword = 'StudySeco' . rand(1000, 9999);
            $user->update([
                'password' => bcrypt($tempPassword),
                'email_verified_at' => now()
            ]);
        }

        // Link user to enrollment
        $enrollment->update(['user_id' => $user->id]);

        // Send welcome email
        try {
            $user->notify(new WelcomeEmail($user, $enrollment, $tempPassword));
            $this->info("Welcome email sent successfully");
        } catch (\Exception $e) {
            $this->error("Failed to send welcome email: " . $e->getMessage());
        }

        $this->info("Enrollment {$enrollmentId} approved successfully!");
        $this->line("User created: {$user->email}");
        $this->line("Temporary password: {$tempPassword}");
        $this->line("Login at: http://127.0.0.1:8000/login");
    }
}