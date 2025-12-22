<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    chatGroups: Array,
    subjects: Array,
    users: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const searchQuery = ref('');
const showCreateModal = ref(false);

const form = useForm({
    name: '',
    description: '',
    type: 'general',
    subject_id: '',
    max_members: 50,
    is_moderated: false,
    members: []
});

const filteredChatGroups = computed(() => {
    if (!searchQuery.value) return props.chatGroups;
    
    return props.chatGroups.filter(group => 
        group.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (group.subject && group.subject.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const formatTime = (date) => {
    if (!date) return '';
    const now = new Date();
    const messageDate = new Date(date);
    const diffMs = now - messageDate;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        return messageDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return messageDate.toLocaleDateString('en-US', { weekday: 'short' });
    } else {
        return messageDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
};

const truncateMessage = (message, length = 50) => {
    if (!message) return '';
    return message.length > length ? message.substring(0, length) + '...' : message;
};

const createGroup = () => {
    form.post(route('chat.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        }
    });
};

const toggleMember = (userId) => {
    if (form.members.includes(userId)) {
        form.members = form.members.filter(id => id !== userId);
    } else {
        form.members.push(userId);
    }
};
</script>

<template>
    <Head title="Chat Groups" />
    
    <DashboardLayout title="Chat & Community" subtitle="Connect with your classmates and teachers">
        <div class="space-y-6">
            <!-- Navigation Tabs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-2 shadow-xl border border-slate-200/50">
                <div class="flex space-x-1">
                    <Link :href="route('chat.index')" 
                          class="flex-1 text-center py-3 px-4 rounded-2xl font-semibold transition-all duration-200 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                            </svg>
                            <span>Chat Groups</span>
                        </div>
                    </Link>
                    <Link :href="route('community.index')" 
                          class="flex-1 text-center py-3 px-4 rounded-2xl font-semibold transition-all duration-200 text-slate-600 hover:text-slate-800 hover:bg-slate-100">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <span>Community Feed</span>
                        </div>
                    </Link>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Search chat groups..." 
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            >
                            <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-slate-600">
                            {{ filteredChatGroups.length }} group{{ filteredChatGroups.length !== 1 ? 's' : '' }} available
                        </div>
                        <button 
                            @click="showCreateModal = true"
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Create Group</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chat Groups List -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Your Chat Groups</h2>
                </div>

                <div v-if="filteredChatGroups.length > 0" class="divide-y divide-slate-200/50">
                    <Link 
                        v-for="group in filteredChatGroups" 
                        :key="group.id"
                        :href="route('chat.show', group.id)"
                        class="block p-6 hover:bg-slate-50/50 transition-colors duration-200 group"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Group Icon -->
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white flex-shrink-0">
                                    <svg v-if="group.type === 'subject'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                                    </svg>
                                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors duration-200 truncate">
                                            {{ group.name }}
                                        </h3>
                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                            <!-- Unread Badge -->
                                            <span 
                                                v-if="group.unread_count > 0" 
                                                class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[20px]"
                                            >
                                                {{ group.unread_count > 99 ? '99+' : group.unread_count }}
                                            </span>
                                            <!-- Last Activity Time -->
                                            <span v-if="group.last_activity_at" class="text-xs text-slate-500">
                                                {{ formatTime(group.last_activity_at) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <!-- Subject Badge -->
                                            <div v-if="group.subject" class="mb-1">
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                                    {{ group.subject.name }}
                                                </span>
                                            </div>
                                            
                                            <!-- Last Message -->
                                            <p v-if="group.last_message" class="text-sm text-slate-600 truncate">
                                                <span class="font-medium">{{ group.last_message.user.name }}:</span>
                                                {{ truncateMessage(group.last_message.message) }}
                                            </p>
                                            <p v-else class="text-sm text-slate-400 italic">No messages yet</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Member Count and Arrow -->
                            <div class="flex items-center space-x-3 text-slate-400 group-hover:text-slate-600 transition-colors duration-200">
                                <div class="flex items-center space-x-1 text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6-12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ group.users_count }}</span>
                                </div>
                                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No chat groups found</h3>
                    <p class="text-slate-500 mb-6">
                        {{ searchQuery ? 'No groups match your search.' : 'You haven\'t joined any chat groups yet.' }}
                    </p>
                    <Link 
                        :href="route('subjects.index')" 
                        class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    >
                        Browse Subjects
                    </Link>
                </div>
            </div>
        </div>

        <!-- Create Group Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showCreateModal = false"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="createGroup">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">Create New Chat Group</h3>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Group Name*</label>
                                            <input v-model="form.name" type="text" required 
                                                   class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500" 
                                                   placeholder="Enter group name">
                                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                            <textarea v-model="form.description" rows="3" 
                                                      class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500" 
                                                      placeholder="Optional description"></textarea>
                                            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Type*</label>
                                                <select v-model="form.type" required 
                                                        class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="general">General</option>
                                                    <option value="subject">Subject-based</option>
                                                </select>
                                            </div>

                                            <div v-if="form.type === 'subject'">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                                <select v-model="form.subject_id" 
                                                        class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">Select Subject</option>
                                                    <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                                        {{ subject.name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div v-else>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Members</label>
                                                <input v-model.number="form.max_members" type="number" min="2" max="500" 
                                                       class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                        </div>

                                        <div class="flex items-center">
                                            <input v-model="form.is_moderated" type="checkbox" id="moderated" 
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <label for="moderated" class="ml-2 block text-sm text-gray-700">
                                                Enable moderation
                                            </label>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Add Members (Optional)</label>
                                            <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-2xl p-3">
                                                <div v-for="member in users" :key="member.id" class="flex items-center space-x-2 py-1">
                                                    <input :id="`member-${member.id}`" type="checkbox" :value="member.id" 
                                                           @change="toggleMember(member.id)"
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300">
                                                    <label :for="`member-${member.id}`" class="text-sm text-gray-700">
                                                        {{ member.name }} ({{ member.email }})
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing" 
                                    class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Group</span>
                            </button>
                            <button type="button" @click="showCreateModal = false" 
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