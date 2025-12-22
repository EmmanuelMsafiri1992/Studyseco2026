<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if an admin user already exists to prevent duplicates.
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
            $this->command->info('Admin user created successfully.');
        } else {
            $this->command->info('Admin user already exists. Skipping.');
        }

        // Check if a teacher user already exists.
        if (!User::where('email', 'teacher@example.com')->exists()) {
            User::create([
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]);
            $this->command->info('Teacher user created successfully.');
        } else {
            $this->command->info('Teacher user already exists. Skipping.');
        }

        // Check if a student user already exists.
        if (!User::where('email', 'student@example.com')->exists()) {
            User::create([
                'name' => 'Student User',
                'email' => 'student@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
            ]);
            $this->command->info('Student user created successfully.');
        } else {
            $this->command->info('Student user already exists. Skipping.');
        }
    }
}
