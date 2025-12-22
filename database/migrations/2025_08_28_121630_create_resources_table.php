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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // book, past_paper, document, video, audio
            $table->string('category')->nullable(); // textbook, reference, guide, etc.
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('grade_level')->nullable();
            $table->string('exam_board')->nullable(); // MANEB, Cambridge, etc.
            $table->integer('year')->nullable(); // for past papers
            $table->string('file_path'); // encrypted file path
            $table->string('file_type'); // pdf, doc, mp4, etc.
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('thumbnail')->nullable();
            $table->json('access_permissions')->nullable(); // roles/users who can access
            $table->boolean('is_active')->default(true);
            $table->boolean('is_protected')->default(true); // enable protection features
            $table->json('protection_settings')->nullable(); // custom protection config
            $table->integer('view_count')->default(0);
            $table->integer('download_attempts')->default(0); // track failed download attempts
            $table->timestamps();

            $table->index(['type', 'subject_id', 'is_active']);
            $table->index(['grade_level', 'exam_board']);
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
