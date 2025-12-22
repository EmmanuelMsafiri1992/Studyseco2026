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
            $table->string('enrollment_number')->unique()->after('id');
            $table->string('country')->nullable()->after('address');
            $table->string('region')->nullable()->after('country');
            $table->string('currency', 3)->default('MWK')->after('region');
            $table->decimal('price_per_subject', 10, 2)->nullable()->after('currency');
            $table->integer('subject_count')->default(0)->after('price_per_subject');
            $table->boolean('is_trial')->default(false)->after('status');
            $table->timestamp('trial_started_at')->nullable()->after('is_trial');
            $table->timestamp('trial_expires_at')->nullable()->after('trial_started_at');
            $table->timestamp('access_expires_at')->nullable()->after('trial_expires_at');
            $table->timestamp('approved_at')->nullable()->after('admin_notes');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'enrollment_number',
                'country',
                'region', 
                'currency',
                'price_per_subject',
                'subject_count',
                'is_trial',
                'trial_started_at',
                'trial_expires_at',
                'access_expires_at',
                'approved_at',
                'approved_by'
            ]);
        });
    }
};
