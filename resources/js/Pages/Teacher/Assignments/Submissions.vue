<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assignment: Object,
    submissions: Object
});

const gradingSubmission = ref(null);
const gradeForm = useForm({
    score: '',
    feedback: ''
});

const openGradeModal = (submission) => {
    gradingSubmission.value = submission;
    gradeForm.score = submission.score || '';
    gradeForm.feedback = submission.feedback || '';
};

const closeGradeModal = () => {
    gradingSubmission.value = null;
    gradeForm.reset();
};

const submitGrade = () => {
    gradeForm.post(route('teacher.assignments.grade', [props.assignment.id, gradingSubmission.value.id]), {
        preserveScroll: true,
        onSuccess: () => {
            closeGradeModal();
        }
    });
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

const isLateSubmission = (submission) => {
    return new Date(submission.submitted_at) > new Date(props.assignment.due_date);
};
</script>

<template>
    <Head :title="`${assignment.title} - Submissions`" />

    <DashboardLayout :title="`${assignment.title}`" subtitle="Student Submissions">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('teacher.assignments.show', assignment.id)"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Assignment
            </Link>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Total Submissions</p>
                        <p class="text-3xl font-bold text-slate-900">{{ submissions.total || 0 }}</p>
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
                        <p class="text-3xl font-bold text-yellow-600">
                            {{ submissions.data?.filter(s => !s.graded_at).length || 0 }}
                        </p>
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
                        <p class="text-3xl font-bold text-green-600">
                            {{ submissions.data?.filter(s => s.graded_at).length || 0 }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submissions List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-6">All Submissions</h3>

            <div v-if="submissions.data && submissions.data.length > 0" class="space-y-4">
                <div v-for="submission in submissions.data" :key="submission.id"
                     class="border border-slate-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-medium text-indigo-700">{{ submission.student?.name?.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-slate-900">{{ submission.student?.name }}</h4>
                                    <p class="text-sm text-slate-500">{{ submission.student?.email }}</p>
                                </div>
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getSubmissionStatusBadge(submission).class]">
                                    {{ getSubmissionStatusBadge(submission).text }}
                                </span>
                                <span v-if="isLateSubmission(submission)" class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                    Late
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Submitted At</p>
                                    <p class="text-sm font-medium text-slate-900">{{ formatDate(submission.submitted_at) }}</p>
                                </div>
                                <div v-if="submission.graded_at">
                                    <p class="text-xs text-slate-500 mb-1">Graded At</p>
                                    <p class="text-sm font-medium text-slate-900">{{ formatDate(submission.graded_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Score</p>
                                    <p class="text-sm font-medium text-slate-900">
                                        {{ submission.score !== null ? `${submission.score}/${assignment.total_points}` : 'Not graded' }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="submission.content" class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Student Response</p>
                                <div class="p-3 bg-slate-50 rounded-lg">
                                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ submission.content }}</p>
                                </div>
                            </div>

                            <div v-if="submission.attachment_url" class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Attachment</p>
                                <a :href="submission.attachment_url" target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Submission
                                </a>
                            </div>

                            <div v-if="submission.feedback" class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Teacher Feedback</p>
                                <div class="p-3 bg-green-50 rounded-lg">
                                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ submission.feedback }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="ml-4">
                            <button @click="openGradeModal(submission)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                                {{ submission.graded_at ? 'Update Grade' : 'Grade' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No submissions yet</h3>
                <p class="text-slate-600">Students haven't submitted their work for this assignment.</p>
            </div>

            <!-- Pagination -->
            <div v-if="submissions.data && submissions.data.length > 0" class="mt-6 flex items-center justify-between pt-4 border-t border-slate-200">
                <div class="text-sm text-slate-700">
                    Showing {{ submissions.from || 0 }} to {{ submissions.to || 0 }} of {{ submissions.total || 0 }} results
                </div>
                <div class="flex items-center space-x-2">
                    <Link v-if="submissions.prev_page_url" :href="submissions.prev_page_url"
                          class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                        Previous
                    </Link>
                    <Link v-if="submissions.next_page_url" :href="submissions.next_page_url"
                          class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                        Next
                    </Link>
                </div>
            </div>
        </div>

        <!-- Grading Modal -->
        <div v-if="gradingSubmission" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-900">Grade Submission</h3>
                        <button @click="closeGradeModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm text-slate-600 mt-1">{{ gradingSubmission.student?.name }}</p>
                </div>

                <form @submit.prevent="submitGrade" class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Score (out of {{ assignment.total_points }}) *
                        </label>
                        <input v-model="gradeForm.score" type="number" min="0" :max="assignment.total_points" required
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                               :class="{ 'border-red-300': gradeForm.errors.score }">
                        <p v-if="gradeForm.errors.score" class="mt-1 text-sm text-red-600">{{ gradeForm.errors.score }}</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Feedback (Optional)</label>
                        <textarea v-model="gradeForm.feedback" rows="6"
                                  class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Provide feedback to help the student improve..."></textarea>
                        <p v-if="gradeForm.errors.feedback" class="mt-1 text-sm text-red-600">{{ gradeForm.errors.feedback }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="closeGradeModal"
                                class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="gradeForm.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ gradeForm.processing ? 'Submitting...' : 'Submit Grade' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
