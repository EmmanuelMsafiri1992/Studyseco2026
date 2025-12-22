<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assignments: Object,
    subjects: Array,
    filters: Object
});

const filters = ref(props.filters || {});

const applyFilters = () => {
    router.get(route('teacher.assignments.index'), filters.value, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    filters.value = {};
    router.get(route('teacher.assignments.index'));
};

const deleteAssignment = (assignment) => {
    if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
        router.delete(route('teacher.assignments.destroy', assignment.id));
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'published': 'bg-green-100 text-green-800',
        'draft': 'bg-gray-100 text-gray-800',
        'closed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isOverdue = (dueDate) => {
    return new Date(dueDate) < new Date();
};
</script>

<template>
    <Head title="My Assignments" />

    <DashboardLayout title="My Assignments" subtitle="Manage and track student assignments">
        <!-- Header Actions -->
        <div class="mb-6 flex justify-between items-center">
            <div class="text-sm text-slate-600">
                Showing {{ assignments.from || 0 }} to {{ assignments.to || 0 }} of {{ assignments.total || 0 }} assignments
            </div>
            <Link :href="route('teacher.assignments.create')"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Assignment
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                    <select v-model="filters.status" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                    <select v-model="filters.subject_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Subjects</option>
                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Search</label>
                    <input v-model="filters.search" type="text" placeholder="Search assignments..."
                           class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Apply
                    </button>
                    <button @click="clearFilters" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Assignments List -->
        <div class="grid grid-cols-1 gap-6">
            <div v-for="assignment in assignments.data" :key="assignment.id"
                 class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <Link :href="route('teacher.assignments.show', assignment.id)"
                                  class="text-xl font-semibold text-slate-900 hover:text-indigo-600">
                                {{ assignment.title }}
                            </Link>
                            <span :class="['px-3 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(assignment.status)]">
                                {{ assignment.status }}
                            </span>
                            <span v-if="isOverdue(assignment.due_date) && assignment.status === 'published'"
                                  class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                Overdue
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mb-3 line-clamp-2">{{ assignment.description }}</p>
                        <div class="flex items-center gap-6 text-sm text-slate-500">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ assignment.subject?.name }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Due: {{ formatDate(assignment.due_date) }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ assignment.submissions?.length || 0 }} submissions
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                {{ assignment.total_points }} points
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <Link :href="route('teacher.assignments.submissions', assignment.id)"
                              class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Submissions">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </Link>
                        <Link :href="route('teacher.assignments.edit', assignment.id)"
                              class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </Link>
                        <button @click="deleteAssignment(assignment)"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!assignments.data || assignments.data.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No assignments found</h3>
                <p class="text-slate-600 mb-4">Get started by creating your first assignment.</p>
                <Link :href="route('teacher.assignments.create')"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Assignment
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="assignments.data && assignments.data.length > 0" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-slate-700">
                Showing {{ assignments.from || 0 }} to {{ assignments.to || 0 }} of {{ assignments.total || 0 }} results
            </div>
            <div class="flex items-center space-x-2">
                <Link v-if="assignments.prev_page_url" :href="assignments.prev_page_url"
                      class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                    Previous
                </Link>
                <Link v-if="assignments.next_page_url" :href="assignments.next_page_url"
                      class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                    Next
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>
