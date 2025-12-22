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
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('post'); // post, question, resource, announcement
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('attachments')->nullable(); // Files, images, links
            $table->json('tags')->nullable(); // Subject tags, categories
            $table->string('subject_id')->nullable(); // Related subject
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_solved')->default(false); // For questions
            $table->boolean('is_featured')->default(false);
            $table->boolean('allows_comments')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->json('poll_options')->nullable(); // For polls
            $table->json('poll_votes')->nullable(); // Poll voting data
            $table->timestamp('poll_expires_at')->nullable();
            $table->json('metadata')->nullable(); // Extra data for extensibility
            $table->timestamps();
            
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_id', 'created_at']);
            $table->index(['is_featured', 'is_pinned', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};
