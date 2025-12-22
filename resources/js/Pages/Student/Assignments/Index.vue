<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assignments: Array
});

const filter = ref('all');

const filteredAssignments = computed(() => {
    if (filter.value === 'all') return props.assignments;
    if (filter.value === 'available') return props.assignments.filter(a => !a.my_submission);
    if (filter.value === 'submitted') return props.assignments.filter(a => a.my_submission && !a.my_submission.graded_at);
    if (filter.value === 'graded') return props.assignments.filter(a => a.my_submission?.graded_at);
    return props.assignments;
});

const getStatusBadge = (assignment) => {
    if (assignment.my_submission?.graded_at) {
        return { class: 'bg-green-100 text-green-800', text: 'Graded' };
    }
    if (assignment.my_submission) {
        return { class: 'bg-yellow-100 text-yellow-800', text: 'Submitted' };
    }
    if (isOverdue(assignment.due_date)) {
        return { class: 'bg-red-100 text-red-800', text: 'Overdue' };
    }
    return { class: 'bg-blue-100 text-blue-800', text: 'Available' };
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

const getDaysUntilDue = (dueDate) => {
    const now = new Date();
    const due = new Date(dueDate);
    const diff = Math.ceil((due - now) / (1000 * 60 * 60 * 24));
    if (diff < 0) return 'Overdue';
    if (diff === 0) return 'Due today';
    if (diff === 1) return 'Due tomorrow';
    return `${diff} days left`;
};

const counts = computed(() => ({
    all: props.assignments.length,
    available: props.assignments.filter(a => !a.my_submission).length,
    submitted: props.assignments.filter(a => a.my_submission && !a.my_submission.graded_at).length,
    graded: props.assignments.filter(a => a.my_submission?.graded_at).length
}));
</script>

<template>
    <Head title="My Assignments" />

    <DashboardLayout title="My Assignments" subtitle="View and submit your assignments">
        <!-- Filter Tabs -->
        <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 p-2">
            <div class="flex gap-2">
                <button @click="filter = 'all'"
                        :class="['flex-1 px-4 py-2 rounded-lg transition-colors', filter === 'all' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100']">
                    All ({{ counts.all }})
                </button>
                <button @click="filter = 'available'"
                        :class="['flex-1 px-4 py-2 rounded-lg transition-colors', filter === 'available' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100']">
                    Available ({{ counts.available }})
                </button>
                <button @click="filter = 'submitted'"
                        :class="['flex-1 px-4 py-2 rounded-lg transition-colors', filter === 'submitted' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100']">
                    Submitted ({{ counts.submitted }})
                </button>
                <button @click="filter = 'graded'"
                        :class="['flex-1 px-4 py-2 rounded-lg transition-colors', filter === 'graded' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100']">
                    Graded ({{ counts.graded }})
                </button>
            </div>
        </div>

        <!-- Assignments List -->
        <div class="grid grid-cols-1 gap-6">
            <div v-for="assignment in filteredAssignments" :key="assignment.id"
                 class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <Link :href="route('student.assignments.show', assignment.id)"
                                  class="text-xl font-semibold text-slate-900 hover:text-indigo-600">
                                {{ assignment.title }}
                            </Link>
                            <span :class="['px-3 py-1 text-xs font-medium rounded-full', getStatusBadge(assignment).class]">
                                {{ getStatusBadge(assignment).text }}
                            </span>
                        </div>

                        <p class="text-sm text-slate-600 mb-3 line-clamp-2">{{ assignment.description }}</p>

                        <div class="flex items-center gap-6 text-sm text-slate-500 mb-3">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ assignment.subject?.name }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ assignment.teacher?.name }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ formatDate(assignment.due_date) }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                {{ assignment.total_points }} points
                            </div>
                        </div>

                        <!-- Submission Status -->
                        <div v-if="assignment.my_submission" class="mt-4 p-3 bg-slate-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Your Submission</p>
                                    <p class="text-xs text-slate-500">Submitted on {{ formatDate(assignment.my_submission.submitted_at) }}</p>
                                </div>
                                <div v-if="assignment.my_submission.graded_at" class="text-right">
                                    <p class="text-lg font-bold text-green-600">{{ assignment.my_submission.score }}/{{ assignment.total_points }}</p>
                                    <p class="text-xs text-slate-500">{{ ((assignment.my_submission.score / assignment.total_points) * 100).toFixed(1) }}%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Due Date Warning -->
                        <div v-else-if="!isOverdue(assignment.due_date)" class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm font-medium text-blue-800">{{ getDaysUntilDue(assignment.due_date) }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="ml-4 flex flex-col gap-2">
                        <Link :href="route('student.assignments.show', assignment.id)"
                              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm text-center">
                            {{ assignment.my_submission ? 'View' : 'Start' }}
                        </Link>
                        <Link v-if="assignment.my_submission"
                              :href="route('student.assignments.viewSubmission', assignment.id)"
                              class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors text-sm text-center">
                            My Work
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredAssignments.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No assignments found</h3>
                <p class="text-slate-600">{{ filter === 'all' ? 'Your teachers haven\'t assigned any work yet.' : `No ${filter} assignments.` }}</p>
            </div>
        </div>
    </DashboardLayout>
</template>
