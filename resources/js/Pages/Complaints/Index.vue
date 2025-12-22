<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    stats: Object,
    waitingChats: Array,
    activeChats: Array,
    recentChats: Array,
    waitingPagination: Object,
    activePagination: Object,
    historyPagination: Object,
    isAgent: Boolean,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Real-time updates would go here
const refreshInterval = ref(null);

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getPriorityColor = (priority) => {
    const colors = {
        'low': 'bg-green-100 text-green-700',
        'normal': 'bg-blue-100 text-blue-700',
        'high': 'bg-orange-100 text-orange-700',
        'urgent': 'bg-red-100 text-red-700'
    };
    return colors[priority] || 'bg-gray-100 text-gray-700';
};

const getStatusColor = (status) => {
    const colors = {
        'waiting': 'bg-yellow-100 text-yellow-700',
        'active': 'bg-green-100 text-green-700',
        'closed': 'bg-gray-100 text-gray-700'
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const assignChat = (sessionId) => {
    router.post(route('complaints.assign', sessionId));
};

// Pagination functions
const navigateWaitingPage = (page) => {
    router.get(route('complaints.index'), 
        { waiting_page: page }, 
        { preserveState: true, preserveScroll: true }
    );
};

const navigateActivePage = (page) => {
    router.get(route('complaints.index'), 
        { active_page: page }, 
        { preserveState: true, preserveScroll: true }
    );
};

const navigateHistoryPage = (page) => {
    router.get(route('complaints.index'), 
        { history_page: page }, 
        { preserveState: true, preserveScroll: true }
    );
};

// Auto-refresh for real-time updates
onMounted(() => {
    if (props.isAgent) {
        refreshInterval.value = setInterval(() => {
            router.reload({ only: ['stats', 'waitingChats', 'activeChats'] });
        }, 5000); // Refresh every 5 seconds
    }
});

// Clean up interval on unmount
onUnmounted(() => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
});
</script>

<template>
    <Head title="Support Chat Management" />

    <DashboardLayout 
        title="Support Chat Management" 
        subtitle="Manage live chat support requests and help users in real-time"
        :stats="stats">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Waiting in Queue</p>
                        <p class="text-2xl font-bold text-amber-600">{{ stats?.waiting_chats || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Active Chats</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats?.active_chats || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C3.395 15.1 3 13.558 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">My Active Chats</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ stats?.my_active_chats || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Today</p>
                        <p class="text-2xl font-bold text-purple-600">{{ stats?.total_today || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Waiting Queue -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50">
                <div class="p-6 border-b border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Waiting Queue
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">{{ waitingPagination?.total || 0 }} users waiting for support</p>
                        </div>
                        <!-- Pagination Info -->
                        <div v-if="waitingPagination?.has_pages" class="text-sm text-slate-500">
                            Page {{ waitingPagination.current_page }} of {{ waitingPagination.last_page }}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="waitingChats?.length" class="space-y-4">
                        <div v-for="chat in waitingChats" :key="chat.id" 
                             class="group p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 hover:shadow-lg transition-all duration-300 hover:scale-[1.02]">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <div class="w-8 h-8 bg-gradient-to-r from-amber-400 to-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                            {{ chat.queue_position }}
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ chat.user?.name || chat.user_name || 'Guest' }}</span>
                                        <span :class="getPriorityColor(chat.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ chat.priority }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 ml-10">Waiting since {{ formatTime(chat.created_at) }}</p>
                                </div>
                                <button @click="assignChat(chat.session_id)" 
                                        class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 group-hover:scale-105">
                                    Take Chat
                                </button>
                            </div>
                            <div v-if="chat.messages?.length" class="bg-white/70 rounded-lg p-3 ml-10">
                                <p class="text-sm text-slate-700 italic">"{{ chat.messages[0].message.substring(0, 80) }}{{ chat.messages[0].message.length > 80 ? '...' : '' }}"</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C3.395 15.1 3 13.558 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9z"></path>
                        </svg>
                        <p class="text-slate-500">No users waiting for support</p>
                        <p class="text-xs text-slate-400 mt-1">Great job staying on top of support!</p>
                    </div>

                    <!-- Pagination Controls -->
                    <div v-if="waitingPagination?.has_pages" class="flex items-center justify-between mt-6 pt-4 border-t border-slate-200">
                        <div class="flex space-x-2">
                            <button @click="navigateWaitingPage(waitingPagination.current_page - 1)" 
                                    :disabled="waitingPagination.current_page === 1"
                                    class="px-3 py-2 text-sm bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                ← Prev
                            </button>
                            <button @click="navigateWaitingPage(waitingPagination.current_page + 1)" 
                                    :disabled="!waitingPagination.has_more_pages"
                                    class="px-3 py-2 text-sm bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                Next →
                            </button>
                        </div>
                        <span class="text-sm text-slate-500">
                            Showing 2 per page
                        </span>
                    </div>
                </div>
            </div>

            <!-- My Active Chats -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50">
                <div class="p-6 border-b border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C3.395 15.1 3 13.558 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9z"></path>
                                </svg>
                                My Active Chats
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">{{ activePagination?.total || 0 }} chats you're currently handling</p>
                        </div>
                        <!-- Pagination Info -->
                        <div v-if="activePagination?.has_pages" class="text-sm text-slate-500">
                            Page {{ activePagination.current_page }} of {{ activePagination.last_page }}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="activeChats?.length" class="space-y-4">
                        <Link v-for="chat in activeChats" :key="chat.id" 
                              :href="route('complaints.chat', chat.session_id)"
                              class="block group p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-200 hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:from-green-100 hover:to-emerald-100">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                        <span class="font-semibold text-slate-800">{{ chat.user?.name || chat.user_name || 'Guest' }}</span>
                                        <span :class="getPriorityColor(chat.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ chat.priority }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 ml-5">{{ chat.category || 'General Support' }}</p>
                                    <p class="text-xs text-slate-500 ml-5">Active since {{ formatTime(chat.assigned_at) }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                        Live
                                    </div>
                                    <svg class="w-5 h-5 text-green-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div v-if="chat.messages?.length" class="bg-white/70 rounded-lg p-3 ml-5">
                                <p class="text-sm text-slate-700 italic">"{{ chat.messages[0].message.substring(0, 80) }}{{ chat.messages[0].message.length > 80 ? '...' : '' }}"</p>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C3.395 15.1 3 13.558 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9z"></path>
                        </svg>
                        <p class="text-slate-500">No active chats assigned to you</p>
                        <p class="text-xs text-slate-400 mt-1">Take a chat from the waiting queue to get started</p>
                    </div>

                    <!-- Pagination Controls -->
                    <div v-if="activePagination?.has_pages" class="flex items-center justify-between mt-6 pt-4 border-t border-slate-200">
                        <div class="flex space-x-2">
                            <button @click="navigateActivePage(activePagination.current_page - 1)" 
                                    :disabled="activePagination.current_page === 1"
                                    class="px-3 py-2 text-sm bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                ← Prev
                            </button>
                            <button @click="navigateActivePage(activePagination.current_page + 1)" 
                                    :disabled="!activePagination.has_more_pages"
                                    class="px-3 py-2 text-sm bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                Next →
                            </button>
                        </div>
                        <span class="text-sm text-slate-500">
                            Showing 2 per page
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Chat History -->
        <div class="mt-8 bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50">
            <div class="p-6 border-b border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Recent Chat History
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">{{ historyPagination?.total || 0 }} recently closed support sessions</p>
                    </div>
                    <!-- Pagination Info -->
                    <div v-if="historyPagination?.has_pages" class="text-sm text-slate-500">
                        Page {{ historyPagination.current_page }} of {{ historyPagination.last_page }}
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div v-if="recentChats?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="chat in recentChats" :key="chat.id" 
                         class="group p-4 bg-gradient-to-r from-slate-50 to-gray-50 rounded-2xl border border-slate-200 hover:shadow-md transition-all duration-300 hover:scale-[1.01]">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <div class="w-2 h-2 bg-slate-400 rounded-full"></div>
                                    <span class="font-semibold text-slate-800">{{ chat.user?.name || chat.user_name || 'Guest' }}</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">
                                        Closed
                                    </span>
                                </div>
                                <p class="text-sm text-slate-600 ml-4">{{ chat.category || 'General Support' }}</p>
                                <p class="text-xs text-slate-500 ml-4">{{ formatDate(chat.closed_at) }}</p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <p class="text-xs text-slate-500">{{ chat.agent?.name || 'System' }}</p>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ chat.resolution_summary ? 'Resolved' : 'Closed' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-slate-500">No recent chat history</p>
                    <p class="text-xs text-slate-400 mt-1">Completed chats will appear here</p>
                </div>

                <!-- Pagination Controls -->
                <div v-if="historyPagination?.has_pages" class="flex items-center justify-between mt-6 pt-4 border-t border-slate-200">
                    <div class="flex space-x-2">
                        <button @click="navigateHistoryPage(historyPagination.current_page - 1)" 
                                :disabled="historyPagination.current_page === 1"
                                class="px-3 py-2 text-sm bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            ← Prev
                        </button>
                        <button @click="navigateHistoryPage(historyPagination.current_page + 1)" 
                                :disabled="!historyPagination.has_more_pages"
                                class="px-3 py-2 text-sm bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Next →
                        </button>
                    </div>
                    <span class="text-sm text-slate-500">
                        Showing 2 per page
                    </span>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>