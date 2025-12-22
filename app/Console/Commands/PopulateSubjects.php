<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subject;

class PopulateSubjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subjects:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate subjects table with default subjects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Current subject count: ' . Subject::count());
        
        // Clear existing subjects
        Subject::truncate();
        
        $subjects = [
            ['name' => 'English', 'code' => 'ENG', 'department' => 'Languages', 'grade_level' => 'Form 1-4', 'is_active' => true],
            ['name' => 'Chichewa', 'code' => 'CHI', 'department' => 'Languages', 'grade_level' => 'Form 1-4', 'is_active' => true],
            ['name' => 'Mathematics', 'code' => 'MATH', 'department' => 'Sciences', 'grade_level' => 'Form 1-4', 'is_active' => true],
            ['name' => 'Life Skills', 'code' => 'LIFE', 'department' => 'General', 'grade_level' => 'Form 1-4', 'is_active' => true],
            ['name' => 'Biology', 'code' => 'BIO', 'department' => 'Sciences', 'grade_level' => 'Form 3-4', 'is_active' => true],
            ['name' => 'Physical Science', 'code' => 'PHYS', 'department' => 'Sciences', 'grade_level' => 'Form 1-2', 'is_active' => true],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'department' => 'Sciences', 'grade_level' => 'Form 3-4', 'is_active' => true],
            ['name' => 'Physics', 'code' => 'PHY', 'department' => 'Sciences', 'grade_level' => 'Form 3-4', 'is_active' => true],
            ['name' => 'Geography', 'code' => 'GEO', 'department' => 'Humanities', 'grade_level' => 'Form 1-4', 'is_active' => true],
            ['name' => 'History', 'code' => 'HIST', 'department' => 'Humanities', 'grade_level' => 'Form 1-4', 'is_active' => true],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
            $this->info('Created subject: ' . $subject['name']);
        }
        
        $this->info('Total subjects created: ' . Subject::count());
        $this->info('Subject IDs: ' . implode(', ', Subject::pluck('id')->toArray()));
        
        return 0;
    }
}
