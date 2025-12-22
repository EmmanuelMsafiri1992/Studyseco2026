<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class MalawiSubjectsSeederV2 extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Clear existing subjects (delete instead of truncate due to foreign key constraints)
        Subject::query()->delete();

        $subjects = [
            // COMPULSORY SUBJECTS (Forms 1-4)
            ['name' => 'English', 'code' => 'ENG', 'description' => 'English Language and Communication Skills - essential for all forms of communication and academic success', 'department' => 'Languages', 'grade_level' => 'Forms 1-4', 'is_compulsory' => true, 'subject_type' => 'core', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Chichewa', 'code' => 'CHI', 'description' => 'National language of Malawi - preserving cultural heritage and local communication', 'department' => 'Languages', 'grade_level' => 'Forms 1-4', 'is_compulsory' => true, 'subject_type' => 'core', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Mathematics', 'code' => 'MATH', 'description' => 'Pure and Applied Mathematics - building logical thinking and problem-solving skills', 'department' => 'Sciences', 'grade_level' => 'Forms 1-4', 'is_compulsory' => true, 'subject_type' => 'core', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Life Skills', 'code' => 'LS', 'description' => 'Personal development, health education, and practical life skills', 'department' => 'General Studies', 'grade_level' => 'Forms 1-2', 'is_compulsory' => true, 'subject_type' => 'core', 'is_active' => true, 'sort_order' => 4],

            // SCIENCE SUBJECTS
            ['name' => 'Biology', 'code' => 'BIO', 'description' => 'Study of living organisms and life processes - foundation for medical and biological sciences', 'department' => 'Sciences', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'science', 'is_active' => true, 'sort_order' => 5],
            ['name' => 'Physical Science', 'code' => 'PHYS', 'description' => 'Integrated Physics and Chemistry for Forms 1-2 - building scientific foundation', 'department' => 'Sciences', 'grade_level' => 'Forms 1-2', 'is_compulsory' => false, 'subject_type' => 'science', 'is_active' => true, 'sort_order' => 6],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'description' => 'Study of matter, its properties and reactions - essential for pharmaceutical and chemical industries', 'department' => 'Sciences', 'grade_level' => 'Forms 3-4', 'is_compulsory' => false, 'subject_type' => 'science', 'is_active' => true, 'sort_order' => 7],
            ['name' => 'Physics', 'code' => 'PHY', 'description' => 'Study of matter, energy and their interactions - foundation for engineering and technology', 'department' => 'Sciences', 'grade_level' => 'Forms 3-4', 'is_compulsory' => false, 'subject_type' => 'science', 'is_active' => true, 'sort_order' => 8],

            // HUMANITIES SUBJECTS
            ['name' => 'Geography', 'code' => 'GEO', 'description' => 'Physical and human geography of Malawi and the world - understanding our environment', 'department' => 'Humanities', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'humanities', 'is_active' => true, 'sort_order' => 9],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'Malawian, African and World History - understanding our past to shape the future', 'department' => 'Humanities', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'humanities', 'is_active' => true, 'sort_order' => 10],
            ['name' => 'Social Studies', 'code' => 'SS', 'description' => 'Civic education, government and society - preparing responsible citizens', 'department' => 'Humanities', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'humanities', 'is_active' => true, 'sort_order' => 11],
            ['name' => 'Bible Knowledge', 'code' => 'BK', 'description' => 'Christian religious education and moral values', 'department' => 'Humanities', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'humanities', 'is_active' => true, 'sort_order' => 12],

            // TECHNICAL/PRACTICAL SUBJECTS
            ['name' => 'Agriculture', 'code' => 'AGR', 'description' => 'Agricultural practices, crop production and livestock management - vital for Malawi\'s economy', 'department' => 'Technical', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'technical', 'is_active' => true, 'sort_order' => 13],
            ['name' => 'Home Economics', 'code' => 'HE', 'description' => 'Food and nutrition, family management, and household skills', 'department' => 'Technical', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'technical', 'is_active' => true, 'sort_order' => 14],
            ['name' => 'Technical Drawing', 'code' => 'TD', 'description' => 'Engineering drawing and design fundamentals - foundation for engineering careers', 'department' => 'Technical', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'technical', 'is_active' => true, 'sort_order' => 15],

            // COMMERCIAL SUBJECTS
            ['name' => 'Business Studies', 'code' => 'BS', 'description' => 'Entrepreneurship, business management and commercial practices', 'department' => 'Commerce', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'commerce', 'is_active' => true, 'sort_order' => 16],

            // LANGUAGE SUBJECTS
            ['name' => 'French', 'code' => 'FR', 'description' => 'French language and culture - opening opportunities for international communication', 'department' => 'Languages', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'language', 'is_active' => true, 'sort_order' => 17],

            // MODERN ADDITIONS
            ['name' => 'Computer Studies', 'code' => 'CS', 'description' => 'Basic computer literacy, programming fundamentals and digital literacy', 'department' => 'Technology', 'grade_level' => 'Forms 1-4', 'is_compulsory' => false, 'subject_type' => 'technology', 'is_active' => true, 'sort_order' => 18]
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        $this->command->info('✅ Successfully seeded ' . count($subjects) . ' authentic Malawi secondary education subjects!');
        $this->command->info('📚 Subjects include:');
        $this->command->info('   • Compulsory: English, Chichewa, Mathematics, Life Skills');
        $this->command->info('   • Sciences: Biology, Physical Science, Chemistry, Physics');
        $this->command->info('   • Humanities: Geography, History, Social Studies, Bible Knowledge');
        $this->command->info('   • Technical: Agriculture, Home Economics, Technical Drawing');
        $this->command->info('   • Commerce: Business Studies');
        $this->command->info('   • Languages: French');
        $this->command->info('   • Technology: Computer Studies');
    }
}