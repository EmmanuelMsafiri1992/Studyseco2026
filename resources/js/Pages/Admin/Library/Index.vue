<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    resources: Object,
    subjects: Array,
    types: Array,
    categories: Array,
    grades: Array,
    years: Array,
    stats: Object,
    filters: Object,
});

// Filter states
const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');
const selectedSubject = ref(props.filters.subject || '');
const selectedStatus = ref(props.filters.status || '');

// Selection states
const selectedResources = ref([]);
const showBulkActions = ref(false);
const showBulkUpload = ref(false);

// Computed
const allSelected = computed(() => {
    return props.resources.data.length > 0 && selectedResources.value.length === props.resources.data.length;
});

const someSelected = computed(() => {
    return selectedResources.value.length > 0 && selectedResources.value.length < props.resources.data.length;
});

// Functions
const applyFilters = () => {
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedType.value) params.type = selectedType.value;
    if (selectedSubject.value) params.subject = selectedSubject.value;
    if (selectedStatus.value) params.status = selectedStatus.value;
    
    router.get(route('admin.library.index'), params, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedType.value = '';
    selectedSubject.value = '';
    selectedStatus.value = '';
    router.get(route('admin.library.index'));
};

const toggleAll = () => {
    if (allSelected.value) {
        selectedResources.value = [];
    } else {
        selectedResources.value = props.resources.data.map(resource => resource.id);
    }
};

const toggleResource = (resourceId) => {
    const index = selectedResources.value.indexOf(resourceId);
    if (index > -1) {
        selectedResources.value.splice(index, 1);
    } else {
        selectedResources.value.push(resourceId);
    }
};

const performBulkAction = (action) => {
    if (selectedResources.value.length === 0) {
        alert('Please select resources first.');
        return;
    }

    if (action === 'delete' && !confirm('Are you sure you want to delete selected resources?')) {
        return;
    }

    router.patch(route('admin.library.bulk-action'), {
        action: action,
        resource_ids: selectedResources.value
    }, {
        onSuccess: () => {
            selectedResources.value = [];
            showBulkActions.value = false;
        }
    });
};

const toggleStatus = (resource) => {
    router.patch(route('admin.library.toggle', resource.id), {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const deleteResource = (resource) => {
    if (confirm('Are you sure you want to delete this resource?')) {
        router.delete(route('admin.library.destroy', resource.id));
    }
};

const getFileIcon = (type) => {
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

const formatFileSize = (bytes) => {
    if (!bytes) return 'Unknown';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Library Management" />
    
    <DashboardLayout title="Library Management" subtitle="Manage books, papers, and educational resources">
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100">
                            <span class="text-2xl">📚</span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-slate-900">{{ stats.total_resources }}</h3>
                            <p class="text-sm text-slate-600">Total Resources</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-slate-900">{{ stats.active_resources }}</h3>
                            <p class="text-sm text-slate-600">Active Resources</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100">
                            <span class="text-2xl">📖</span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-slate-900">{{ stats.total_books }}</h3>
                            <p class="text-sm text-slate-600">Books</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-orange-100">
                            <span class="text-2xl">👁️</span>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-slate-900">{{ stats.total_views }}</h3>
                            <p class="text-sm text-slate-600">Total Views</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-col sm:flex-row gap-4 flex-1">
                        <!-- Search -->
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                @input="applyFilters"
                                type="text"
                                placeholder="Search resources..."
                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Filters -->
                        <select
                            v-model="selectedType"
                            @change="applyFilters"
                            class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Types</option>
                            <option v-for="type in types" :key="type" :value="type">
                                {{ type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                            </option>
                        </select>

                        <select
                            v-model="selectedSubject"
                            @change="applyFilters"
                            class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Subjects</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>

                        <select
                            v-model="selectedStatus"
                            @change="applyFilters"
                            class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>

                        <button
                            @click="resetFilters"
                            class="px-4 py-2 text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors"
                        >
                            Reset
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button
                            v-if="selectedResources.length > 0"
                            @click="showBulkActions = !showBulkActions"
                            class="px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors"
                        >
                            Bulk Actions ({{ selectedResources.length }})
                        </button>
                        
                        <button
                            @click="showBulkUpload = true"
                            class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors"
                        >
                            Bulk Upload
                        </button>
                        
                        <Link
                            :href="route('admin.library.create')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                        >
                            Add Resource
                        </Link>
                    </div>
                </div>

                <!-- Bulk Actions Panel -->
                <div v-if="showBulkActions && selectedResources.length > 0" class="mt-4 p-4 bg-slate-50 rounded-xl">
                    <div class="flex gap-3">
                        <button
                            @click="performBulkAction('activate')"
                            class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                        >
                            Activate
                        </button>
                        <button
                            @click="performBulkAction('deactivate')"
                            class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm"
                        >
                            Deactivate
                        </button>
                        <button
                            @click="performBulkAction('delete')"
                            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resources Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        :indeterminate="someSelected"
                                        @change="toggleAll"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Resource</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Type</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Subject</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Size</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Views</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-for="resource in resources.data" :key="resource.id" class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="resource.id"
                                        @change="toggleResource(resource.id)"
                                        :checked="selectedResources.includes(resource.id)"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-2xl">{{ getFileIcon(resource.type) }}</span>
                                        <div>
                                            <div class="font-medium text-slate-900">{{ resource.title }}</div>
                                            <div v-if="resource.description" class="text-sm text-slate-500 truncate max-w-xs">
                                                {{ resource.description }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ resource.type.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900">
                                    {{ resource.subject?.name || 'No subject' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ formatFileSize(resource.file_size) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ resource.view_count || 0 }}
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStatus(resource)"
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            resource.is_active 
                                                ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                                : 'bg-red-100 text-red-800 hover:bg-red-200'
                                        ]"
                                    >
                                        {{ resource.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <Link
                                            :href="route('admin.library.show', resource.id)"
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="route('admin.library.edit', resource.id)"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteResource(resource)"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="resources.links" class="px-6 py-4 border-t border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-500">
                            Showing {{ resources.from }} to {{ resources.to }} of {{ resources.total }} results
                        </div>
                        <div class="flex space-x-2">
                            <template v-for="link in resources.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg border',
                                        link.active 
                                            ? 'bg-blue-600 text-white border-blue-600' 
                                            : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-2 text-sm rounded-lg border bg-slate-100 text-slate-400 border-slate-300 cursor-not-allowed"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>