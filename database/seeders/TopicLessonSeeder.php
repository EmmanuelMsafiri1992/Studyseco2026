<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Topic;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class TopicLessonSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting to seed topics and lessons with working video content...');
        
        // Mathematics Topics and Lessons
        $this->seedMathematicsContent();
        
        // English Topics and Lessons
        $this->seedEnglishContent();
        
        // Biology Topics and Lessons
        $this->seedBiologyContent();
        
        // Chemistry Topics and Lessons
        $this->seedChemistryContent();
        
        // Physics Topics and Lessons
        $this->seedPhysicsContent();
        
        // Geography Topics and Lessons
        $this->seedGeographyContent();
        
        // Computer Studies Topics and Lessons
        $this->seedComputerStudiesContent();
        
        $this->command->info('✅ Successfully seeded comprehensive academic content!');
        $this->command->info('📊 Summary:');
        $this->command->info('   • ' . Topic::count() . ' topics created');
        $this->command->info('   • ' . Lesson::count() . ' lessons created');
        $this->command->info('   • All lessons include working video URLs for testing');
    }
    
    private function seedMathematicsContent()
    {
        $math = Subject::where('code', 'MATH')->first();
        if (!$math) return;
        
        $this->command->info('📐 Seeding Mathematics content...');
        
        // Topic 1: Algebra Basics
        $algebraTopic = Topic::create([
            'subject_id' => $math->id,
            'name' => 'Introduction to Algebra',
            'description' => 'Fundamental concepts of algebra including variables, expressions, and equations',
            'order_index' => 1
        ]);
        
        $algebraLessons = [
            [
                'title' => 'What is Algebra?',
                'description' => 'Understanding variables, constants, and algebraic expressions',
                'video_path' => 'https://www.youtube.com/watch?v=NybHckSEQBI', // Khan Academy: What is Algebra?
                'duration_minutes' => 8,
                'notes' => 'Introduction to algebraic thinking and basic terminology',
                'order_index' => 1
            ],
            [
                'title' => 'Solving Simple Equations',
                'description' => 'Learn to solve basic linear equations step by step',
                'video_path' => 'https://www.youtube.com/watch?v=9RT-FzWCKmI', // Khan Academy: Solving Basic Equations
                'duration_minutes' => 12,
                'notes' => 'Master the fundamentals of equation solving with practical examples',
                'order_index' => 2
            ],
            [
                'title' => 'Working with Fractions in Algebra',
                'description' => 'Solving equations involving fractions and rational expressions',
                'video_path' => 'https://www.youtube.com/watch?v=WZvuOj2xLQA', // Khan Academy: Fractions in Algebra
                'duration_minutes' => 15,
                'notes' => 'Essential skills for handling fractions in algebraic contexts',
                'order_index' => 3
            ]
        ];
        
        foreach ($algebraLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $algebraTopic->id,
                'is_published' => true
            ]));
        }
        
        // Topic 2: Geometry Fundamentals
        $geometryTopic = Topic::create([
            'subject_id' => $math->id,
            'name' => 'Geometry Fundamentals',
            'description' => 'Basic geometric shapes, properties, and calculations',
            'order_index' => 2
        ]);
        
        $geometryLessons = [
            [
                'title' => 'Area and Perimeter',
                'description' => 'Calculating area and perimeter of basic shapes',
                'video_path' => 'https://www.youtube.com/watch?v=frodlCDhKRQ', // Khan Academy: Area and Perimeter
                'duration_minutes' => 10,
                'notes' => 'Fundamental formulas for rectangles, triangles, and circles',
                'order_index' => 1
            ],
            [
                'title' => 'Pythagorean Theorem',
                'description' => 'Understanding and applying the Pythagorean theorem',
                'video_path' => 'https://www.youtube.com/watch?v=AA6RfgP-AHU', // Khan Academy: Pythagorean Theorem
                'duration_minutes' => 14,
                'notes' => 'One of the most important theorems in mathematics',
                'order_index' => 2
            ]
        ];
        
        foreach ($geometryLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $geometryTopic->id,
                'is_published' => true
            ]));
        }
    }
    
    private function seedEnglishContent()
    {
        $english = Subject::where('code', 'ENG')->first();
        if (!$english) return;
        
        $this->command->info('📚 Seeding English content...');
        
        // Topic 1: Grammar Essentials
        $grammarTopic = Topic::create([
            'subject_id' => $english->id,
            'name' => 'Grammar Essentials',
            'description' => 'Master the fundamental rules of English grammar',
            'order_index' => 1
        ]);
        
        $grammarLessons = [
            [
                'title' => 'Parts of Speech',
                'description' => 'Understanding nouns, verbs, adjectives, and other parts of speech',
                'video_path' => 'https://www.youtube.com/watch?v=5Y8tIVAl2yc', // English Grammar: Parts of Speech
                'duration_minutes' => 18,
                'notes' => 'Foundation of English grammar - essential for all students',
                'order_index' => 1
            ],
            [
                'title' => 'Sentence Structure',
                'description' => 'Building proper sentences with subjects, predicates, and clauses',
                'video_path' => 'https://www.youtube.com/watch?v=dDOJG7yMrdc', // English Grammar: Sentence Structure
                'duration_minutes' => 12,
                'notes' => 'Learn to construct clear and effective sentences',
                'order_index' => 2
            ],
            [
                'title' => 'Punctuation Rules',
                'description' => 'Proper use of commas, periods, semicolons, and other punctuation',
                'video_path' => 'https://www.youtube.com/watch?v=pAP1Gn5ckQU', // English: Punctuation Rules
                'duration_minutes' => 16,
                'notes' => 'Essential punctuation skills for clear communication',
                'order_index' => 3
            ]
        ];
        
        foreach ($grammarLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $grammarTopic->id,
                'is_published' => true
            ]));
        }
        
        // Topic 2: Essay Writing
        $writingTopic = Topic::create([
            'subject_id' => $english->id,
            'name' => 'Essay Writing Skills',
            'description' => 'Develop strong academic writing skills',
            'order_index' => 2
        ]);
        
        $writingLessons = [
            [
                'title' => 'Introduction to Essay Writing',
                'description' => 'Structure, thesis statements, and essay planning',
                'video_path' => 'https://www.youtube.com/watch?v=_pOru0kZZgM', // Essay Writing: Introduction
                'duration_minutes' => 20,
                'notes' => 'Learn the five-paragraph essay structure and planning techniques',
                'order_index' => 1
            ],
            [
                'title' => 'Writing Strong Paragraphs',
                'description' => 'Topic sentences, supporting details, and paragraph unity',
                'video_path' => 'https://www.youtube.com/watch?v=KHT4NK-KT-k', // Paragraph Writing Skills
                'duration_minutes' => 15,
                'notes' => 'Master the art of coherent paragraph construction',
                'order_index' => 2
            ]
        ];
        
        foreach ($writingLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $writingTopic->id,
                'is_published' => true
            ]));
        }
    }
    
    private function seedBiologyContent()
    {
        $biology = Subject::where('code', 'BIO')->first();
        if (!$biology) return;
        
        $this->command->info('🧬 Seeding Biology content...');
        
        // Topic 1: Cell Biology
        $cellTopic = Topic::create([
            'subject_id' => $biology->id,
            'name' => 'Introduction to Cells',
            'description' => 'Understanding the basic unit of life',
            'order_index' => 1
        ]);
        
        $cellLessons = [
            [
                'title' => 'What is a Cell?',
                'description' => 'Basic structure and functions of cells',
                'video_path' => 'https://www.youtube.com/watch?v=8IlzKri08kk', // Crash Course Biology: What is a Cell
                'duration_minutes' => 11,
                'notes' => 'Introduction to cellular biology - the foundation of life sciences',
                'order_index' => 1
            ],
            [
                'title' => 'Plant vs Animal Cells',
                'description' => 'Comparing and contrasting plant and animal cell structures',
                'video_path' => 'https://www.youtube.com/watch?v=Hmwvj9X4GNY', // Plant vs Animal Cells
                'duration_minutes' => 9,
                'notes' => 'Understanding the key differences between plant and animal cells',
                'order_index' => 2
            ],
            [
                'title' => 'Cell Organelles',
                'description' => 'Functions of nucleus, mitochondria, and other organelles',
                'video_path' => 'https://www.youtube.com/watch?v=h0KLF2xPTtU', // Cell Organelles
                'duration_minutes' => 13,
                'notes' => 'Detailed study of cellular components and their functions',
                'order_index' => 3
            ]
        ];
        
        foreach ($cellLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $cellTopic->id,
                'is_published' => true
            ]));
        }
        
        // Topic 2: Human Body Systems
        $bodyTopic = Topic::create([
            'subject_id' => $biology->id,
            'name' => 'Human Body Systems',
            'description' => 'Understanding how body systems work together',
            'order_index' => 2
        ]);
        
        $bodyLessons = [
            [
                'title' => 'Circulatory System',
                'description' => 'Heart, blood vessels, and blood circulation',
                'video_path' => 'https://www.youtube.com/watch?v=CWFyxn0qDEU', // Circulatory System
                'duration_minutes' => 12,
                'notes' => 'How blood circulates throughout the human body',
                'order_index' => 1
            ],
            [
                'title' => 'Respiratory System',
                'description' => 'Breathing, lungs, and gas exchange',
                'video_path' => 'https://www.youtube.com/watch?v=SPGRkexI_cs', // Respiratory System
                'duration_minutes' => 10,
                'notes' => 'Understanding how we breathe and exchange gases',
                'order_index' => 2
            ]
        ];
        
        foreach ($bodyLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $bodyTopic->id,
                'is_published' => true
            ]));
        }
    }
    
    private function seedChemistryContent()
    {
        $chemistry = Subject::where('code', 'CHEM')->first();
        if (!$chemistry) return;
        
        $this->command->info('🧪 Seeding Chemistry content...');
        
        // Topic 1: Atoms and Elements
        $atomTopic = Topic::create([
            'subject_id' => $chemistry->id,
            'name' => 'Atoms and Elements',
            'description' => 'Basic building blocks of matter',
            'order_index' => 1
        ]);
        
        $atomLessons = [
            [
                'title' => 'Introduction to Atoms',
                'description' => 'Protons, neutrons, electrons, and atomic structure',
                'video_path' => 'https://www.youtube.com/watch?v=FSyAehMdpyI', // Crash Course Chemistry: Atoms
                'duration_minutes' => 14,
                'notes' => 'Understanding the fundamental particles that make up all matter',
                'order_index' => 1
            ],
            [
                'title' => 'The Periodic Table',
                'description' => 'Organization of elements and periodic trends',
                'video_path' => 'https://www.youtube.com/watch?v=rz4Dd1I_fX0', // Periodic Table Explained
                'duration_minutes' => 16,
                'notes' => 'How elements are organized and what the periodic table tells us',
                'order_index' => 2
            ],
            [
                'title' => 'Chemical Bonding',
                'description' => 'Ionic and covalent bonds between atoms',
                'video_path' => 'https://www.youtube.com/watch?v=QqjcCvzWwww', // Chemical Bonding
                'duration_minutes' => 18,
                'notes' => 'How atoms combine to form compounds through chemical bonds',
                'order_index' => 3
            ]
        ];
        
        foreach ($atomLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $atomTopic->id,
                'is_published' => true
            ]));
        }
    }
    
    private function seedPhysicsContent()
    {
        $physics = Subject::where('code', 'PHY')->first();
        if (!$physics) return;
        
        $this->command->info('⚡ Seeding Physics content...');
        
        // Topic 1: Motion and Forces
        $motionTopic = Topic::create([
            'subject_id' => $physics->id,
            'name' => 'Motion and Forces',
            'description' => 'Understanding how objects move and the forces that affect them',
            'order_index' => 1
        ]);
        
        $motionLessons = [
            [
                'title' => 'Introduction to Motion',
                'description' => 'Distance, displacement, speed, and velocity',
                'video_path' => 'https://www.youtube.com/watch?v=ZM8ECpBuQYE', // Khan Academy: Introduction to Motion
                'duration_minutes' => 12,
                'notes' => 'Basic concepts of motion and how we describe movement',
                'order_index' => 1
            ],
            [
                'title' => 'Newton\'s Laws of Motion',
                'description' => 'The three fundamental laws governing motion',
                'video_path' => 'https://www.youtube.com/watch?v=kKKM8Y-u7ds', // Newton's Laws
                'duration_minutes' => 15,
                'notes' => 'Understanding inertia, F=ma, and action-reaction pairs',
                'order_index' => 2
            ],
            [
                'title' => 'Gravity and Weight',
                'description' => 'Understanding gravitational force and its effects',
                'video_path' => 'https://www.youtube.com/watch?v=ne6BY_IjzUw', // Gravity Explained
                'duration_minutes' => 10,
                'notes' => 'How gravity affects objects on Earth and beyond',
                'order_index' => 3
            ]
        ];
        
        foreach ($motionLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $motionTopic->id,
                'is_published' => true
            ]));
        }
    }
    
    private function seedGeographyContent()
    {
        $geography = Subject::where('code', 'GEO')->first();
        if (!$geography) return;
        
        $this->command->info('🌍 Seeding Geography content...');
        
        // Topic 1: Physical Geography
        $physicalTopic = Topic::create([
            'subject_id' => $geography->id,
            'name' => 'Physical Geography of Malawi',
            'description' => 'Understanding Malawi\'s physical features and climate',
            'order_index' => 1
        ]);
        
        $physicalLessons = [
            [
                'title' => 'Landforms of Malawi',
                'description' => 'Mountains, plateaus, and the Great Rift Valley',
                'video_path' => 'https://www.youtube.com/watch?v=hFH4jCBmtVo', // Geography: Landforms
                'duration_minutes' => 11,
                'notes' => 'Exploring Malawi\'s diverse physical landscape',
                'order_index' => 1
            ],
            [
                'title' => 'Lake Malawi and Water Bodies',
                'description' => 'Studying Malawi\'s lakes, rivers, and water resources',
                'video_path' => 'https://www.youtube.com/watch?v=Y0tz8GhYfuI', // African Great Lakes
                'duration_minutes' => 13,
                'notes' => 'The importance of water resources in Malawi',
                'order_index' => 2
            ],
            [
                'title' => 'Climate and Weather',
                'description' => 'Understanding Malawi\'s climate patterns and seasons',
                'video_path' => 'https://www.youtube.com/watch?v=e6GJTyFdUuE', // Climate vs Weather
                'duration_minutes' => 9,
                'notes' => 'How climate affects agriculture and daily life in Malawi',
                'order_index' => 3
            ]
        ];
        
        foreach ($physicalLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $physicalTopic->id,
                'is_published' => true
            ]));
        }
    }
    
    private function seedComputerStudiesContent()
    {
        $cs = Subject::where('code', 'CS')->first();
        if (!$cs) return;
        
        $this->command->info('💻 Seeding Computer Studies content...');
        
        // Topic 1: Computer Basics
        $basicsTopic = Topic::create([
            'subject_id' => $cs->id,
            'name' => 'Computer Fundamentals',
            'description' => 'Introduction to computers and digital technology',
            'order_index' => 1
        ]);
        
        $basicsLessons = [
            [
                'title' => 'What is a Computer?',
                'description' => 'Hardware, software, and basic computer operations',
                'video_path' => 'https://www.youtube.com/watch?v=mCq8-xTH7jA', // Computer Basics
                'duration_minutes' => 8,
                'notes' => 'Understanding the basic components and functions of computers',
                'order_index' => 1
            ],
            [
                'title' => 'Input and Output Devices',
                'description' => 'Keyboard, mouse, monitor, printer, and other peripherals',
                'video_path' => 'https://www.youtube.com/watch?v=Jg-cHuNB3tQ', // Input Output Devices
                'duration_minutes' => 12,
                'notes' => 'How we interact with computers through various devices',
                'order_index' => 2
            ],
            [
                'title' => 'File Management',
                'description' => 'Creating, saving, and organizing files and folders',
                'video_path' => 'https://www.youtube.com/watch?v=k-EID5_2D9U', // File Management
                'duration_minutes' => 10,
                'notes' => 'Essential skills for organizing digital information',
                'order_index' => 3
            ]
        ];
        
        foreach ($basicsLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $basicsTopic->id,
                'is_published' => true
            ]));
        }
        
        // Topic 2: Internet and Digital Citizenship
        $internetTopic = Topic::create([
            'subject_id' => $cs->id,
            'name' => 'Internet and Digital Citizenship',
            'description' => 'Using the internet safely and responsibly',
            'order_index' => 2
        ]);
        
        $internetLessons = [
            [
                'title' => 'Introduction to the Internet',
                'description' => 'How the internet works and basic web navigation',
                'video_path' => 'https://www.youtube.com/watch?v=kBXQZMmiA4s', // How the Internet Works
                'duration_minutes' => 14,
                'notes' => 'Understanding the global network that connects the world',
                'order_index' => 1
            ],
            [
                'title' => 'Online Safety and Security',
                'description' => 'Protecting personal information and avoiding online threats',
                'video_path' => 'https://www.youtube.com/watch?v=R0EtSzPfIxU', // Internet Safety
                'duration_minutes' => 11,
                'notes' => 'Essential skills for safe internet use in the modern world',
                'order_index' => 2
            ]
        ];
        
        foreach ($internetLessons as $lesson) {
            Lesson::create(array_merge($lesson, [
                'topic_id' => $internetTopic->id,
                'is_published' => true
            ]));
        }
    }
}