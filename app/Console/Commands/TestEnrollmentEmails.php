<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\TrialWelcomeEmail;
use App\Notifications\WelcomeEmail;
use App\Notifications\EnrollmentPending;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestEnrollmentEmails extends Command
{
    protected $signature = 'test:enrollment-emails';
    protected $description = 'Test enrollment email notifications';

    public function handle()
    {
        $this->info('Testing enrollment email notifications...');
        
        // Create a test enrollment
        $testEnrollment = Enrollment::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'address' => 'Test Address',
            'country' => 'Malawi',
            'region' => 'malawi',
            'selected_subjects' => [1, 2, 3],
            'total_amount' => 0,
            'currency' => 'MWK',
            'is_trial' => true,
            'trial_started_at' => now(),
            'trial_expires_at' => now()->addDays(7),
            'access_expires_at' => now()->addDays(7),
            'status' => 'approved',
            'payment_method' => 'trial',
            'payment_reference' => 'TRIAL_TEST_123'
        ]);
        
        // Create a test user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => bcrypt('TestPassword123'),
            'role' => 'student',
            'email_verified_at' => now()
        ]);
        
        $testEnrollment->update(['user_id' => $testUser->id]);
        
        $this->info('Created test enrollment and user');
        
        // Test Trial Welcome Email
        try {
            $testUser->notify(new TrialWelcomeEmail($testUser, $testEnrollment, 'TestPassword123'));
            $this->info('✅ Trial Welcome Email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Trial Welcome Email failed: ' . $e->getMessage());
        }
        
        // Test Enrollment Pending Email
        try {
            Notification::route('mail', $testEnrollment->email)
                ->notify(new EnrollmentPending($testEnrollment));
            $this->info('✅ Enrollment Pending Email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Enrollment Pending Email failed: ' . $e->getMessage());
        }
        
        // Test Welcome Email (for paid users)
        try {
            $testUser->notify(new WelcomeEmail($testUser, $testEnrollment, 'TestPassword123'));
            $this->info('✅ Welcome Email sent successfully');
        } catch (\Exception $e) {
            $this->error('❌ Welcome Email failed: ' . $e->getMessage());
        }
        
        // Clean up test data
        $testUser->delete();
        $testEnrollment->delete();
        
        $this->info('✅ Test completed and cleanup done');
        $this->info('Check your logs (storage/logs/laravel.log) to see the email content');
    }
}