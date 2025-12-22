<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Display a listing of chat groups.
     */
    public function index()
    {
        $user = auth()->user();
        
        $chatGroups = $user->chatGroups()
            ->with(['subject', 'creator'])
            ->withCount(['users', 'messages'])
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->map(function ($group) use ($user) {
                $group->unread_count = $group->getUnreadCountForUser($user);
                $group->last_message = $group->recentMessages()->with('user')->first();
                return $group;
            });

        $subjects = Subject::all();
        $users = User::all();

        return Inertia::render('Chat/Index', [
            'chatGroups' => $chatGroups,
            'subjects' => $subjects,
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new chat group.
     */
    public function create()
    {
        $subjects = Subject::all();
        $users = User::all();

        return Inertia::render('Chat/Create', [
            'subjects' => $subjects,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created chat group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:general,subject',
            'subject_id' => 'nullable|exists:subjects,id',
            'max_members' => 'required|integer|min:2|max:500',
            'is_moderated' => 'boolean',
            'members' => 'array',
            'members.*' => 'exists:users,id'
        ]);

        $chatGroup = ChatGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'subject_id' => $validated['subject_id'] ?? null,
            'created_by' => auth()->id(),
            'max_members' => $validated['max_members'],
            'is_active' => true,
            'is_moderated' => $validated['is_moderated'] ?? false,
            'last_activity_at' => now()
        ]);

        // Add creator as admin
        $chatGroup->addMember(auth()->user(), 'admin');

        // Add selected members
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $userId) {
                $user = User::find($userId);
                if ($user && !$chatGroup->hasMember($user)) {
                    $chatGroup->addMember($user, 'member');
                }
            }
        }

        return redirect()->route('chat.show', $chatGroup)->with('success', 'Chat group created successfully.');
    }

    /**
     * Display the specified chat group.
     */
    public function show(ChatGroup $chatGroup)
    {
        $user = auth()->user();
        
        // Check if user is member
        if (!$chatGroup->hasMember($user)) {
            abort(403, 'You are not a member of this chat group.');
        }

        $chatGroup->load(['subject', 'creator', 'users']);
        
        // Get messages with pagination
        $messages = $chatGroup->messages()
            ->with(['user', 'replyTo.user'])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark as read
        $chatGroup->markAsReadForUser($user);

        return Inertia::render('Chat/Show', [
            'chatGroup' => $chatGroup,
            'messages' => $messages,
            'userRole' => $chatGroup->users()->where('user_id', $user->id)->first()?->pivot?->role ?? 'member'
        ]);
    }

    /**
     * Store a new message.
     */
    public function storeMessage(Request $request, ChatGroup $chatGroup)
    {
        $user = auth()->user();
        
        if (!$chatGroup->hasMember($user)) {
            abort(403, 'You are not a member of this chat group.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'message_type' => 'in:text,image,file,announcement',
            'reply_to' => 'nullable|exists:chat_messages,id',
            'attachments' => 'nullable|array'
        ]);

        $message = ChatMessage::create([
            'chat_group_id' => $chatGroup->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'text',
            'reply_to' => $validated['reply_to'] ?? null,
            'attachments' => $validated['attachments'] ?? null
        ]);

        $message->load('user', 'replyTo.user');

        // Update group activity
        $chatGroup->updateActivity();

        return response()->json([
            'message' => $message,
            'success' => true
        ]);
    }

    /**
     * Join a chat group.
     */
    public function join(ChatGroup $chatGroup)
    {
        $user = auth()->user();
        
        if ($chatGroup->hasMember($user)) {
            return back()->with('info', 'You are already a member of this group.');
        }

        if ($chatGroup->users()->count() >= $chatGroup->max_members) {
            return back()->withErrors(['error' => 'This group has reached its maximum capacity.']);
        }

        $chatGroup->addMember($user);

        return back()->with('success', 'Successfully joined the group.');
    }

    /**
     * Leave a chat group.
     */
    public function leave(ChatGroup $chatGroup)
    {
        $user = auth()->user();
        
        if (!$chatGroup->hasMember($user)) {
            return back()->withErrors(['error' => 'You are not a member of this group.']);
        }

        $chatGroup->removeMember($user);

        return redirect()->route('chat.index')->with('success', 'Successfully left the group.');
    }

    /**
     * Get messages for a chat group (AJAX).
     */
    public function getMessages(Request $request, ChatGroup $chatGroup)
    {
        $user = auth()->user();
        
        if (!$chatGroup->hasMember($user)) {
            abort(403);
        }

        $messages = $chatGroup->messages()
            ->with(['user', 'replyTo.user'])
            ->where('is_deleted', false)
            ->when($request->before, function ($query, $before) {
                $query->where('created_at', '<', $before);
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Add a member to the chat group.
     */
    public function addMember(Request $request, ChatGroup $chatGroup)
    {
        $user = auth()->user();
        
        // Check if user is admin or moderator
        if (!$chatGroup->isAdmin($user) && !$chatGroup->isModerator($user)) {
            abort(403, 'You do not have permission to add members to this group.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $memberToAdd = User::find($validated['user_id']);
        
        if ($chatGroup->hasMember($memberToAdd)) {
            return back()->withErrors(['error' => 'User is already a member of this group.']);
        }

        if ($chatGroup->users()->count() >= $chatGroup->max_members) {
            return back()->withErrors(['error' => 'This group has reached its maximum capacity.']);
        }

        $chatGroup->addMember($memberToAdd);

        return back()->with('success', 'Member added successfully.');
    }

    /**
     * Remove a member from the chat group.
     */
    public function removeMember(ChatGroup $chatGroup, User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user is admin or moderator (or removing themselves)
        if (!$chatGroup->isAdmin($currentUser) && !$chatGroup->isModerator($currentUser) && $currentUser->id !== $user->id) {
            abort(403, 'You do not have permission to remove members from this group.');
        }

        if (!$chatGroup->hasMember($user)) {
            return back()->withErrors(['error' => 'User is not a member of this group.']);
        }

        // Prevent removing the creator unless they're removing themselves
        if ($chatGroup->created_by === $user->id && $currentUser->id !== $user->id) {
            return back()->withErrors(['error' => 'Cannot remove the group creator.']);
        }

        $chatGroup->removeMember($user);

        $message = $currentUser->id === $user->id ? 'Successfully left the group.' : 'Member removed successfully.';
        
        return $currentUser->id === $user->id 
            ? redirect()->route('chat.index')->with('success', $message)
            : back()->with('success', $message);
    }
}