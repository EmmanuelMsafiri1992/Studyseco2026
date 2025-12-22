<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MalawiSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing subjects
        \App\Models\Subject::query()->delete();
        
        $subjects = [
            // Core Subjects (Mandatory for all students)
            [
                'name' => 'English',
                'code' => 'ENG',
                'description' => 'English Language, Literature, and Communication Skills',
                'department' => 'Languages',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Chichewa',
                'code' => 'CHI',
                'description' => 'National language of Malawi - grammar, literature and communication',
                'department' => 'Languages',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'description' => 'Pure Mathematics - Algebra, Geometry, Trigonometry, Statistics',
                'department' => 'Sciences',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Life Skills',
                'code' => 'LIFE',
                'description' => 'Personal development, health education, and social skills',
                'department' => 'General Studies',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],

            // Sciences (Choose 2-3)
            [
                'name' => 'Biology',
                'code' => 'BIO',
                'description' => 'Study of living organisms, human biology, ecology, and genetics',
                'department' => 'Sciences',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Physical Science',
                'code' => 'PHYS',
                'description' => 'Combined Physics and Chemistry - matter, energy, forces, and reactions',
                'department' => 'Sciences',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Chemistry',
                'code' => 'CHEM',
                'description' => 'Pure Chemistry - atomic structure, chemical bonds, reactions, and organic chemistry',
                'department' => 'Sciences',
                'grade_level' => 'Form 3-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Physics',
                'code' => 'PHY',
                'description' => 'Pure Physics - mechanics, waves, electricity, magnetism, and modern physics',
                'department' => 'Sciences',
                'grade_level' => 'Form 3-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],

            // Humanities & Social Sciences (Choose 2-3)
            [
                'name' => 'Geography',
                'code' => 'GEO',
                'description' => 'Physical and human geography of Malawi, Africa, and the world',
                'department' => 'Humanities',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'History',
                'code' => 'HIST',
                'description' => 'Malawi history, African history, and world history',
                'department' => 'Humanities',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Social Studies',
                'code' => 'SOCIAL',
                'description' => 'Civics, government, economics, and society',
                'department' => 'Humanities',
                'grade_level' => 'Form 1-2',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Bible Knowledge',
                'code' => 'BK',
                'description' => 'Religious education and moral studies',
                'department' => 'Humanities',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],

            // Technical & Practical Subjects (Choose 1-2)
            [
                'name' => 'Agriculture',
                'code' => 'AGRI',
                'description' => 'Crop production, animal husbandry, and agricultural economics',
                'department' => 'Technical',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Home Economics',
                'code' => 'HOME',
                'description' => 'Food and nutrition, textiles, child development, and home management',
                'department' => 'Technical',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],
            [
                'name' => 'Technical Drawing',
                'code' => 'TD',
                'description' => 'Engineering drawing, geometric constructions, and design principles',
                'department' => 'Technical',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],

            // Business & Commerce
            [
                'name' => 'Business Studies',
                'code' => 'BIZ',
                'description' => 'Business principles, entrepreneurship, and basic accounting',
                'department' => 'Commerce',
                'grade_level' => 'Form 3-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],

            // Languages (Additional)
            [
                'name' => 'French',
                'code' => 'FRE',
                'description' => 'French language - speaking, reading, writing, and literature',
                'department' => 'Languages',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ],

            // Computer Studies
            [
                'name' => 'Computer Studies',
                'code' => 'COMP',
                'description' => 'Computer literacy, programming basics, and information technology',
                'department' => 'Technology',
                'grade_level' => 'Form 1-4',
                'credits' => 1,
                'teacher_name' => 'To be assigned',
                'cover_image' => null,
                'is_active' => true
            ]
        ];

        foreach ($subjects as $subject) {
            \App\Models\Subject::create($subject);
        }
    }
}
