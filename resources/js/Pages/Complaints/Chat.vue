<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    chat: Object,
    messages: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const newMessage = ref('');
const messageInput = ref(null);
const messagesContainer = ref(null);
const isTyping = ref(false);
const pollInterval = ref(null);

// Format message timestamp
const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Get message alignment based on sender
const getMessageAlignment = (message) => {
    if (message.sender_type === 'user') return 'justify-start';
    if (message.sender_type === 'agent') return 'justify-end';
    return 'justify-center';
};

// Get message styling based on sender
const getMessageStyle = (message) => {
    if (message.sender_type === 'user') {
        return 'bg-slate-100 text-slate-800 rounded-br-sm';
    }
    if (message.sender_type === 'agent') {
        return 'bg-blue-500 text-white rounded-bl-sm';
    }
    return 'bg-amber-100 text-amber-800 border border-amber-200 italic';
};

// Send message
const sendMessage = async () => {
    if (!newMessage.value.trim()) return;
    
    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isTyping.value = true;
    
    try {
        const response = await fetch(route('complaints.message', props.chat.session_id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                message: messageText,
                message_type: 'text'
            })
        });

        const data = await response.json();
        
        if (!data.message) {
            throw new Error('Failed to send message');
        }
        
        // Add the sent message to the local messages array
        if (props.messages) {
            props.messages.push({
                id: data.message.id,
                message: data.message.message,
                sender_type: data.message.sender_type,
                sender_name: data.message.user?.name,
                created_at: data.message.created_at,
                user: data.message.user
            });
        }
        
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
        // Add the message back if it failed
        newMessage.value = messageText;
    } finally {
        isTyping.value = false;
    }
};

// Close chat
const closeChat = () => {
    if (confirm('Are you sure you want to close this chat?')) {
        router.post(route('complaints.close', props.chat.session_id));
    }
};

// Scroll to bottom of messages
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Auto-refresh messages
onMounted(() => {
    scrollToBottom();
    
    // Focus message input
    if (messageInput.value) {
        messageInput.value.focus();
    }
    
    // Poll for new messages every 2 seconds
    pollInterval.value = setInterval(() => {
        router.reload({ 
            only: ['messages'],
            preserveScroll: true,
            onSuccess: () => {
                scrollToBottom();
            }
        });
    }, 2000);
});

// Clean up interval
onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
    }
});

// Get priority color
const getPriorityColor = (priority) => {
    const colors = {
        'low': 'bg-green-100 text-green-700',
        'normal': 'bg-blue-100 text-blue-700',
        'high': 'bg-orange-100 text-orange-700',
        'urgent': 'bg-red-100 text-red-700'
    };
    return colors[priority] || 'bg-gray-100 text-gray-700';
};

// Get status color
const getStatusColor = (status) => {
    const colors = {
        'waiting': 'bg-yellow-100 text-yellow-700',
        'active': 'bg-green-100 text-green-700',
        'closed': 'bg-gray-100 text-gray-700'
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="`Chat with ${chat.user?.name || chat.user_name || 'Guest'}`" />

    <DashboardLayout 
        :title="`Chat with ${chat.user?.name || chat.user_name || 'Guest'}`" 
        :subtitle="`${chat.category || 'General Support'} - ${chat.priority} priority`">

        <div class="max-w-6xl mx-auto">
            <!-- Chat Header -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ (chat.user?.name || chat.user_name || 'G').charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-800">{{ chat.user?.name || chat.user_name || 'Guest User' }}</h2>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span :class="getStatusColor(chat.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ chat.status }}
                                    </span>
                                    <span :class="getPriorityColor(chat.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ chat.priority }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ chat.category || 'General Support' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button 
                                @click="closeChat"
                                v-if="chat.status !== 'closed'"
                                class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 transition-colors duration-200">
                                Close Chat
                            </button>
                            <Link 
                                :href="route('complaints.index')"
                                class="px-4 py-2 bg-slate-500 text-white text-sm font-semibold rounded-xl hover:bg-slate-600 transition-colors duration-200">
                                Back to Dashboard
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Chat Info -->
                    <div class="mt-4 p-4 bg-slate-50 rounded-xl">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-slate-500">Started:</span>
                                <p class="font-semibold">{{ formatTime(chat.started_at) }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500">Session ID:</span>
                                <p class="font-mono text-xs">{{ chat.session_id.substring(0, 8) }}...</p>
                            </div>
                            <div v-if="chat.user_email">
                                <span class="text-slate-500">Email:</span>
                                <p class="font-semibold">{{ chat.user_email }}</p>
                            </div>
                            <div v-if="chat.queue_position">
                                <span class="text-slate-500">Queue Position:</span>
                                <p class="font-semibold">#{{ chat.queue_position }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 flex flex-col h-[70vh]">
                <!-- Messages Area -->
                <div 
                    ref="messagesContainer"
                    class="flex-1 p-6 overflow-y-auto space-y-4">
                    
                    <div v-if="messages?.length" class="space-y-4">
                        <div 
                            v-for="message in messages" 
                            :key="message.id"
                            :class="['flex', getMessageAlignment(message)]">
                            <div :class="['max-w-xs lg:max-w-md px-4 py-3 rounded-2xl text-sm', getMessageStyle(message)]">
                                <!-- Agent name for agent messages -->
                                <div v-if="message.sender_type === 'agent' && message.sender_name" 
                                     class="text-xs opacity-80 mb-1 font-semibold">
                                    {{ message.sender_name }}
                                </div>
                                
                                <!-- Message content -->
                                <div class="whitespace-pre-wrap">{{ message.message }}</div>
                                
                                <!-- Timestamp -->
                                <div :class="[
                                    'text-xs mt-2 opacity-70',
                                    message.sender_type === 'agent' ? 'text-blue-100' : 'text-slate-500'
                                ]">
                                    {{ formatTime(message.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- No messages state -->
                    <div v-else class="flex-1 flex items-center justify-center">
                        <div class="text-center text-slate-500">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C3.395 15.1 3 13.558 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9z"></path>
                            </svg>
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    </div>
                    
                    <!-- Typing indicator -->
                    <div v-if="isTyping" class="flex justify-end">
                        <div class="bg-blue-400 text-white px-4 py-2 rounded-2xl rounded-bl-sm text-sm">
                            <span class="animate-pulse">Sending...</span>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="p-6 border-t border-slate-200">
                    <form @submit.prevent="sendMessage" class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input 
                                ref="messageInput"
                                v-model="newMessage"
                                type="text" 
                                placeholder="Type your message..."
                                :disabled="chat.status === 'closed'"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-slate-100 disabled:cursor-not-allowed"
                                @keyup.enter="sendMessage">
                        </div>
                        <button 
                            type="submit" 
                            :disabled="!newMessage.trim() || isTyping || chat.status === 'closed'"
                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </form>
                    
                    <!-- Chat status message -->
                    <div v-if="chat.status === 'closed'" class="mt-3 text-center">
                        <span class="text-sm text-slate-500">This chat has been closed</span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>