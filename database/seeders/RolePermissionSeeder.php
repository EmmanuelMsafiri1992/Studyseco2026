<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Can view all users', 'category' => 'user_management'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'description' => 'Can create new users', 'category' => 'user_management'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'description' => 'Can edit user details', 'category' => 'user_management'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'description' => 'Can delete users', 'category' => 'user_management'],
            ['name' => 'Assign Roles', 'slug' => 'assign-roles', 'description' => 'Can assign roles to users', 'category' => 'user_management'],
            
            // Academic Management
            ['name' => 'View Subjects', 'slug' => 'view-subjects', 'description' => 'Can view all subjects', 'category' => 'academic'],
            ['name' => 'Create Subjects', 'slug' => 'create-subjects', 'description' => 'Can create new subjects', 'category' => 'academic'],
            ['name' => 'Edit Subjects', 'slug' => 'edit-subjects', 'description' => 'Can edit subjects', 'category' => 'academic'],
            ['name' => 'Delete Subjects', 'slug' => 'delete-subjects', 'description' => 'Can delete subjects', 'category' => 'academic'],
            ['name' => 'Manage Topics', 'slug' => 'manage-topics', 'description' => 'Can manage topics and lessons', 'category' => 'academic'],
            
            // Payment Management
            ['name' => 'View Payments', 'slug' => 'view-payments', 'description' => 'Can view all payments', 'category' => 'financial'],
            ['name' => 'Verify Payments', 'slug' => 'verify-payments', 'description' => 'Can verify and approve payments', 'category' => 'financial'],
            ['name' => 'Manage Payment Methods', 'slug' => 'manage-payment-methods', 'description' => 'Can manage payment methods', 'category' => 'financial'],
            ['name' => 'View Financial Reports', 'slug' => 'view-financial-reports', 'description' => 'Can view financial reports', 'category' => 'financial'],
            
            // Chat & Communication
            ['name' => 'Join Chat Groups', 'slug' => 'join-chat-groups', 'description' => 'Can join subject-based chat groups', 'category' => 'communication'],
            ['name' => 'Create Chat Groups', 'slug' => 'create-chat-groups', 'description' => 'Can create new chat groups', 'category' => 'communication'],
            ['name' => 'Moderate Chats', 'slug' => 'moderate-chats', 'description' => 'Can moderate chat messages', 'category' => 'communication'],
            ['name' => 'Send Announcements', 'slug' => 'send-announcements', 'description' => 'Can send system-wide announcements', 'category' => 'communication'],
            
            // Analytics & Reports
            ['name' => 'View Analytics', 'slug' => 'view-analytics', 'description' => 'Can view system analytics', 'category' => 'analytics'],
            ['name' => 'View Student Progress', 'slug' => 'view-student-progress', 'description' => 'Can view student progress reports', 'category' => 'analytics'],
            ['name' => 'Export Reports', 'slug' => 'export-reports', 'description' => 'Can export system reports', 'category' => 'analytics'],
            
            // System Management
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'description' => 'Can manage system settings', 'category' => 'system'],
            ['name' => 'View System Logs', 'slug' => 'view-system-logs', 'description' => 'Can view system logs', 'category' => 'system'],
            ['name' => 'Manage Enrollments', 'slug' => 'manage-enrollments', 'description' => 'Can manage student enrollments', 'category' => 'system'],
            
            // Resources Management
            ['name' => 'Upload Resources', 'slug' => 'upload-resources', 'description' => 'Can upload learning resources', 'category' => 'resources'],
            ['name' => 'Manage Resources', 'slug' => 'manage-resources', 'description' => 'Can manage all resources', 'category' => 'resources'],
            ['name' => 'Download Resources', 'slug' => 'download-resources', 'description' => 'Can download learning resources', 'category' => 'resources'],
            
            // Assignments & Assessments
            ['name' => 'Create Assignments', 'slug' => 'create-assignments', 'description' => 'Can create assignments', 'category' => 'assessment'],
            ['name' => 'Grade Assignments', 'slug' => 'grade-assignments', 'description' => 'Can grade student assignments', 'category' => 'assessment'],
            ['name' => 'View Grades', 'slug' => 'view-grades', 'description' => 'Can view assignment grades', 'category' => 'assessment'],
            ['name' => 'Submit Assignments', 'slug' => 'submit-assignments', 'description' => 'Can submit assignments', 'category' => 'assessment'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Create Roles
        $roles = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-admin',
                'description' => 'Full system access with all permissions',
                'priority' => 100,
                'dashboard_features' => ['users', 'subjects', 'payments', 'analytics', 'settings', 'chat', 'resources', 'assignments']
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'System administrator with most permissions',
                'priority' => 90,
                'dashboard_features' => ['users', 'subjects', 'payments', 'analytics', 'chat', 'resources', 'assignments']
            ],
            [
                'name' => 'Teacher',
                'slug' => 'teacher',
                'description' => 'Educator with teaching and assessment permissions',
                'priority' => 50,
                'dashboard_features' => ['subjects', 'chat', 'resources', 'assignments', 'analytics']
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Learner with basic access permissions',
                'priority' => 10,
                'dashboard_features' => ['subjects', 'chat', 'resources', 'assignments']
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(['slug' => $roleData['slug']], $roleData);
            
            // Assign permissions to roles
            $this->assignPermissionsToRole($role);
        }
    }

    private function assignPermissionsToRole($role)
    {
        switch ($role->slug) {
            case 'super-admin':
                // Super admin gets all permissions
                $role->permissions()->sync(Permission::pluck('id'));
                break;
                
            case 'admin':
                // Admin gets most permissions except super-admin specific ones
                $permissions = Permission::whereNotIn('slug', ['view-system-logs'])->pluck('id');
                $role->permissions()->sync($permissions);
                break;
                
            case 'teacher':
                // Teacher permissions
                $teacherPermissions = [
                    'view-subjects', 'create-subjects', 'edit-subjects', 'manage-topics',
                    'join-chat-groups', 'create-chat-groups', 'moderate-chats',
                    'view-student-progress', 'export-reports',
                    'upload-resources', 'manage-resources', 'download-resources',
                    'create-assignments', 'grade-assignments', 'view-grades'
                ];
                $permissions = Permission::whereIn('slug', $teacherPermissions)->pluck('id');
                $role->permissions()->sync($permissions);
                break;
                
            case 'student':
                // Student permissions
                $studentPermissions = [
                    'view-subjects', 'join-chat-groups', 'download-resources',
                    'submit-assignments', 'view-grades'
                ];
                $permissions = Permission::whereIn('slug', $studentPermissions)->pluck('id');
                $role->permissions()->sync($permissions);
                break;
        }
    }
}