<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestTeacherAccess extends Command
{
    protected $signature = 'test:teacher-access';
    protected $description = 'Test teacher access permissions';

    public function handle()
    {
        $teacher = User::where('role', 'teacher')->first();
        
        if (!$teacher) {
            $this->error('No teacher found in the system.');
            return;
        }
        
        $this->info("Found teacher: {$teacher->name}");
        $this->info("Role value: '{$teacher->role}'");
        $this->info("Email: {$teacher->email}");
        
        // Test the role checking logic
        $roles = ['admin', 'teacher'];
        $hasAccess = false;
        
        foreach ($roles as $role) {
            if ($teacher->role === $role) {
                $hasAccess = true;
                $this->info("✓ Teacher matches role: {$role}");
                break;
            }
        }
        
        if ($hasAccess) {
            $this->info("✅ Teacher should have access to complaints system");
        } else {
            $this->error("❌ Teacher does NOT have access - role mismatch");
        }
    }
}