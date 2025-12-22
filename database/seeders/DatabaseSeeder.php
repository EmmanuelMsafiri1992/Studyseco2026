<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
public function run()
{
    $this->call([
        UserSeeder::class,
        RolePermissionSeeder::class,
        MalawiSubjectsSeederV2::class,
        TopicLessonSeeder::class,
        PaymentMethodSeeder::class,
        PaymentConfigurationSeeder::class,
        SiteContentSeeder::class,
        StudentStoriesSeeder::class,
        LibrarySeeder::class,
        SecondarySchoolSeeder::class,
        AuditLogSeeder::class,
        AiTrainingMaterialSeeder::class,
        SupportChatSeeder::class,
    ]);
}
}
