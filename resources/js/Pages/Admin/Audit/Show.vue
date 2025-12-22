<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auditLog: Object
});

const formatJson = (obj) => {
    if (!obj) return 'N/A';
    return JSON.stringify(obj, null, 2);
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
    <Head title="Audit Log Details" />
    
    <DashboardLayout title="Audit Log Details" subtitle="Detailed view of system activity">
        
        <!-- Back Button -->
        <div class="mb-6">
            <Link :href="route('admin.audit.index')" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Audit Logs
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center mb-6">
                        <span class="text-2xl mr-3">{{ getEventIcon(auditLog.event) }}</span>
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ auditLog.event }}</h2>
                            <p class="text-slate-600">{{ auditLog.event }} event by {{ auditLog.user?.name || 'System' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-slate-700 mb-2">Event Type</h3>
                            <span :class="[
                                'px-3 py-1 text-sm font-medium rounded-full',
                                getEventColor(auditLog.event) === 'green' ? 'bg-green-100 text-green-800' :
                                getEventColor(auditLog.event) === 'red' ? 'bg-red-100 text-red-800' :
                                getEventColor(auditLog.event) === 'blue' ? 'bg-blue-100 text-blue-800' :
                                getEventColor(auditLog.event) === 'yellow' ? 'bg-yellow-100 text-yellow-800' :
                                getEventColor(auditLog.event) === 'purple' ? 'bg-purple-100 text-purple-800' :
                                'bg-gray-100 text-gray-800'
                            ]">
                                {{ auditLog.event }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-slate-700 mb-2">Timestamp</h3>
                            <p class="text-slate-900">{{ new Date(auditLog.created_at).toLocaleString() }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-slate-700 mb-2">IP Address</h3>
                            <p class="text-slate-900">{{ auditLog.ip_address || 'N/A' }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-slate-700 mb-2">URL</h3>
                            <p class="text-slate-900 break-all">{{ auditLog.url || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- User Agent -->
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-slate-700 mb-2">User Agent</h3>
                        <p class="text-slate-900 text-sm bg-slate-50 p-3 rounded-lg break-all">{{ auditLog.user_agent || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Data Changes -->
                <div v-if="auditLog.old_values || auditLog.new_values" class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Data Changes</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="auditLog.old_values">
                            <h4 class="text-sm font-medium text-slate-700 mb-2">Old Values</h4>
                            <pre class="text-sm bg-red-50 border border-red-200 rounded-lg p-3 overflow-x-auto"><code>{{ formatJson(auditLog.old_values) }}</code></pre>
                        </div>

                        <div v-if="auditLog.new_values">
                            <h4 class="text-sm font-medium text-slate-700 mb-2">New Values</h4>
                            <pre class="text-sm bg-green-50 border border-green-200 rounded-lg p-3 overflow-x-auto"><code>{{ formatJson(auditLog.new_values) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- User Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">User Information</h3>
                    
                    <div v-if="auditLog.user" class="space-y-3">
                        <div>
                            <h4 class="text-sm font-medium text-slate-700">Name</h4>
                            <p class="text-slate-900">{{ auditLog.user.name }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-slate-700">Email</h4>
                            <p class="text-slate-900">{{ auditLog.user.email }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-slate-700">Role</h4>
                            <span :class="[
                                'px-2 py-1 text-xs font-medium rounded-full',
                                auditLog.user.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                                auditLog.user.role === 'teacher' ? 'bg-blue-100 text-blue-800' :
                                auditLog.user.role === 'student' ? 'bg-green-100 text-green-800' :
                                'bg-gray-100 text-gray-800'
                            ]">
                                {{ auditLog.user.role }}
                            </span>
                        </div>
                    </div>
                    
                    <div v-else>
                        <p class="text-slate-500">System Event</p>
                    </div>
                </div>

                <!-- Affected Resource -->
                <div v-if="auditLog.model_type" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Affected Resource</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <h4 class="text-sm font-medium text-slate-700">Resource Type</h4>
                            <p class="text-slate-900">{{ auditLog.model_type?.split('\\').pop() }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-slate-700">Resource ID</h4>
                            <p class="text-slate-900">{{ auditLog.model_id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>