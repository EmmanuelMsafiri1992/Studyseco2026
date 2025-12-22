<?php

namespace Database\Seeders;

use App\Models\AiTrainingMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiTrainingMaterialSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user and subjects
        $admin = User::where('role', 'admin')->first();
        $subjects = Subject::where('is_active', true)->get();

        if (!$admin || $subjects->isEmpty()) {
            $this->command->warn('⚠️  Admin user or subjects not found. Skipping AI training material seeder.');
            return;
        }

        $materials = [];

        foreach ($subjects->take(6) as $subject) {
            // Add different types of materials for each subject
            $materialTypes = [
                [
                    'type' => 'book',
                    'title' => "{$subject->name} Complete Textbook",
                    'description' => "Comprehensive textbook covering all {$subject->name} topics for secondary education",
                    'size' => rand(5000000, 15000000) // 5-15MB
                ],
                [
                    'type' => 'past_paper',
                    'title' => "{$subject->name} Past Papers Collection",
                    'description' => "Collection of previous examination papers for {$subject->name} with marking schemes",
                    'size' => rand(2000000, 8000000) // 2-8MB
                ],
                [
                    'type' => 'notes',
                    'title' => "{$subject->name} Study Notes",
                    'description' => "Detailed study notes and summaries for {$subject->name}",
                    'size' => rand(1000000, 5000000) // 1-5MB
                ]
            ];

            foreach ($materialTypes as $material) {
                $materials[] = [
                    'title' => $material['title'],
                    'content' => $material['description'],
                    'type' => $material['type'],
                    'subject_id' => $subject->id,
                    'grade_level' => 'Form ' . rand(1, 4),
                    'syllabus' => 'Malawi Secondary Education',
                    'file_path' => "ai-training-materials/{$material['type']}-{$subject->id}-" . rand(1000, 9999) . ".pdf",
                    'file_type' => 'application/pdf',
                    'file_size' => $material['size'],
                    'metadata' => [
                        'original_name' => str_replace(' ', '_', strtolower($material['title'])) . ".pdf",
                        'tags' => $this->generateTags($subject->name, $material['type']),
                        'uploaded_at' => now()->subDays(rand(1, 60))->toISOString(),
                        'processing_notes' => rand(0, 1) === 1 ? 'Processed successfully' : null
                    ],
                    'uploaded_by' => $admin->id,
                    'is_processed' => rand(0, 1) === 1,
                    'is_active' => true,
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now()->subDays(rand(1, 30))
                ];
            }
        }

        foreach ($materials as $material) {
            AiTrainingMaterial::create($material);
        }

        $this->command->info('✅ Created ' . count($materials) . ' sample AI training materials across ' . $subjects->take(6)->count() . ' subjects');
    }

    private function generateTags(string $subjectName, string $materialType): array
    {
        $baseTags = [strtolower($subjectName), $materialType, 'secondary', 'education'];
        
        $subjectSpecificTags = match(strtolower($subjectName)) {
            'mathematics' => ['algebra', 'geometry', 'calculus', 'statistics'],
            'english' => ['grammar', 'literature', 'writing', 'comprehension'],
            'biology' => ['genetics', 'ecology', 'anatomy', 'evolution'],
            'chemistry' => ['organic', 'inorganic', 'physical', 'reactions'],
            'physics' => ['mechanics', 'electricity', 'waves', 'thermodynamics'],
            'geography' => ['physical', 'human', 'climate', 'mapping'],
            'history' => ['world-war', 'independence', 'colonialism', 'politics'],
            default => ['general', 'concepts', 'theory']
        };

        return array_merge($baseTags, array_slice($subjectSpecificTags, 0, 2));
    }

}