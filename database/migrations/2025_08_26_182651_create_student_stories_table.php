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
        Schema::create('student_stories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('country_flag', 10);
            $table->string('current_status');
            $table->longText('story');
            $table->json('achievements')->nullable();
            $table->integer('msce_credits')->nullable();
            $table->string('avatar_color_from', 50);
            $table->string('avatar_color_to', 50);
            $table->string('achievement_icon', 10);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_featured', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_stories');
    }
};
