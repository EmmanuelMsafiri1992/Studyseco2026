<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportChat;
use App\Models\SupportChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Start a new chat session for guest users from landing page
     */
    public function startChat(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'initial_message' => 'required|string',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        // Create support chat session for guest user
        $chat = SupportChat::create([
            'session_id' => Str::uuid(),
            'user_id' => null, // Guest user
            'status' => 'waiting',
            'priority' => $validated['priority'] ?? 'normal',
            'category' => 'General Support',
            'user_name' => $validated['name'] ?? 'Guest',
            'user_email' => $validated['email'] ?? null,
            'user_info' => $request->only(['user_agent', 'ip_address']),
            'initial_message' => $validated['initial_message'],
            'started_at' => now(),
        ]);

        // Create initial message
        $chat->messages()->create([
            'user_id' => null,
            'message' => $validated['initial_message'],
            'message_type' => 'text',
            'sender_type' => 'user',
        ]);

        // Create welcome system message
        $chat->messages()->create([
            'user_id' => null,
            'message' => 'Thanks for your message! A StudySeco representative will respond soon!',
            'message_type' => 'system',
            'sender_type' => 'system',
        ]);

        // Update queue positions
        $this->updateQueuePositions();

        return response()->json([
            'success' => true,
            'session_id' => $chat->session_id,
            'queue_position' => $chat->queue_position,
            'message' => 'Chat session started successfully',
        ]);
    }

    /**
     * Send a message in an existing chat session
     */
    public function sendMessage(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $chat = SupportChat::where('session_id', $sessionId)->first();

        if (!$chat) {
            return response()->json(['error' => 'Chat session not found'], 404);
        }

        if ($chat->status === 'closed') {
            return response()->json(['error' => 'Chat session is closed'], 400);
        }

        // Create message
        $message = $chat->messages()->create([
            'user_id' => null,
            'message' => $validated['message'],
            'message_type' => 'text',
            'sender_type' => 'user',
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'chat_status' => $chat->status,
            'queue_position' => $chat->queue_position,
        ]);
    }

    /**
     * Get messages for a chat session
     */
    public function getMessages(Request $request, $sessionId)
    {
        $chat = SupportChat::where('session_id', $sessionId)
            ->with(['messages.user', 'agent'])
            ->first();

        if (!$chat) {
            return response()->json(['error' => 'Chat session not found'], 404);
        }

        // Mark messages as read by user
        $chat->messages()->where('sender_type', '!=', 'user')
            ->where('is_read_by_user', false)
            ->update([
                'is_read_by_user' => true,
                'read_by_user_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'chat' => [
                'session_id' => $chat->session_id,
                'status' => $chat->status,
                'queue_position' => $chat->queue_position,
                'agent_name' => $chat->agent?->name,
            ],
            'messages' => $chat->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->getSenderName(),
                    'created_at' => $message->created_at->format('H:i'),
                    'is_system' => $message->sender_type === 'system',
                ];
            }),
        ]);
    }

    /**
     * Get chat status and queue position
     */
    public function getStatus($sessionId)
    {
        $chat = SupportChat::where('session_id', $sessionId)->first();

        if (!$chat) {
            return response()->json(['error' => 'Chat session not found'], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $chat->status,
            'queue_position' => $chat->queue_position,
            'agent_name' => $chat->agent?->name,
            'estimated_wait' => $this->getEstimatedWaitTime($chat->queue_position),
        ]);
    }

    /**
     * Close chat session from user side
     */
    public function endChat($sessionId)
    {
        $chat = SupportChat::where('session_id', $sessionId)->first();

        if (!$chat) {
            return response()->json(['error' => 'Chat session not found'], 404);
        }

        $chat->close('Ended by user');

        return response()->json([
            'success' => true,
            'message' => 'Chat session ended',
        ]);
    }

    /**
     * Update queue positions for waiting chats
     */
    private function updateQueuePositions()
    {
        $waitingChats = SupportChat::waiting()->orderBy('created_at')->get();
        
        foreach ($waitingChats as $index => $chat) {
            $chat->updateQueuePosition($index + 1);
        }
    }

    /**
     * Get estimated wait time based on queue position
     */
    private function getEstimatedWaitTime($position)
    {
        if ($position <= 1) {
            return 'Next in line';
        } elseif ($position <= 3) {
            return '2-3 minutes';
        } elseif ($position <= 5) {
            return '3-5 minutes';
        } else {
            return '5-10 minutes';
        }
    }
}
