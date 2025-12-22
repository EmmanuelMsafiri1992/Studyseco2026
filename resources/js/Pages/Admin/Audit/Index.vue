<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auditLogs: Object,
    stats: Object,
    eventTypes: Array,
    filters: Object
});

const filters = ref(props.filters || {});

const applyFilters = () => {
    router.get(route('admin.audit.index'), filters.value, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    filters.value = {};
    router.get(route('admin.audit.index'));
};

const getEventIcon = (event) => {
    const icons = {
        'created': '➕',
        'updated': '✏️',
        'deleted': '🗑️',
        'login': '🔑',
        'logout': '🚪',
        'payment': '💰',
        'enrollment': '📝',
        'access_granted': '✅',
        'access_revoked': '❌',
        'tutor_assigned': '👨‍🏫',
        'tutor_unassigned': '👨‍🏫'
    };
    return icons[event] || '📋';
};

const getEventColor = (event) => {
    const colors = {
        'created': 'green',
        'updated': 'blue',
        'deleted': 'red',
        'login': 'emerald',
        'logout': 'slate',
        'payment': 'yellow',
        'enrollment': 'indigo',
        'access_granted': 'green',
        'access_revoked': 'red',
        'tutor_assigned': 'purple',
        'tutor_unassigned': 'orange'
    };
    return colors[event] || 'gray';
};
</script>

<template>
    <Head title="Audit Logs" />
    
    <DashboardLayout title="Audit Trail" subtitle="System security and activity logs">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Events</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total_events?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Unique Users</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.unique_users?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Events Today</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.events_today?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Critical Events</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.critical_events?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Event Type</label>
                    <select v-model="filters.event_type" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option v-for="type in eventTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date From</label>
                    <input v-model="filters.date_from" type="date" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date To</label>
                    <input v-model="filters.date_to" type="date" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Search</label>
                    <input v-model="filters.search" type="text" placeholder="Search events..." class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center space-x-4 mt-4">
                <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Apply Filters
                </button>
                <button @click="clearFilters" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Recent Activity</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="log in auditLogs.data" :key="log.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">{{ getEventIcon(log.event) }}</span>
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getEventColor(log.event) === 'green' ? 'bg-green-100 text-green-800' :
                                        getEventColor(log.event) === 'red' ? 'bg-red-100 text-red-800' :
                                        getEventColor(log.event) === 'blue' ? 'bg-blue-100 text-blue-800' :
                                        getEventColor(log.event) === 'yellow' ? 'bg-yellow-100 text-yellow-800' :
                                        getEventColor(log.event) === 'purple' ? 'bg-purple-100 text-purple-800' :
                                        'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ log.event }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="log.user">
                                    <div class="text-sm font-medium text-slate-900">{{ log.user.name }}</div>
                                    <div class="text-sm text-slate-500">{{ log.user.email }}</div>
                                </div>
                                <div v-else class="text-sm text-slate-500">System</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-900">{{ log.event }} event by {{ log.user?.name || 'System' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ log.ip_address || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('admin.audit.show', log.id)" class="text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        Showing {{ auditLogs.from || 0 }} to {{ auditLogs.to || 0 }} of {{ auditLogs.total || 0 }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link v-if="auditLogs.prev_page_url" :href="auditLogs.prev_page_url" 
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                            Previous
                        </Link>
                        <Link v-if="auditLogs.next_page_url" :href="auditLogs.next_page_url"
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>