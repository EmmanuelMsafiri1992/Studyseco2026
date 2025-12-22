<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    resource: Object,
});

const user = props.auth?.user || { role: 'guest' };

const getTypeIcon = (type) => {
    const icons = {
        book: '📚',
        past_paper: '📄',
        document: '📋',
        video: '🎥',
        audio: '🎵',
        presentation: '📊'
    };
    return icons[type] || '📁';
};

const getTypeColor = (type) => {
    const colors = {
        book: 'from-blue-500 to-indigo-600',
        past_paper: 'from-green-500 to-emerald-600',
        document: 'from-purple-500 to-violet-600',
        video: 'from-red-500 to-rose-600',
        audio: 'from-yellow-500 to-orange-600',
        presentation: 'from-pink-500 to-purple-600'
    };
    return colors[type] || colors.document;
};

const formatFileSize = (bytes) => {
    if (!bytes) return 'Unknown';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`View Resource: ${resource.title}`" />
    
    <DashboardLayout :title="resource.title" subtitle="Resource Details">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Back Button -->
            <Link
                :href="route('admin.library.index')"
                class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Library Management
            </Link>

            <!-- Resource Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div :class="['p-6 bg-gradient-to-r text-white', getTypeColor(resource.type)]">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <span class="text-3xl">{{ getTypeIcon(resource.type) }}</span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold mb-2">{{ resource.title }}</h1>
                                <div class="flex items-center space-x-4 text-sm text-white/80">
                                    <span class="px-3 py-1 bg-white/20 rounded-full">
                                        {{ resource.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </span>
                                    <span v-if="resource.subject">📚 {{ resource.subject.name }}</span>
                                    <span v-if="resource.grade_level">🎓 Grade {{ resource.grade_level }}</span>
                                    <span v-if="resource.year">📅 {{ resource.year }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <Link
                                :href="route('admin.library.edit', resource.id)"
                                class="px-4 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors flex items-center space-x-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </Link>
                            
                            <span :class="[
                                'px-4 py-2 rounded-lg font-medium',
                                resource.is_active 
                                    ? 'bg-green-100/90 text-green-800' 
                                    : 'bg-red-100/90 text-red-800'
                            ]">
                                {{ resource.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource Details -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Description -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Description</h3>
                        <p v-if="resource.description" class="text-slate-600 leading-relaxed">
                            {{ resource.description }}
                        </p>
                        <p v-else class="text-slate-400 italic">No description available</p>
                    </div>

                    <!-- File Information -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">File Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Type</label>
                                <div class="text-slate-900">{{ resource.file_type || 'Unknown' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Size</label>
                                <div class="text-slate-900">{{ formatFileSize(resource.file_size) }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Path</label>
                                <div class="text-slate-900 text-sm font-mono bg-slate-50 px-2 py-1 rounded">
                                    {{ resource.file_path || 'No file' }}
                                </div>
                            </div>
                            <div v-if="resource.thumbnail">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Thumbnail</label>
                                <div class="text-slate-900">Available</div>
                            </div>
                        </div>
                    </div>

                    <!-- Past Paper Specific Info -->
                    <div v-if="resource.type === 'past_paper'" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Examination Details</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-if="resource.exam_board">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Exam Board</label>
                                <div class="text-slate-900">{{ resource.exam_board }}</div>
                            </div>
                            <div v-if="resource.year">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Year</label>
                                <div class="text-slate-900">{{ resource.year }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Statistics -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Views</span>
                                <span class="font-semibold text-slate-900">{{ resource.view_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Download Attempts</span>
                                <span class="font-semibold text-slate-900">{{ resource.download_attempts || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Created</span>
                                <span class="font-semibold text-slate-900">{{ formatDate(resource.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Updated</span>
                                <span class="font-semibold text-slate-900">{{ formatDate(resource.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Category and Tags -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Classification</h3>
                        <div class="space-y-3">
                            <div v-if="resource.category">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ resource.category }}
                                </span>
                            </div>
                            
                            <div v-if="resource.subject">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ resource.subject.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Protection Settings -->
                    <div v-if="resource.is_protected" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Protected Resource
                        </h3>
                        <div class="text-sm text-slate-600">
                            This resource has protection features enabled.
                        </div>
                    </div>

                    <!-- Access Permissions -->
                    <div v-if="resource.access_permissions" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Access Control</h3>
                        <div class="text-sm text-slate-600">
                            Custom access permissions are configured for this resource.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>