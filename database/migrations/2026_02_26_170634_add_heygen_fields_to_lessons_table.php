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
        Schema::table('lessons', function (Blueprint $table) {
            // HeyGen video generation fields
            $table->text('heygen_script')->nullable()->after('notes');
            $table->string('heygen_avatar_id')->nullable()->after('heygen_script');
            $table->string('heygen_voice_id')->nullable()->after('heygen_avatar_id');
            $table->string('heygen_video_id')->nullable()->after('heygen_voice_id');
            $table->enum('heygen_status', ['none', 'pending', 'processing', 'completed', 'failed'])
                ->default('none')->after('heygen_video_id');
            $table->text('heygen_error')->nullable()->after('heygen_status');
            $table->timestamp('heygen_started_at')->nullable()->after('heygen_error');
            $table->timestamp('heygen_completed_at')->nullable()->after('heygen_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'heygen_script',
                'heygen_avatar_id',
                'heygen_voice_id',
                'heygen_video_id',
                'heygen_status',
                'heygen_error',
                'heygen_started_at',
                'heygen_completed_at',
            ]);
        });
    }
};
