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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('email_verification_token')->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('email_verification_token');
            $table->string('phone_verification_code', 6)->nullable()->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_verification_code');
            $table->timestamp('verification_expires_at')->nullable()->after('phone_verified_at');
            $table->boolean('is_verified')->default(false)->after('verification_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_token',
                'email_verified_at',
                'phone_verification_code',
                'phone_verified_at',
                'verification_expires_at',
                'is_verified'
            ]);
        });
    }
};