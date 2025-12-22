<?php

namespace Database\Seeders;

use App\Models\SupportChat;
use App\Models\SupportChatMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupportChatSeeder extends Seeder
{
    public function run(): void
    {
        // Get users for realistic data
        $admin = User::where('role', 'admin')->first();
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();

        if (!$admin || !$teacher || !$student) {
            $this->command->warn('⚠️  Required users not found. Skipping support chat seeder.');
            return;
        }

        // Create sample support chats
        $chats = [
            [
                'session_id' => Str::uuid(),
                'user_id' => $student->id,
                'agent_id' => $admin->id,
                'status' => 'closed',
                'priority' => 'normal',
                'category' => 'payment',
                'initial_message' => 'Hi, I need help with my payment verification. I made a payment yesterday but my account is still not activated.',
                'started_at' => now()->subDays(2),
                'assigned_at' => now()->subDays(2)->addHours(1),
                'closed_at' => now()->subDays(1),
                'resolution_summary' => 'Payment was verified and account activated successfully.',
                'satisfaction_rating' => 5,
                'satisfaction_feedback' => 'Very helpful and quick response. Thank you!'
            ],
            [
                'session_id' => Str::uuid(),
                'user_id' => $student->id,
                'agent_id' => $teacher->id,
                'status' => 'active',
                'priority' => 'high',
                'category' => 'technical',
                'initial_message' => 'I cannot access the lessons. The page keeps loading but never shows the content.',
                'started_at' => now()->subHours(3),
                'assigned_at' => now()->subHours(2),
            ],
            [
                'session_id' => Str::uuid(),
                'user_id' => $student->id,
                'status' => 'waiting',
                'priority' => 'normal',
                'category' => 'enrollment',
                'initial_message' => 'Hello, I want to add Mathematics to my subjects. How can I do this?',
                'started_at' => now()->subMinutes(30),
                'queue_position' => 1,
            ],
            [
                'session_id' => Str::uuid(),
                'user_id' => null,
                'user_name' => 'John Guest',
                'user_email' => 'john@example.com',
                'user_info' => [
                    'phone' => '+265 999 123 456',
                    'location' => 'Lilongwe, Malawi'
                ],
                'status' => 'waiting',
                'priority' => 'low',
                'category' => 'general',
                'initial_message' => 'Hi, I would like to know more about your courses and pricing.',
                'started_at' => now()->subMinutes(15),
                'queue_position' => 2,
            ]
        ];

        foreach ($chats as $chatData) {
            $chat = SupportChat::create($chatData);

            // Add sample messages for each chat
            $this->addMessages($chat);
        }

        $this->command->info('✅ Created ' . count($chats) . ' sample support chats with messages');
    }

    private function addMessages(SupportChat $chat): void
    {
        $messages = [];

        // Initial message
        $messages[] = [
            'support_chat_id' => $chat->id,
            'user_id' => $chat->user_id,
            'message' => $chat->initial_message,
            'sender_type' => 'user',
            'is_read_by_agent' => $chat->status !== 'waiting',
            'read_by_agent_at' => $chat->status !== 'waiting' ? $chat->assigned_at : null,
            'created_at' => $chat->started_at,
        ];

        // Add conversation based on status
        if ($chat->status === 'closed') {
            // Closed chat - complete conversation
            $messages[] = [
                'support_chat_id' => $chat->id,
                'user_id' => $chat->agent_id,
                'message' => 'Hello! I\'m here to help you with your payment verification. Let me check your account details.',
                'sender_type' => 'agent',
                'is_read_by_user' => true,
                'read_by_user_at' => $chat->started_at->addMinutes(2),
                'created_at' => $chat->assigned_at->addMinutes(1),
            ];

            $messages[] = [
                'support_chat_id' => $chat->id,
                'user_id' => $chat->user_id,
                'message' => 'Thank you! My reference number is PAY123456.',
                'sender_type' => 'user',
                'is_read_by_agent' => true,
                'read_by_agent_at' => $chat->assigned_at->addMinutes(3),
                'created_at' => $chat->assigned_at->addMinutes(2),
            ];

            $messages[] = [
                'support_chat_id' => $chat->id,
                'user_id' => $chat->agent_id,
                'message' => 'I found your payment and have verified it. Your account is now activated. You should have full access to all subjects.',
                'sender_type' => 'agent',
                'is_read_by_user' => true,
                'read_by_user_at' => $chat->assigned_at->addMinutes(6),
                'created_at' => $chat->assigned_at->addMinutes(5),
            ];
        } elseif ($chat->status === 'active') {
            // Active chat - ongoing conversation
            $messages[] = [
                'support_chat_id' => $chat->id,
                'user_id' => $chat->agent_id,
                'message' => 'Hi! I understand you\'re having trouble accessing lessons. Let me help you troubleshoot this issue.',
                'sender_type' => 'agent',
                'is_read_by_user' => true,
                'read_by_user_at' => $chat->assigned_at->addMinutes(2),
                'created_at' => $chat->assigned_at->addMinutes(1),
            ];

            $messages[] = [
                'support_chat_id' => $chat->id,
                'user_id' => $chat->user_id,
                'message' => 'Yes, it happens on both my phone and computer. The loading spinner just keeps spinning.',
                'sender_type' => 'user',
                'is_read_by_agent' => false,
                'created_at' => $chat->assigned_at->addMinutes(3),
            ];
        }

        foreach ($messages as $messageData) {
            SupportChatMessage::create($messageData);
        }
    }
}