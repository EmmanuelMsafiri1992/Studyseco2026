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
        Schema::create('support_chats', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->unsignedBigInteger('user_id')->nullable(); // Null for guest users
            $table->unsignedBigInteger('agent_id')->nullable(); // Admin/Teacher handling the chat
            $table->string('status')->default('waiting'); // waiting, active, closed
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('category')->nullable();
            $table->string('user_name')->nullable(); // For guest users
            $table->string('user_email')->nullable(); // For guest users
            $table->json('user_info')->nullable(); // Additional guest user info
            $table->timestamp('started_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('queue_position')->nullable();
            $table->text('initial_message')->nullable();
            $table->json('tags')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->integer('satisfaction_rating')->nullable(); // 1-5
            $table->text('satisfaction_feedback')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'created_at']);
            $table->index(['agent_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_chats');
    }
};
