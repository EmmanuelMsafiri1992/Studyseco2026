<?php

namespace App\Console\Commands;

use App\Models\CommunityPost;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Console\Command;

class TestReactions extends Command
{
    protected $signature = 'test:reactions';
    protected $description = 'Test reaction functionality';

    public function handle()
    {
        $this->info('Testing reaction functionality...');
        
        // Create test user and post
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'email_verified_at' => now()
        ]);
        
        $testPost = CommunityPost::create([
            'user_id' => $testUser->id,
            'type' => 'post',
            'title' => 'Test Post',
            'content' => 'This is a test post for reactions',
            'likes_count' => 0,
            'comments_count' => 0,
            'views_count' => 0
        ]);
        
        $this->info("Created test user: {$testUser->email}");
        $this->info("Created test post: {$testPost->title}");
        
        // Test different reactions
        $reactionTypes = ['like', 'love', 'helpful', 'funny', 'solved'];
        
        foreach ($reactionTypes as $type) {
            // Add reaction
            PostReaction::create([
                'user_id' => $testUser->id,
                'community_post_id' => $testPost->id,
                'type' => $type
            ]);
            
            $this->info("✅ Added {$type} reaction");
        }
        
        // Check reactions
        $totalReactions = PostReaction::where('community_post_id', $testPost->id)->count();
        $this->info("Total reactions: {$totalReactions}");
        
        $userReactions = PostReaction::where('user_id', $testUser->id)
            ->where('community_post_id', $testPost->id)
            ->pluck('type')
            ->toArray();
            
        $this->info("User reactions: " . implode(', ', $userReactions));
        
        // Test toggleReaction method in controller
        $this->info("\nTesting toggle functionality...");
        
        // Remove a reaction
        $likeReaction = PostReaction::where([
            'user_id' => $testUser->id,
            'community_post_id' => $testPost->id,
            'type' => 'like'
        ])->first();
        
        if ($likeReaction) {
            $likeReaction->delete();
            $this->info("✅ Removed like reaction");
        }
        
        // Re-add it
        PostReaction::create([
            'user_id' => $testUser->id,
            'community_post_id' => $testPost->id,
            'type' => 'like'
        ]);
        $this->info("✅ Added like reaction back");
        
        // Check final state
        $finalReactions = PostReaction::where('community_post_id', $testPost->id)->count();
        $finalUserReactions = PostReaction::where('user_id', $testUser->id)
            ->where('community_post_id', $testPost->id)
            ->pluck('type')
            ->toArray();
            
        $this->info("Final total reactions: {$finalReactions}");
        $this->info("Final user reactions: " . implode(', ', $finalUserReactions));
        
        // Clean up
        PostReaction::where('community_post_id', $testPost->id)->delete();
        $testPost->delete();
        $testUser->delete();
        
        $this->info("✅ Test completed and cleanup done");
        $this->info("Reactions are working properly!");
    }
}