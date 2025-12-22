<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    role: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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

// Group permissions by category
const groupedPermissions = props.role.permissions?.reduce((groups, permission) => {
    const category = permission.category || 'Other';
    if (!groups[category]) {
        groups[category] = [];
    }
    groups[category].push(permission);
    return groups;
}, {}) || {};

const toggleStatus = () => {
    router.post(route('admin.roles.toggle', props.role.id));
};

const deleteRole = () => {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        router.delete(route('admin.roles.destroy', props.role.id));
    }
};
</script>

<template>
    <Head :title="`Role: ${role.name}`" />

    <DashboardLayout 
        :title="`Role: ${role.name}`" 
        subtitle="View role details and manage permissions">

        <div class="max-w-6xl mx-auto">
            <!-- Back Button and Actions -->
            <div class="flex items-center justify-between mb-6">
                <Link :href="route('admin.roles.index')" class="inline-flex items-center space-x-2 text-slate-600 hover:text-slate-800 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Roles</span>
                </Link>

                <div class="flex items-center space-x-3">
                    <button 
                        @click="toggleStatus"
                        :class="[
                            'px-4 py-2 rounded-2xl font-medium transition-colors duration-200',
                            role.is_active 
                                ? 'bg-red-100 text-red-700 hover:bg-red-200' 
                                : 'bg-green-100 text-green-700 hover:bg-green-200'
                        ]">
                        {{ role.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <Link :href="route('admin.roles.edit', role.id)" class="bg-amber-100 text-amber-700 px-4 py-2 rounded-2xl font-medium hover:bg-amber-200 transition-colors duration-200">
                        Edit Role
                    </Link>
                    <button 
                        v-if="!['super-admin', 'admin', 'teacher', 'student'].includes(role.slug)"
                        @click="deleteRole"
                        class="bg-red-100 text-red-700 px-4 py-2 rounded-2xl font-medium hover:bg-red-200 transition-colors duration-200">
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Role Information -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Basic Info -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                    {{ role.name.charAt(0) }}
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-800">{{ role.name }}</h1>
                                    <p class="text-slate-500">{{ role.slug }}</p>
                                </div>
                            </div>
                            <span :class="getStatusBadgeClass(role.is_active)" class="px-4 py-2 text-sm font-semibold rounded-full">
                                {{ role.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div v-if="role.description">
                                <h3 class="text-lg font-semibold text-slate-800 mb-2">Description</h3>
                                <p class="text-slate-600">{{ role.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 mb-1">Priority</h4>
                                    <span :class="getPriorityBadgeClass(role.priority)" class="px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ role.priority }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 mb-1">Users Count</h4>
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                        <span class="text-slate-600">{{ role.users?.length || 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200">
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 mb-1">Created</h4>
                                    <p class="text-slate-600 text-sm">{{ formatDate(role.created_at) }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 mb-1">Last Updated</h4>
                                    <p class="text-slate-600 text-sm">{{ formatDate(role.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                        <h2 class="text-xl font-bold text-slate-800 mb-6">Permissions</h2>
                        
                        <div v-if="Object.keys(groupedPermissions).length === 0" class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <p class="text-slate-500">No permissions assigned to this role.</p>
                        </div>

                        <div v-else class="space-y-6">
                            <div v-for="(permissions, category) in groupedPermissions" :key="category" class="border border-slate-200 rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-slate-800 mb-4 capitalize">{{ category }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-3 p-3 bg-slate-50 rounded-xl">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-slate-800">{{ permission.name }}</div>
                                            <div v-if="permission.description" class="text-xs text-slate-500">{{ permission.description }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users with this Role -->
                <div class="space-y-8">
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                        <h2 class="text-xl font-bold text-slate-800 mb-6">Users with this Role</h2>
                        
                        <div v-if="!role.users || role.users.length === 0" class="text-center py-6">
                            <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <p class="text-slate-500 text-sm">No users assigned to this role.</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="user in role.users" :key="user.id" class="flex items-center space-x-3 p-3 bg-slate-50 rounded-xl">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-slate-800">{{ user.name }}</div>
                                    <div class="text-xs text-slate-500">{{ user.email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                        <h2 class="text-xl font-bold text-slate-800 mb-6">Quick Stats</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Total Permissions</span>
                                <span class="font-semibold text-slate-800">{{ role.permissions?.length || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Total Users</span>
                                <span class="font-semibold text-slate-800">{{ role.users?.length || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Priority Level</span>
                                <span :class="getPriorityBadgeClass(role.priority)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ role.priority }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Status</span>
                                <span :class="getStatusBadgeClass(role.is_active)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ role.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>