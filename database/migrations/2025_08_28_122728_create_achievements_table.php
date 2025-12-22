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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // academic, sports, conduct, extracurricular, leadership
            $table->string('level'); // bronze, silver, gold, platinum
            $table->string('category')->nullable(); // specific subject or activity
            $table->integer('points')->default(0); // achievement points
            $table->json('criteria_met')->nullable(); // specific criteria that were met
            $table->string('badge_icon')->nullable(); // icon for the badge
            $table->string('badge_color')->default('#3B82F6'); // badge color
            $table->date('achieved_date');
            $table->foreignId('awarded_by')->constrained('users')->onDelete('cascade'); // teacher/admin who awarded
            $table->text('awarding_notes')->nullable(); // notes from the awarding person
            $table->boolean('is_public')->default(true); // can be displayed publicly
            $table->boolean('is_featured')->default(false); // featured achievement
            $table->integer('display_order')->default(0); // for sorting
            $table->timestamps();

            $table->index(['user_id', 'type', 'achieved_date']);
            $table->index(['is_public', 'is_featured']);
            $table->index('achieved_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
