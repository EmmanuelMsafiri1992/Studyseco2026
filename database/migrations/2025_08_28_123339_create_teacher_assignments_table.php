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
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('assignment_type')->default('automatic'); // automatic, manual, requested
            $table->string('status')->default('active'); // active, inactive, pending, completed
            $table->integer('priority_score')->default(0); // for automatic assignment algorithm
            $table->json('assignment_criteria')->nullable(); // criteria used for assignment
            $table->date('assigned_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null'); // admin who assigned
            $table->text('assignment_notes')->nullable();
            $table->json('performance_metrics')->nullable(); // track student progress
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'student_id', 'subject_id'], 'unique_teacher_student_subject');
            $table->index(['teacher_id', 'is_active']);
            $table->index(['student_id', 'is_active']);
            $table->index(['subject_id', 'is_active']);
            $table->index(['assignment_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
