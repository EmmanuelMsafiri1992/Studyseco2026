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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->enum('assignment_type', ['homework', 'project', 'quiz', 'essay', 'research', 'practice'])->default('homework');
            $table->json('attachments')->nullable(); // Array of file paths
            $table->dateTime('due_date');
            $table->integer('max_points')->default(100);
            $table->boolean('allow_late_submission')->default(false);
            $table->dateTime('late_submission_deadline')->nullable();
            $table->integer('late_penalty_percentage')->default(0); // 0-100%
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->json('assignment_settings')->nullable(); // Additional settings like file types allowed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
