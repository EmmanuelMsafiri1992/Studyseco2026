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
            $table->string('original_video_path')->nullable()->after('video_path');
            $table->enum('transcoding_status', ['pending', 'processing', 'completed', 'failed', 'none'])->default('none')->after('original_video_path');
            $table->string('master_playlist_path')->nullable()->after('transcoding_status');
            $table->integer('transcoding_progress')->default(0)->after('master_playlist_path');
            $table->timestamp('transcoding_started_at')->nullable()->after('transcoding_progress');
            $table->timestamp('transcoding_completed_at')->nullable()->after('transcoding_started_at');
            $table->integer('source_width')->nullable()->after('transcoding_completed_at');
            $table->integer('source_height')->nullable()->after('source_width');
            $table->integer('source_bitrate')->nullable()->after('source_height');
            $table->integer('duration_seconds')->nullable()->after('source_bitrate');

            $table->index('transcoding_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'original_video_path',
                'transcoding_status',
                'master_playlist_path',
                'transcoding_progress',
                'transcoding_started_at',
                'transcoding_completed_at',
                'source_width',
                'source_height',
                'source_bitrate',
                'duration_seconds',
            ]);
        });
    }
};
