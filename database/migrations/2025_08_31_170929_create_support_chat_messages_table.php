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
        Schema::create('support_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_chat_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Null for system messages
            $table->text('message');
            $table->string('message_type')->default('text'); // text, image, file, system
            $table->json('attachments')->nullable();
            $table->string('sender_type'); // user, agent, system
            $table->boolean('is_read_by_user')->default(false);
            $table->boolean('is_read_by_agent')->default(false);
            $table->timestamp('read_by_user_at')->nullable();
            $table->timestamp('read_by_agent_at')->nullable();
            $table->timestamps();
            
            $table->foreign('support_chat_id')->references('id')->on('support_chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['support_chat_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_chat_messages');
    }
};
