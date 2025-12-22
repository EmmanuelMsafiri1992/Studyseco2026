<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\EnrollmentPayment;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users for realistic audit data
        $admin = User::where('role', 'admin')->first();
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();

        // Sample audit logs
        $auditLogs = [
            [
                'user_id' => $admin?->id,
                'event' => 'login',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'url' => 'http://127.0.0.1:8000/login',
                'created_at' => now()->subHours(2)
            ],
            [
                'user_id' => $teacher?->id,
                'event' => 'login',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'url' => 'http://127.0.0.1:8000/login',
                'created_at' => now()->subHours(1)
            ],
            [
                'user_id' => $student?->id,
                'event' => 'enrollment',
                'ip_address' => '10.0.0.50',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
                'url' => 'http://127.0.0.1:8000/enroll',
                'new_values' => [
                    'subjects' => ['Mathematics', 'English', 'Biology'],
                    'country' => 'Malawi',
                    'enrollment_type' => 'trial'
                ],
                'created_at' => now()->subMinutes(45)
            ],
            [
                'user_id' => $admin?->id,
                'event' => 'payment',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'url' => 'http://127.0.0.1:8000/admin/payments',
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'verified', 'amount' => 50000],
                'created_at' => now()->subMinutes(30)
            ],
            [
                'user_id' => $admin?->id,
                'event' => 'access_granted',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'url' => 'http://127.0.0.1:8000/admin/payments',
                'created_at' => now()->subMinutes(30)
            ],
            [
                'user_id' => $admin?->id,
                'event' => 'tutor_assigned',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'url' => 'http://127.0.0.1:8000/admin/tutor-assignments',
                'new_values' => [
                    'tutor_id' => $teacher?->id,
                    'tutor_name' => $teacher?->name,
                    'student_name' => $student?->name
                ],
                'created_at' => now()->subMinutes(15)
            ],
            [
                'user_id' => null,
                'event' => 'system',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Laravel/12.0 (Artisan Command)',
                'url' => 'artisan:backup',
                'created_at' => now()->subMinutes(5)
            ],
            [
                'user_id' => $student?->id,
                'event' => 'logout',
                'ip_address' => '10.0.0.50',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
                'url' => 'http://127.0.0.1:8000/logout',
                'created_at' => now()->subMinutes(2)
            ]
        ];

        foreach ($auditLogs as $log) {
            AuditLog::create($log);
        }

        $this->command->info('✅ Created ' . count($auditLogs) . ' sample audit log entries');
    }
}