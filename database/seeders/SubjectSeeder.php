<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Lesson;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Mathematics subject for Form 1
        $mathSubject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MATH101',
            'description' => 'Introduction to basic mathematical concepts including algebra, geometry, and arithmetic for Form 1 students in Malawi.',
            'department' => 'Mathematics',
            'grade_level' => 'Form 1',
            'credits' => 4,
            'teacher_name' => 'Mr. John Banda',
            'is_active' => true,
        ]);

        // Create topics for Mathematics
        $algebraTopic = Topic::create([
            'subject_id' => $mathSubject->id,
            'name' => 'Introduction to Algebra',
            'description' => 'Learn the basics of algebra including variables, expressions, and simple equations.',
            'order_index' => 0,
            'is_active' => true,
        ]);

        $geometryTopic = Topic::create([
            'subject_id' => $mathSubject->id,
            'name' => 'Basic Geometry',
            'description' => 'Understanding shapes, angles, and basic geometric concepts.',
            'order_index' => 1,
            'is_active' => true,
        ]);

        // Create sample lessons for Algebra topic
        Lesson::create([
            'topic_id' => $algebraTopic->id,
            'title' => 'What are Variables?',
            'description' => 'Learn about variables and how they are used in mathematical expressions.',
            'notes' => 'Remember: A variable is a letter that represents an unknown number. The most common variables are x, y, and z.',
            'order_index' => 0,
            'is_published' => true,
        ]);

        Lesson::create([
            'topic_id' => $algebraTopic->id,
            'title' => 'Simple Algebraic Expressions',
            'description' => 'Understanding how to read and write algebraic expressions.',
            'notes' => 'Practice exercises:\n1. Write x + 5 in words\n2. Express "three times a number" as algebra\n3. Simplify 2x + 3x',
            'order_index' => 1,
            'is_published' => true,
        ]);

        // Create Physical Science subject for Form 2
        $physicsSubject = Subject::create([
            'name' => 'Physical Science',
            'code' => 'PHYS201',
            'description' => 'Introduction to physics and chemistry concepts for Form 2 students.',
            'department' => 'Physical Science',
            'grade_level' => 'Form 2',
            'credits' => 3,
            'teacher_name' => 'Mrs. Grace Mwale',
            'is_active' => true,
        ]);

        // Create topic for Physical Science
        $motionTopic = Topic::create([
            'subject_id' => $physicsSubject->id,
            'name' => 'Motion and Forces',
            'description' => 'Understanding how objects move and the forces that affect motion.',
            'order_index' => 0,
            'is_active' => true,
        ]);

        // Create lesson for Motion topic
        Lesson::create([
            'topic_id' => $motionTopic->id,
            'title' => 'Introduction to Motion',
            'description' => 'Learn about different types of motion and how to measure speed.',
            'notes' => 'Key concepts:\n- Distance vs Displacement\n- Speed = Distance ÷ Time\n- Velocity includes direction',
            'order_index' => 0,
            'is_published' => true,
        ]);

        $this->command->info('Sample subjects, topics, and lessons created successfully!');
    }
}
