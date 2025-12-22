<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\SharedResource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    /**
     * Display the community feed.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $subject = $request->get('subject');
        $sort = $request->get('sort', 'recent');
        $user = auth()->user();

        // Optimized query with minimal relations
        $posts = CommunityPost::select([
                'id', 'user_id', 'type', 'title', 'content', 'subject_id', 'priority',
                'is_anonymous', 'is_solved', 'is_featured', 'is_pinned', 'tags',
                'likes_count', 'comments_count', 'views_count', 'shares_count',
                'poll_options', 'poll_votes', 'created_at'
            ])
            ->with([
                'user:id,name,email,profile_photo_url', 
                'subject:id,name'
            ])
            ->when($filter !== 'all', fn($query) => $query->where('type', $filter))
            ->when($subject, fn($query) => $query->where('subject_id', $subject))
            ->when($sort === 'trending', fn($query) => $query->trending())
            ->when($sort === 'popular', fn($query) => $query->orderBy('likes_count', 'desc'))
            ->when($sort === 'solved', fn($query) => $query->where('is_solved', true))
            ->when($sort === 'recent', fn($query) => $query->latest())
            ->paginate(15); // Reduced from 20 to 15

        // Batch check user reactions to avoid N+1 queries
        if ($user) {
            $postIds = $posts->pluck('id');
            $userReactions = PostReaction::where('user_id', $user->id)
                ->whereIn('community_post_id', $postIds)
                ->get()
                ->groupBy('community_post_id');

            $posts->getCollection()->transform(function ($post) use ($userReactions) {
                $reactions = $userReactions->get($post->id, collect());
                $reactionTypes = $reactions->pluck('type')->toArray();
                
                $post->user_has_liked = in_array('like', $reactionTypes);
                $post->user_has_helpful = in_array('helpful', $reactionTypes);
                $post->user_has_love = in_array('love', $reactionTypes);
                $post->user_has_funny = in_array('funny', $reactionTypes);
                $post->user_has_solved = in_array('solved', $reactionTypes);
                $post->user_reactions = $reactionTypes;
                
                // Truncate content for feed view
                if (strlen($post->content) > 300) {
                    $post->content_preview = substr($post->content, 0, 300) . '...';
                } else {
                    $post->content_preview = $post->content;
                }
                
                return $post;
            });
        }

        // Cache subjects for 1 hour
        $subjects = cache()->remember('active_subjects', 3600, function () {
            return Subject::select('id', 'name')->where('is_active', true)->get();
        });
        
        return Inertia::render('Community/Index', [
            'posts' => $posts,
            'subjects' => $subjects,
            'filters' => compact('filter', 'subject', 'sort')
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $subjects = Subject::where('is_active', true)->get();
        
        return Inertia::render('Community/Create', [
            'subjects' => $subjects
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:post,question,resource,announcement',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
            'subject_id' => 'nullable|exists:subjects,id',
            'priority' => 'in:low,medium,high,urgent',
            'is_anonymous' => 'boolean',
            'tags' => 'array',
            'poll_options' => 'array|max:10',
            'poll_expires_at' => 'nullable|date|after:now',
            'attachments' => 'array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('community-uploads', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        $post = CommunityPost::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'subject_id' => $validated['subject_id'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'tags' => $validated['tags'] ?? [],
            'attachments' => $attachments,
            'poll_options' => $validated['poll_options'] ?? null,
            'poll_expires_at' => $validated['poll_expires_at'] ?? null,
        ]);

        return redirect()->route('community.show', $post)->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified post.
     */
    public function show(CommunityPost $post)
    {
        $post->incrementViews();
        
        $post->load([
            'user', 
            'subject', 
            'reactions.user',
            'comments' => function($query) {
                $query->with(['user', 'replies.user'])->orderBy('is_solution', 'desc')->orderBy('created_at', 'asc');
            },
            'sharedResources'
        ]);

        $user = auth()->user();
        
        if ($user) {
            // Get all user reactions for this post
            $userReactions = PostReaction::where('user_id', $user->id)
                ->where('community_post_id', $post->id)
                ->pluck('type')
                ->toArray();
            
            $post->user_has_liked = in_array('like', $userReactions);
            $post->user_has_helpful = in_array('helpful', $userReactions);
            $post->user_has_love = in_array('love', $userReactions);
            $post->user_has_funny = in_array('funny', $userReactions);
            $post->user_has_solved = in_array('solved', $userReactions);
            $post->user_reactions = $userReactions;
        } else {
            $post->user_has_liked = false;
            $post->user_has_helpful = false;
            $post->user_has_love = false;
            $post->user_has_funny = false;
            $post->user_has_solved = false;
            $post->user_reactions = [];
        }
        
        $post->can_mark_solution = $user && ($user->id === $post->user_id || $user->role === 'admin');

        return Inertia::render('Community/Show', [
            'post' => $post
        ]);
    }

    /**
     * Toggle reaction on a post.
     */
    public function toggleReaction(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'type' => 'required|in:like,love,helpful,solved,funny'
        ]);

        $userId = auth()->id();
        $type = $validated['type'];

        // Remove any existing reaction of this type from this user for this post
        $existingReaction = PostReaction::where([
            'user_id' => $userId,
            'community_post_id' => $post->id,
            'type' => $type
        ])->first();

        $hasReaction = false;
        
        if ($existingReaction) {
            // Remove reaction
            $existingReaction->delete();
            $hasReaction = false;
        } else {
            // Add new reaction
            PostReaction::create([
                'user_id' => $userId,
                'community_post_id' => $post->id,
                'type' => $type
            ]);
            $hasReaction = true;
        }

        // Recalculate total likes count (all reaction types)
        $totalReactions = PostReaction::where('community_post_id', $post->id)->count();
        $post->update(['likes_count' => $totalReactions]);
        
        // Get current user's reactions for this post
        $userReactions = PostReaction::where('user_id', $userId)
            ->where('community_post_id', $post->id)
            ->pluck('type')
            ->toArray();

        return response()->json([
            'success' => true,
            'type' => $type,
            'has_reaction' => $hasReaction,
            'likes_count' => $totalReactions,
            'user_has_liked' => in_array('like', $userReactions),
            'user_has_love' => in_array('love', $userReactions),
            'user_has_helpful' => in_array('helpful', $userReactions),
            'user_has_funny' => in_array('funny', $userReactions),
            'user_has_solved' => in_array('solved', $userReactions),
            'user_reactions' => $userReactions
        ]);
    }

    /**
     * Store a comment on a post.
     */
    public function storeComment(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:post_comments,id',
            'attachments' => 'array',
            'attachments.*' => 'file|max:5120', // 5MB max per file
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('comment-uploads', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        $comment = PostComment::create([
            'user_id' => auth()->id(),
            'community_post_id' => $post->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'attachments' => $attachments,
        ]);

        $comment->load('user');
        $post->increment('comments_count');

        // Return back to the post with a success message
        return back()->with('success', 'Comment posted successfully!');
    }

    /**
     * Mark a comment as the solution.
     */
    public function markSolution(PostComment $comment)
    {
        $post = $comment->communityPost;
        
        // Only post author or admin can mark solutions
        if (auth()->id() !== $post->user_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Remove previous solution
        $post->comments()->where('is_solution', true)->update(['is_solution' => false]);
        
        // Mark this as solution
        $comment->markAsSolution();

        return back()->with('success', 'Comment marked as solution!');
    }

    /**
     * Vote on a poll.
     */
    public function votePoll(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'option_index' => 'required|integer|min:0'
        ]);

        if (!$post->poll_options || $post->poll_expires_at < now()) {
            return response()->json(['error' => 'Poll is not available'], 400);
        }

        $votes = $post->poll_votes ?? [];
        $userId = auth()->id();
        $optionIndex = $validated['option_index'];

        // Remove previous vote if exists
        foreach ($votes as $index => $voters) {
            if (($key = array_search($userId, $voters)) !== false) {
                unset($votes[$index][$key]);
                $votes[$index] = array_values($votes[$index]);
            }
        }

        // Add new vote
        if (!isset($votes[$optionIndex])) {
            $votes[$optionIndex] = [];
        }
        $votes[$optionIndex][] = $userId;

        $post->update(['poll_votes' => $votes]);

        return response()->json(['success' => true, 'poll_votes' => $votes]);
    }

    /**
     * Share a resource.
     */
    public function shareResource(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:file,link,video,document',
            'subject_id' => 'nullable|exists:subjects,id',
            'tags' => 'array',
            'file' => 'nullable|file|max:51200', // 50MB max
            'external_url' => 'nullable|url',
        ]);

        $filePath = null;
        $fileSize = null;
        $mimeType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('shared-resources', 'public');
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
        }

        $resource = SharedResource::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'file_path' => $filePath,
            'external_url' => $validated['external_url'] ?? null,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'subject_id' => $validated['subject_id'] ?? null,
            'tags' => $validated['tags'] ?? [],
        ]);

        return redirect()->route('community.index')->with('success', 'Resource shared successfully!');
    }

    /**
     * Download a shared resource.
     */
    public function downloadResource(SharedResource $resource)
    {
        if (!$resource->file_path) {
            abort(404);
        }

        $resource->incrementDownloads();

        return Storage::disk('public')->download($resource->file_path, $resource->title);
    }

    /**
     * Show the form for editing a post.
     */
    public function edit(CommunityPost $post)
    {
        // Check if user can edit this post
        if (auth()->user()->id !== $post->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'You can only edit your own posts.');
        }

        $subjects = Subject::select('id', 'name')->get();
        
        return Inertia::render('Community/Edit', [
            'post' => $post->load('subject'),
            'subjects' => $subjects
        ]);
    }

    /**
     * Update a community post.
     */
    public function update(Request $request, CommunityPost $post)
    {
        // Check if user can edit this post
        if (auth()->user()->id !== $post->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'You can only edit your own posts.');
        }

        $validated = $request->validate([
            'type' => 'required|in:post,question,resource,announcement',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
            'subject_id' => 'nullable|exists:subjects,id',
            'priority' => 'in:low,medium,high,urgent',
            'is_anonymous' => 'boolean',
            'tags' => 'array',
            'poll_options' => 'array|max:10',
            'poll_expires_at' => 'nullable|date|after:now',
        ]);

        $post->update($validated);

        return redirect()->route('community.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Delete a community post.
     */
    public function destroy(CommunityPost $post)
    {
        // Check if user can delete this post
        if (auth()->user()->id !== $post->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'You can only delete your own posts.');
        }

        // Delete associated comments and reactions
        $post->comments()->delete();
        $post->reactions()->delete();
        
        // Delete the post
        $post->delete();

        return redirect()->route('community.index')
            ->with('success', 'Post deleted successfully!');
    }
}