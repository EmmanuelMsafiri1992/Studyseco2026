<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentTestDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🎓 Creating test student with proper enrollment...');
        
        // Create or update test student
        $testStudent = User::updateOrCreate(
            ['email' => 'teststudent@studyseco.com'],
            [
                'name' => 'Test Student',
                'email' => 'teststudent@studyseco.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );

        // Get subjects that have topics and lessons
        $subjectsWithContent = Subject::with('topics.lessons')
            ->where('is_active', true)
            ->get()
            ->filter(function($subject) {
                return $subject->topics->count() > 0 && 
                       $subject->topics->sum(function($topic) {
                           return $topic->lessons->count();
                       }) > 0;
            })
            ->pluck('id')
            ->toArray();

        // Create or update enrollment  
        $enrollment = Enrollment::updateOrCreate(
            [
                'user_id' => $testStudent->id,
                'email' => $testStudent->email
            ],
            [
                'name' => $testStudent->name,
                'phone' => '+265 123 456 789',
                'address' => 'Test Address, Lilongwe',
                'country' => 'Malawi',
                'region' => 'Central',
                'currency' => 'MWK',
                'price_per_subject' => 10000,
                'subject_count' => count($subjectsWithContent),
                'selected_subjects' => $subjectsWithContent,
                'total_amount' => count($subjectsWithContent) * 10000,
                'payment_method' => 'trial',
                'payment_reference' => 'TEST-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'status' => 'approved',
                'is_trial' => false,
                'access_expires_at' => now()->addDays(365),
                'is_verified' => true,
                'email_verified_at' => now(),
                'phone_verified_at' => now()
            ]
        );

        // Update existing student enrollment if exists
        $existingStudent = User::where('email', 'xtreamcoder2013@gmail.com')->first();
        if ($existingStudent) {
            $existingEnrollment = Enrollment::where('user_id', $existingStudent->id)
                ->orWhere('email', $existingStudent->email)
                ->first();
            
            if ($existingEnrollment) {
                $existingEnrollment->update([
                    'selected_subjects' => $subjectsWithContent,
                    'status' => 'approved'
                ]);
                $this->command->info('✅ Updated existing student enrollment');
            }
        }

        $this->command->info('✅ Test student created successfully!');
        $this->command->info('📧 Email: teststudent@studyseco.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('📚 Enrolled in ' . count($subjectsWithContent) . ' subjects with content');
        
        // Display enrolled subjects
        $subjects = Subject::whereIn('id', $subjectsWithContent)->get();
        $this->command->info('📖 Enrolled subjects:');
        foreach ($subjects as $subject) {
            $topicsCount = $subject->topics()->count();
            $lessonsCount = $subject->topics()->withCount('lessons')->get()->sum('lessons_count');
            $this->command->info("   • {$subject->name} ({$topicsCount} topics, {$lessonsCount} lessons)");
        }
    }
}