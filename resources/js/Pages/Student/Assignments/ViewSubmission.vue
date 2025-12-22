<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assignment: Object,
    submission: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getScoreColor = (score, total) => {
    const percentage = (score / total) * 100;
    if (percentage >= 90) return 'text-green-600';
    if (percentage >= 70) return 'text-blue-600';
    if (percentage >= 50) return 'text-yellow-600';
    return 'text-red-600';
};

const getGradeText = (score, total) => {
    const percentage = (score / total) * 100;
    if (percentage >= 90) return 'Excellent';
    if (percentage >= 70) return 'Good';
    if (percentage >= 50) return 'Fair';
    return 'Needs Improvement';
};

const isLateSubmission = () => {
    return new Date(props.submission.submitted_at) > new Date(props.assignment.due_date);
};
</script>

<template>
    <Head title="View Submission" />

    <DashboardLayout title="View Submission" :subtitle="assignment.title">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('student.assignments.index')"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Assignments
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Assignment Details -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ assignment.title }}</h2>
                    <p class="text-slate-700 mb-4">{{ assignment.description }}</p>

                    <div v-if="assignment.instructions" class="p-4 bg-slate-50 rounded-lg">
                        <h3 class="text-sm font-semibold text-slate-900 mb-2">Instructions</h3>
                        <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ assignment.instructions }}</p>
                    </div>
                </div>

                <!-- Your Submission -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-slate-900">Your Submission</h3>
                        <div class="flex items-center gap-2">
                            <span :class="['px-3 py-1 text-xs font-medium rounded-full', submission.graded_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
                                {{ submission.graded_at ? 'Graded' : 'Pending Review' }}
                            </span>
                            <span v-if="isLateSubmission()" class="px-3 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                Late Submission
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-slate-500 mb-1">Submitted At</p>
                        <p class="text-base font-medium text-slate-900">{{ formatDate(submission.submitted_at) }}</p>
                    </div>

                    <!-- Content -->
                    <div v-if="submission.content" class="mb-4">
                        <p class="text-sm text-slate-500 mb-2">Your Response</p>
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <p class="text-slate-700 whitespace-pre-wrap">{{ submission.content }}</p>
                        </div>
                    </div>

                    <!-- Attachment -->
                    <div v-if="submission.attachment_url">
                        <p class="text-sm text-slate-500 mb-2">Attachment</p>
                        <a :href="submission.attachment_url" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download My Submission
                        </a>
                    </div>
                </div>

                <!-- Teacher Feedback -->
                <div v-if="submission.graded_at" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-xl font-semibold text-slate-900 mb-4">Teacher Feedback</h3>

                    <div class="mb-4">
                        <p class="text-sm text-slate-500 mb-1">Graded At</p>
                        <p class="text-base font-medium text-slate-900">{{ formatDate(submission.graded_at) }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-slate-500 mb-1">Graded By</p>
                        <p class="text-base font-medium text-slate-900">{{ submission.graded_by?.name || 'Teacher' }}</p>
                    </div>

                    <div v-if="submission.feedback" class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm font-semibold text-slate-900 mb-2">Feedback</p>
                        <p class="text-slate-700 whitespace-pre-wrap">{{ submission.feedback }}</p>
                    </div>
                    <div v-else class="p-4 bg-slate-50 rounded-lg">
                        <p class="text-sm text-slate-500 italic">No written feedback provided</p>
                    </div>
                </div>

                <!-- Pending Review Message -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-yellow-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Awaiting Review</h3>
                        <p class="text-slate-600">Your teacher is reviewing your submission. You'll be notified once it's graded.</p>
                        <Link v-if="!submission.graded_at" :href="route('student.assignments.show', assignment.id)"
                              class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Update Submission
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Score Card -->
                <div v-if="submission.graded_at" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Your Score</h3>
                    <div class="text-center">
                        <p :class="['text-5xl font-bold mb-2', getScoreColor(submission.score, assignment.total_points)]">
                            {{ submission.score }}
                        </p>
                        <p class="text-slate-500 text-sm mb-3">out of {{ assignment.total_points }} points</p>
                        <div class="w-full bg-slate-200 rounded-full h-2 mb-3">
                            <div :class="['h-2 rounded-full', getScoreColor(submission.score, assignment.total_points).replace('text-', 'bg-')]"
                                 :style="`width: ${(submission.score / assignment.total_points) * 100}%`"></div>
                        </div>
                        <p :class="['text-lg font-semibold', getScoreColor(submission.score, assignment.total_points)]">
                            {{ ((submission.score / assignment.total_points) * 100).toFixed(1) }}%
                        </p>
                        <p class="text-sm text-slate-600 mt-1">{{ getGradeText(submission.score, assignment.total_points) }}</p>
                    </div>
                </div>

                <!-- Assignment Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Assignment Info</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Subject</p>
                            <p class="text-sm font-medium text-slate-900">{{ assignment.subject?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Teacher</p>
                            <p class="text-sm font-medium text-slate-900">{{ assignment.teacher?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Due Date</p>
                            <p class="text-sm font-medium text-slate-900">{{ formatDate(assignment.due_date) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <Link :href="route('student.assignments.show', assignment.id)"
                              class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-center text-sm">
                            View Assignment
                        </Link>
                        <Link :href="route('student.assignments.index')"
                              class="block w-full px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors text-center text-sm">
                            All Assignments
                        </Link>
                        <a v-if="assignment.attachment_url" :href="assignment.attachment_url" target="_blank"
                           class="block w-full px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors text-center text-sm">
                            Download Materials
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
