<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    chatGroup: Object,
    messages: Object,
    userRole: String,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const messageInput = ref('');
const messagesContainer = ref(null);
const replyingTo = ref(null);
const isLoadingMessages = ref(false);
const allMessages = ref(props.messages.data || []);
const showMemberModal = ref(false);
const showAddMemberModal = ref(false);
const availableUsers = ref([]);

const addMemberForm = useForm({
    user_id: ''
});

const canManageMembers = computed(() => {
    return props.userRole === 'admin' || props.userRole === 'moderator';
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendMessage = async () => {
    if (!messageInput.value.trim()) return;

    const messageData = {
        message: messageInput.value.trim(),
        message_type: 'text',
        reply_to: replyingTo.value?.id || null
    };

    try {
        const response = await axios.post(
            route('chat.storeMessage', props.chatGroup.id), 
            messageData
        );

        if (response.data.success) {
            allMessages.value.push(response.data.message);
            messageInput.value = '';
            replyingTo.value = null;
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

const replyToMessage = (message) => {
    replyingTo.value = message;
    document.getElementById('message-input')?.focus();
};

const cancelReply = () => {
    replyingTo.value = null;
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const formatDate = (date) => {
    const messageDate = new Date(date);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (messageDate.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (messageDate.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return messageDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
};

const shouldShowDateSeparator = (currentMessage, previousMessage) => {
    if (!previousMessage) return true;
    
    const currentDate = new Date(currentMessage.created_at).toDateString();
    const previousDate = new Date(previousMessage.created_at).toDateString();
    
    return currentDate !== previousDate;
};

const isConsecutiveMessage = (currentMessage, previousMessage) => {
    if (!previousMessage) return false;
    
    const timeDiff = new Date(currentMessage.created_at) - new Date(previousMessage.created_at);
    const fiveMinutes = 5 * 60 * 1000;
    
    return currentMessage.user_id === previousMessage.user_id && timeDiff < fiveMinutes;
};

const fetchAvailableUsers = async () => {
    try {
        const response = await axios.get('/api/users');
        availableUsers.value = response.data.filter(u => 
            !props.chatGroup.users.some(member => member.id === u.id)
        );
    } catch (error) {
        console.error('Error fetching users:', error);
    }
};

const addMember = () => {
    addMemberForm.post(route('chat.addMember', props.chatGroup.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddMemberModal.value = false;
            addMemberForm.reset();
        },
        onError: (errors) => {
            console.error('Add member errors:', errors);
        }
    });
};

const removeMember = (member) => {
    if (confirm(`Are you sure you want to remove ${member.name} from this group?`)) {
        const form = useForm({});
        form.delete(route('chat.removeMember', [props.chatGroup.id, member.id]), {
            preserveScroll: true,
            onSuccess: () => {
                // Member removed successfully
            },
            onError: (errors) => {
                console.error('Remove member errors:', errors);
            }
        });
    }
};

const openAddMemberModal = () => {
    fetchAvailableUsers();
    showAddMemberModal.value = true;
};

onMounted(() => {
    scrollToBottom();
});

watch(() => allMessages.value.length, () => {
    scrollToBottom();
});
</script>

<template>
    <Head :title="`${chatGroup.name} - Chat`" />
    
    <DashboardLayout :title="chatGroup.name" :subtitle="`${chatGroup.users_count} members`">
        <div class="max-w-6xl mx-auto">
            <!-- Chat Header -->
            <div class="bg-white/80 backdrop-blur-sm rounded-t-3xl p-6 shadow-xl border border-slate-200/50 border-b-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link :href="route('chat.index')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </Link>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white">
                                <svg v-if="chatGroup.type === 'subject'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            
                            <div>
                                <h1 class="font-bold text-slate-800">{{ chatGroup.name }}</h1>
                                <div class="flex items-center space-x-2 text-sm text-slate-500">
                                    <span>{{ chatGroup.users_count }} members</span>
                                    <span v-if="chatGroup.subject" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                        {{ chatGroup.subject.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Members Button -->
                        <button @click="showMemberModal = true" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-200" title="View Members">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                        
                        <!-- Add Member Button (Admin/Moderator only) -->
                        <button v-if="canManageMembers" @click="openAddMemberModal" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-200" title="Add Member">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </button>
                        
                        <!-- Group Info Button -->
                        <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-200" title="Group Info">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="bg-white/80 backdrop-blur-sm shadow-xl border-x border-slate-200/50 h-[600px] flex flex-col">
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <template v-for="(message, index) in allMessages" :key="message.id">
                        <!-- Date Separator -->
                        <div v-if="shouldShowDateSeparator(message, allMessages[index - 1])" class="flex justify-center py-2">
                            <span class="px-3 py-1 text-xs font-medium text-slate-500 bg-slate-100 rounded-full">
                                {{ formatDate(message.created_at) }}
                            </span>
                        </div>

                        <!-- Message -->
                        <div 
                            :class="[
                                'flex',
                                message.user_id === user.id ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div 
                                :class="[
                                    'max-w-lg flex',
                                    message.user_id === user.id ? 'flex-row-reverse' : 'flex-row',
                                    isConsecutiveMessage(message, allMessages[index - 1]) ? 'mt-1' : 'mt-4'
                                ]"
                            >
                                <!-- Avatar (only show for first message in sequence) -->
                                <div v-if="!isConsecutiveMessage(message, allMessages[index - 1])" class="flex-shrink-0">
                                    <img 
                                        :src="message.user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(message.user.name)}&background=6366f1&color=ffffff`" 
                                        :alt="message.user.name"
                                        :class="[
                                            'w-8 h-8 rounded-full',
                                            message.user_id === user.id ? 'ml-3' : 'mr-3'
                                        ]"
                                    >
                                </div>
                                <div v-else :class="message.user_id === user.id ? 'mr-11' : 'ml-11'"></div>

                                <div class="flex flex-col">
                                    <!-- User name and time (only for first message in sequence) -->
                                    <div 
                                        v-if="!isConsecutiveMessage(message, allMessages[index - 1])" 
                                        :class="[
                                            'flex items-center space-x-2 mb-1',
                                            message.user_id === user.id ? 'justify-end' : 'justify-start'
                                        ]"
                                    >
                                        <span v-if="message.user_id !== user.id" class="text-sm font-semibold text-slate-700">
                                            {{ message.user.name }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            {{ formatTime(message.created_at) }}
                                        </span>
                                    </div>

                                    <!-- Reply Context -->
                                    <div 
                                        v-if="message.reply_to" 
                                        :class="[
                                            'mb-2 p-2 rounded-lg border-l-4 border-slate-300 bg-slate-50 text-sm',
                                            message.user_id === user.id ? 'mr-0' : 'ml-0'
                                        ]"
                                    >
                                        <div class="font-medium text-slate-600">{{ message.reply_to.user.name }}</div>
                                        <div class="text-slate-500">{{ message.reply_to.message }}</div>
                                    </div>

                                    <!-- Message Content -->
                                    <div 
                                        :class="[
                                            'px-4 py-2 rounded-2xl max-w-sm break-words',
                                            message.user_id === user.id 
                                                ? 'bg-indigo-500 text-white rounded-br-md' 
                                                : 'bg-slate-100 text-slate-800 rounded-bl-md'
                                        ]"
                                    >
                                        {{ message.message }}
                                    </div>

                                    <!-- Message Actions -->
                                    <div 
                                        :class="[
                                            'flex items-center space-x-2 mt-1 opacity-0 hover:opacity-100 transition-opacity duration-200',
                                            message.user_id === user.id ? 'justify-end' : 'justify-start'
                                        ]"
                                    >
                                        <button 
                                            @click="replyToMessage(message)"
                                            class="p-1 text-slate-400 hover:text-slate-600 rounded"
                                            title="Reply"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div v-if="allMessages.length === 0" class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-600 mb-2">No messages yet</h3>
                            <p class="text-slate-500">Be the first to start the conversation!</p>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t border-slate-200/50 p-4">
                    <!-- Reply Preview -->
                    <div v-if="replyingTo" class="mb-3 p-3 bg-slate-50 rounded-lg border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-600">Replying to {{ replyingTo.user.name }}</div>
                                <div class="text-sm text-slate-500">{{ replyingTo.message }}</div>
                            </div>
                            <button 
                                @click="cancelReply"
                                class="p-1 text-slate-400 hover:text-slate-600 rounded"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="sendMessage" class="flex items-center space-x-3">
                        <div class="flex-1 relative">
                            <input 
                                id="message-input"
                                v-model="messageInput"
                                type="text" 
                                placeholder="Type your message..." 
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pr-12 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                @keydown.enter="sendMessage"
                            >
                            <button 
                                type="submit"
                                :disabled="!messageInput.trim()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 bg-indigo-500 text-white rounded-xl hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Members Modal -->
        <div v-if="showMemberModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showMemberModal = false"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">Group Members</h3>
                                
                                <div class="max-h-96 overflow-y-auto">
                                    <div v-for="member in chatGroup.users" :key="member.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                                        <div class="flex items-center space-x-3">
                                            <img :src="member.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}&background=6366f1&color=ffffff`" 
                                                 :alt="member.name" class="w-10 h-10 rounded-full">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ member.name }}</div>
                                                <div class="text-sm text-gray-500">{{ member.email }}</div>
                                                <div v-if="member.pivot.role !== 'member'" class="text-xs font-medium text-indigo-600 capitalize">
                                                    {{ member.pivot.role }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div v-if="canManageMembers && member.id !== chatGroup.created_by && member.id !== user.id" class="flex items-center space-x-2">
                                            <button @click="removeMember(member)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="showMemberModal = false" 
                                class="w-full inline-flex justify-center rounded-2xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Member Modal -->
        <div v-if="showAddMemberModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showAddMemberModal = false"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="addMember">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">Add Member to Group</h3>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                                        <select v-model="addMemberForm.user_id" required 
                                                class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Choose a user...</option>
                                            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                                {{ user.name }} ({{ user.email }})
                                            </option>
                                        </select>
                                        <div v-if="addMemberForm.errors.user_id" class="mt-1 text-sm text-red-600">{{ addMemberForm.errors.user_id }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="addMemberForm.processing" 
                                    class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span v-if="addMemberForm.processing">Adding...</span>
                                <span v-else>Add Member</span>
                            </button>
                            <button type="button" @click="showAddMemberModal = false" 
                                    class="mt-3 w-full inline-flex justify-center rounded-2xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.message-bubble:hover .message-actions {
    opacity: 1;
}
</style>