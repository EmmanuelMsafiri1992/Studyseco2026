<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetUserPassword extends Command
{
    protected $signature = 'user:set-password {user} {password}';
    protected $description = 'Set password for a user';

    public function handle()
    {
        $userId = $this->argument('user');
        $password = $this->argument('password');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }
        
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->save();
        
        $this->info("✅ Password set successfully!");
        $this->info("📧 Email: {$user->email}");
        $this->info("🔑 Password: {$password}");
        $this->info("👤 Name: {$user->name}");
        $this->info("🌐 Login: http://127.0.0.1:8000/login");
        
        return 0;
    }
}