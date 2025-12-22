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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->enum('game_type', ['trivia', 'math_challenge', 'word_puzzle', 'memory'])->default('trivia');
            $table->json('game_config')->nullable(); // Game-specific settings like questions, difficulty
            $table->integer('max_score')->default(100);
            $table->integer('time_limit')->nullable(); // Time limit in seconds
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
