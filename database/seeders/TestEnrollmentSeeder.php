<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Subject;

class TestEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('email', 'student@example.com')->first();

        if (!$student) {
            $this->command->error('Student user not found. Please run UserSeeder first.');
            return;
        }

        // Check if enrollment already exists
        $existingEnrollment = Enrollment::where('user_id', $student->id)
            ->orWhere('email', $student->email)
            ->first();

        if ($existingEnrollment) {
            $this->command->info('Enrollment for student@example.com already exists.');
            return;
        }

        // Get some test subjects
        $subjects = Subject::where('is_active', true)->limit(3)->pluck('id')->toArray();

        if (empty($subjects)) {
            $this->command->warn('No active subjects found. Creating enrollment without subjects.');
            $subjects = [];
        }

        // Create a trial enrollment for testing
        Enrollment::create([
            'user_id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'phone' => '1234567890',
            'address' => 'Test Address',
            'country' => 'Malawi',
            'region' => 'Southern Africa',
            'selected_subjects' => $subjects,
            'subject_count' => count($subjects),
            'price_per_subject' => 50000,
            'total_amount' => count($subjects) * 50000,
            'currency' => 'MWK',
            'is_trial' => true,
            'trial_started_at' => now(),
            'trial_expires_at' => now()->addDays(7),
            'access_expires_at' => now()->addDays(7),
            'status' => 'approved',
            'payment_method' => 'trial',
            'payment_reference' => 'TRIAL_TEST',
        ]);

        $this->command->info('Trial enrollment created for student@example.com');
    }
}
