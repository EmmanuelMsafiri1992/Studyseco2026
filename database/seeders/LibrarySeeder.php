<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all();
        
        if ($subjects->isEmpty()) {
            $this->command->info('No subjects found. Please run SubjectSeeder first.');
            return;
        }

        // Get some subject IDs for variety
        $mathSubject = $subjects->where('name', 'Mathematics')->first();
        $englishSubject = $subjects->where('name', 'English')->first();
        $scienceSubject = $subjects->where('name', 'Biology')->first() ?? $subjects->where('name', 'Physical Science')->first();
        $historySubject = $subjects->where('name', 'History')->first();
        
        // Base template for resources - all will have these common fields added
        $baseResource = [
            'file_path' => 'library-resources/sample.pdf', // placeholder file path
            'file_type' => 'application/pdf',
            'is_active' => true,
            'is_protected' => false,
            'access_permissions' => null,
            'protection_settings' => null,
            'download_attempts' => 0,
        ];

        $resources = [
            // Mathematics Books
            [
                'title' => 'Advanced Mathematics for Form 3',
                'description' => 'Comprehensive mathematics textbook covering algebra, geometry, and trigonometry for Form 3 students.',
                'type' => 'book',
                'category' => 'Textbook',
                'subject_id' => $mathSubject?->id,
                'grade_level' => 11,
                'year' => 2024,
                'view_count' => 45,
                'file_size' => 15728640, // ~15MB
            ],
            [
                'title' => 'Mathematics Workbook Form 2',
                'description' => 'Practice exercises and worksheets for Form 2 mathematics curriculum.',
                'type' => 'book',
                'category' => 'Workbook',
                'subject_id' => $mathSubject?->id,
                'grade_level' => 10,
                'year' => 2024,
                'view_count' => 32,
                'file_size' => 8388608, // ~8MB
            ],
            
            // English Books
            [
                'title' => 'English Literature Anthology',
                'description' => 'Collection of poems, short stories, and excerpts from novels for secondary school students.',
                'type' => 'book',
                'category' => 'Literature',
                'subject_id' => $englishSubject?->id,
                'grade_level' => 11,
                'year' => 2024,
                'view_count' => 28,
                'file_size' => 12582912, // ~12MB
            ],
            [
                'title' => 'Grammar and Composition Guide',
                'description' => 'Comprehensive guide to English grammar rules and essay writing techniques.',
                'type' => 'book',
                'category' => 'Grammar',
                'subject_id' => $englishSubject?->id,
                'grade_level' => 10,
                'year' => 2024,
                'view_count' => 67,
                'file_size' => 5242880, // ~5MB
            ],

            // Science Books
            [
                'title' => 'Biology Practical Manual',
                'description' => 'Laboratory experiments and practical work guide for biology students.',
                'type' => 'book',
                'category' => 'Practical Guide',
                'subject_id' => $scienceSubject?->id,
                'grade_level' => 12,
                'year' => 2024,
                'view_count' => 23,
                'file_size' => 18874368, // ~18MB
            ],

            // Past Papers
            [
                'title' => 'MSCE Mathematics 2023',
                'description' => 'Complete MSCE Mathematics examination paper from 2023 with marking scheme.',
                'type' => 'past_paper',
                'category' => 'MSCE',
                'subject_id' => $mathSubject?->id,
                'grade_level' => 12,
                'exam_board' => 'MANEB',
                'year' => 2023,
                'view_count' => 156,
                'file_size' => 2097152, // ~2MB
            ],
            [
                'title' => 'MSCE English 2023',
                'description' => 'MSCE English Language examination paper 2023 with comprehensive answers.',
                'type' => 'past_paper',
                'category' => 'MSCE',
                'subject_id' => $englishSubject?->id,
                'grade_level' => 12,
                'exam_board' => 'MANEB',
                'year' => 2023,
                'view_count' => 134,
                'file_size' => 1572864, // ~1.5MB
            ],
            [
                'title' => 'JCE Mathematics 2023',
                'description' => 'Junior Certificate of Education Mathematics paper from 2023.',
                'type' => 'past_paper',
                'category' => 'JCE',
                'subject_id' => $mathSubject?->id,
                'grade_level' => 10,
                'exam_board' => 'MANEB',
                'year' => 2023,
                'view_count' => 89,
                'file_size' => 1048576, // ~1MB
            ],

            // Documents
            [
                'title' => 'Study Tips for Effective Learning',
                'description' => 'Comprehensive guide with proven strategies for effective studying and exam preparation.',
                'type' => 'document',
                'category' => 'Study Guide',
                'subject_id' => null,
                'grade_level' => null,
                'year' => 2024,
                'view_count' => 78,
                'file_size' => 3145728, // ~3MB
            ],
            [
                'title' => 'MANEB Examination Guidelines 2024',
                'description' => 'Official guidelines and regulations for MANEB examinations in 2024.',
                'type' => 'document',
                'category' => 'Official Guidelines',
                'subject_id' => null,
                'grade_level' => null,
                'year' => 2024,
                'view_count' => 67,
                'file_size' => 1572864, // ~1.5MB
            ],

            // More Past Papers
            [
                'title' => 'MSCE Biology 2023',
                'description' => 'MSCE Biology examination paper 2023 with marking scheme and diagrams.',
                'type' => 'past_paper',
                'category' => 'MSCE',
                'subject_id' => $scienceSubject?->id,
                'grade_level' => 12,
                'exam_board' => 'MANEB',
                'year' => 2023,
                'view_count' => 112,
                'file_size' => 3145728, // ~3MB
            ],
            [
                'title' => 'JCE English 2023',
                'description' => 'Junior Certificate English Language paper with comprehension and essay sections.',
                'type' => 'past_paper',
                'category' => 'JCE',
                'subject_id' => $englishSubject?->id,
                'grade_level' => 10,
                'exam_board' => 'MANEB',
                'year' => 2023,
                'view_count' => 76,
                'file_size' => 1835008, // ~1.75MB
            ],

            // Reference Materials
            [
                'title' => 'Mathematical Formulas Reference',
                'description' => 'Quick reference guide containing all essential mathematical formulas for secondary education.',
                'type' => 'document',
                'category' => 'Reference',
                'subject_id' => $mathSubject?->id,
                'grade_level' => null,
                'year' => 2024,
                'view_count' => 145,
                'file_size' => 2097152, // ~2MB
            ],
        ];

        foreach ($resources as $resourceData) {
            Resource::create(array_merge($baseResource, $resourceData));
        }

        $this->command->info('Library seeder completed successfully! Added ' . count($resources) . ' resources.');
    }
}