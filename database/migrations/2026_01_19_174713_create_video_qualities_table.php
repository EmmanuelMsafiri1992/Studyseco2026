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
        Schema::create('video_qualities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('quality'); // 240p, 480p, 720p, 1080p
            $table->integer('width');
            $table->integer('height');
            $table->integer('bitrate'); // in kbps
            $table->string('video_path')->nullable();
            $table->string('hls_playlist_path')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('storage_disk')->default('public');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['lesson_id', 'quality']);
            $table->index(['lesson_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_qualities');
    }
};
