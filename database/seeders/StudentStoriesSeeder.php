<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentStoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\StudentStory::create([
            'name' => 'Temwa Mwale',
            'location' => 'London, UK',
            'country_flag' => '🇬🇧',
            'current_status' => 'University of Edinburgh - Engineering',
            'story' => 'StudySeco allowed me to complete my Malawian secondary education while living in London. The teachers were incredible, and I passed my MSCE with 6 credits!',
            'achievements' => ['MSCE 6 Credits', 'Engineering Student', 'Top Performer'],
            'msce_credits' => 6,
            'avatar_color_from' => 'emerald-500',
            'avatar_color_to' => 'blue-500',
            'achievement_icon' => '🎓',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\StudentStory::create([
            'name' => 'Grace Kachingwe',
            'location' => 'Toronto, Canada',
            'country_flag' => '🇨🇦',
            'current_status' => 'University of Toronto - Medicine',
            'story' => 'The flexibility of studying online while maintaining the Malawian curriculum was perfect. I achieved my dream of studying medicine in Canada!',
            'achievements' => ['MSCE 8 Credits', 'Medical Student', 'Scholarship Winner'],
            'msce_credits' => 8,
            'avatar_color_from' => 'purple-500',
            'avatar_color_to' => 'pink-500',
            'achievement_icon' => '🏆',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        \App\Models\StudentStory::create([
            'name' => 'James Nyirenda',
            'location' => 'Sydney, Australia',
            'country_flag' => '🇦🇺',
            'current_status' => 'UNSW Sydney - MBA Student',
            'story' => 'StudySeco prepared me well for university. The quality of education was exceptional, and I\'m now pursuing my MBA while working in Sydney.',
            'achievements' => ['MSCE 7 Credits', 'MBA Student', 'Business Leader'],
            'msce_credits' => 7,
            'avatar_color_from' => 'orange-500',
            'avatar_color_to' => 'red-500',
            'achievement_icon' => '💼',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
