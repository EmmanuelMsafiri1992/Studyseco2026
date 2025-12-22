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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->text('submission_text')->nullable();
            $table->json('attachments')->nullable(); // Array of submitted file paths
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->decimal('grade', 5, 2)->nullable(); // e.g., 87.50
            $table->integer('points_earned')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->json('grading_rubric')->nullable(); // Detailed grading breakdown
            $table->boolean('is_late')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Ensure one submission per student per assignment (for current attempt)
            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'assignment_submission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
