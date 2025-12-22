<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assignment: Object,
    submissions: Object,
    stats: Object
});

const getStatusBadgeClass = (status) => {
    const classes = {
        'published': 'bg-green-100 text-green-800',
        'draft': 'bg-gray-100 text-gray-800',
        'closed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getSubmissionStatusBadge = (submission) => {
    if (submission.graded_at) {
        return { class: 'bg-green-100 text-green-800', text: 'Graded' };
    }
    return { class: 'bg-yellow-100 text-yellow-800', text: 'Pending' };
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
    <Head :title="assignment.title" />

    <DashboardLayout :title="assignment.title" subtitle="Assignment Details">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('teacher.assignments.index')"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Assignments
            </Link>
        </div>

        <!-- Assignment Details Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-4">
                        <h2 class="text-2xl font-bold text-slate-900">{{ assignment.title }}</h2>
                        <span :class="['px-3 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(assignment.status)]">
                            {{ assignment.status }}
                        </span>
                        <span v-if="isOverdue(assignment.due_date) && assignment.status === 'published'"
                              class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                            Overdue
                        </span>
                    </div>
                    <p class="text-slate-700 mb-4">{{ assignment.description }}</p>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <Link :href="route('teacher.assignments.edit', assignment.id)"
                          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </Link>
                </div>
            </div>

            <!-- Assignment Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Subject</p>
                    <p class="text-base font-medium text-slate-900">{{ assignment.subject?.name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Total Points</p>
                    <p class="text-base font-medium text-slate-900">{{ assignment.total_points }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Due Date</p>
                    <p class="text-base font-medium text-slate-900">{{ formatDate(assignment.due_date) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Late Submissions</p>
                    <p class="text-base font-medium text-slate-900">{{ assignment.allow_late_submissions ? 'Allowed' : 'Not Allowed' }}</p>
                </div>
            </div>

            <!-- Instructions -->
            <div v-if="assignment.instructions" class="mb-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Instructions</h3>
                <div class="p-4 bg-slate-50 rounded-lg">
                    <p class="text-slate-700 whitespace-pre-wrap">{{ assignment.instructions }}</p>
                </div>
            </div>

            <!-- Attachment -->
            <div v-if="assignment.attachment_url" class="mb-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Attachment</h3>
                <a :href="assignment.attachment_url" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Assignment File
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Total Submissions</p>
                        <p class="text-3xl font-bold text-slate-900">{{ stats?.total_submissions || 0 }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Pending Grading</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ stats?.pending_grading || 0 }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Graded</p>
                        <p class="text-3xl font-bold text-green-600">{{ stats?.graded || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Average Score</p>
                        <p class="text-3xl font-bold text-slate-900">{{ stats?.average_score || '0' }}%</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-slate-900">Recent Submissions</h3>
                <Link :href="route('teacher.assignments.submissions', assignment.id)"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                    View All Submissions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </Link>
            </div>

            <!-- Submissions Table -->
            <div v-if="submissions && submissions.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Student</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Submitted At</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Score</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="submission in submissions" :key="submission.id" class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-700">{{ submission.student?.name?.charAt(0) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-slate-900">{{ submission.student?.name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-slate-600">{{ formatDate(submission.submitted_at) }}</td>
                            <td class="py-3 px-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getSubmissionStatusBadge(submission).class]">
                                    {{ getSubmissionStatusBadge(submission).text }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm font-medium text-slate-900">
                                {{ submission.score !== null ? `${submission.score}/${assignment.total_points}` : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <Link :href="route('teacher.assignments.submissions', assignment.id)"
                                      class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    {{ submission.graded_at ? 'View' : 'Grade' }}
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No submissions yet</h3>
                <p class="text-slate-600">Students haven't submitted their work for this assignment.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
