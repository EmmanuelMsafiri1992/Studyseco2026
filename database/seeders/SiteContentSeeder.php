<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteContent;
use App\Models\StudentStory;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        // Sample site content
        $contents = [
            [
                'key' => 'hero_title',
                'title' => 'Hero Section Title',
                'content' => 'Unlock Your Potential with StudySeco - Excellence in Malawian Secondary Education',
                'meta_data' => json_encode(['section' => 'hero', 'priority' => 1]),
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'hero_subtitle',
                'title' => 'Hero Section Subtitle', 
                'content' => 'Comprehensive online learning platform designed specifically for Malawian secondary school students',
                'meta_data' => json_encode(['section' => 'hero', 'priority' => 2]),
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'about_title',
                'title' => 'About Section Title',
                'content' => 'About StudySeco',
                'meta_data' => json_encode(['section' => 'about', 'priority' => 1]),
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'about_content',
                'title' => 'About Section Content',
                'content' => 'StudySeco is dedicated to providing quality education resources tailored specifically for Malawian secondary school students. Our platform offers comprehensive study materials aligned with the national curriculum.',
                'meta_data' => json_encode(['section' => 'about', 'priority' => 2]),
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'key' => 'features_title',
                'title' => 'Features Section Title',
                'content' => 'Why Choose StudySeco?',
                'meta_data' => json_encode(['section' => 'features', 'priority' => 1]),
                'is_active' => true,
                'sort_order' => 5
            ]
        ];

        foreach ($contents as $content) {
            SiteContent::firstOrCreate(
                ['key' => $content['key']],
                $content
            );
        }

        // Sample student stories
        $stories = [
            [
                'name' => 'Temwa Chisale',
                'location' => 'Lilongwe',
                'country_flag' => '🇲🇼',
                'current_status' => 'University of Cape Town - Medical Student',
                'story' => 'StudySeco helped me excel in my MSCE examinations. The comprehensive study materials and practice tests prepared me well for university admission. Now I am pursuing my dream of becoming a doctor.',
                'achievements' => json_encode(['MSCE 9 Credits', 'UCT Medical School Admission', 'Academic Excellence Award']),
                'msce_credits' => 9,
                'avatar_color_from' => 'emerald-500',
                'avatar_color_to' => 'blue-500',
                'achievement_icon' => '🎓',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'James Mwale',
                'location' => 'Blantyre',
                'country_flag' => '🇲🇼',
                'current_status' => 'Chancellor College - Engineering Student',
                'story' => 'The mathematics and science resources on StudySeco were exceptional. I was able to understand complex concepts through the clear explanations and interactive content.',
                'achievements' => json_encode(['MSCE 8 Credits', 'Engineering Scholarship', 'Top 5% National Performance']),
                'msce_credits' => 8,
                'avatar_color_from' => 'blue-500',
                'avatar_color_to' => 'purple-500',
                'achievement_icon' => '🏆',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Grace Banda',
                'location' => 'Mzuzu',
                'country_flag' => '🇲🇼',
                'current_status' => 'Mzuzu University - Education Student',
                'story' => 'StudySeco transformed my approach to learning. The English and Chichewa language resources helped me improve my communication skills significantly.',
                'achievements' => json_encode(['MSCE 7 Credits', 'Teaching Diploma', 'Community Service Award']),
                'msce_credits' => 7,
                'avatar_color_from' => 'purple-500',
                'avatar_color_to' => 'pink-500',
                'achievement_icon' => '⭐',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($stories as $story) {
            StudentStory::firstOrCreate(
                ['name' => $story['name'], 'location' => $story['location']],
                $story
            );
        }
    }
}