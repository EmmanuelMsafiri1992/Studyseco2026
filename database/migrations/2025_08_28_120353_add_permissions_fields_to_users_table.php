<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('custom_permissions')->nullable()->after('role');
            $table->json('dashboard_preferences')->nullable()->after('custom_permissions');
            $table->boolean('is_active')->default(true)->after('dashboard_preferences');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('profile_photo_url')->nullable()->after('last_login_at');
            $table->string('phone')->nullable()->after('profile_photo_url');
            $table->text('bio')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'custom_permissions',
                'dashboard_preferences',
                'is_active',
                'last_login_at',
                'profile_photo_url',
                'phone',
                'bio'
            ]);
        });
    }
};