<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    roles: Object,
    permissionCategories: Array,
    stats: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Filter state
const searchQuery = ref('');
const selectedStatus = ref('all');

// Filtered roles
const filteredRoles = computed(() => {
    let filtered = props.roles.data || [];
    
    if (searchQuery.value) {
        filtered = filtered.filter(role => 
            role.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            role.slug.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    
    if (selectedStatus.value !== 'all') {
        const isActive = selectedStatus.value === 'active';
        filtered = filtered.filter(role => role.is_active === isActive);
    }
    
    return filtered;
});

// Utility functions
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadgeClass = (isActive) => {
    return isActive 
        ? 'bg-green-100 text-green-700'
        : 'bg-red-100 text-red-700';
};

const getPriorityBadgeClass = (priority) => {
    if (priority >= 80) return 'bg-purple-100 text-purple-700';
    if (priority >= 60) return 'bg-blue-100 text-blue-700';
    if (priority >= 40) return 'bg-yellow-100 text-yellow-700';
    return 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Role Management" />

    <DashboardLayout 
        title="Role Management" 
        subtitle="Manage user roles and permissions for your system"
        :stats="stats">

        <!-- Search and Filter Bar -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input v-model="searchQuery" type="text" placeholder="Search roles..." class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                        <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <select v-model="selectedStatus" class="bg-white/90 backdrop-blur-sm border border-slate-200 px-4 py-3 pr-12 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 appearance-none min-w-[140px] cursor-pointer shadow-sm hover:shadow-md transition-all duration-200">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    <Link :href="route('admin.roles.create')" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Create Role</span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
            <div class="p-6 border-b border-slate-200/50">
                <h2 class="text-xl font-bold text-slate-800">System Roles</h2>
                <p class="text-sm text-slate-500 mt-1">{{ filteredRoles.length }} roles found</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Role</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Description</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Priority</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Users</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Permissions</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/50">
                        <tr v-for="role in filteredRoles" :key="role.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {{ role.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ role.name }}</p>
                                        <p class="text-sm text-slate-500">{{ role.slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">
                                {{ role.description || 'No description provided' }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getPriorityBadgeClass(role.priority)" class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ role.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    <span>{{ role.users_count || 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <span>{{ role.permissions?.length || 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getStatusBadgeClass(role.is_active)" class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ role.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('admin.roles.show', role.id)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</Link>
                                    <Link :href="route('admin.roles.edit', role.id)" class="text-amber-600 hover:text-amber-800 text-sm font-medium">Edit</Link>
                                    <button v-if="!['super-admin', 'admin', 'teacher', 'student'].includes(role.slug)" 
                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredRoles.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                                    </svg>
                                    <p>No roles found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="roles?.links" class="px-6 py-4 border-t border-slate-200/50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">
                        Showing {{ roles.from || 0 }} to {{ roles.to || 0 }} of {{ roles.total || 0 }} results
                    </p>
                    <div class="flex items-center space-x-2">
                        <template v-for="link in roles.links" :key="link.label">
                            <Link v-if="link.url" 
                                  :href="link.url" 
                                  v-html="link.label"
                                  :class="[
                                    'px-3 py-2 text-sm rounded-lg transition-colors duration-150',
                                    link.active 
                                        ? 'bg-indigo-100 text-indigo-700' 
                                        : 'text-slate-600 hover:bg-slate-100'
                                  ]">
                            </Link>
                            <span v-else 
                                  v-html="link.label"
                                  class="px-3 py-2 text-sm rounded-lg text-slate-400 cursor-not-allowed">
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>