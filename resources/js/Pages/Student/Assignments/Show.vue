<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assignment: Object,
    mySubmission: Object
});

const attachmentInput = ref(null);
const attachmentPreview = ref(null);

const form = useForm({
    content: props.mySubmission?.content || '',
    attachment: null
});

const selectAttachment = () => {
    attachmentInput.value.click();
};

const updateAttachmentPreview = () => {
    const file = attachmentInput.value.files[0];
    if (file) {
        form.attachment = file;
        attachmentPreview.value = file.name;
    }
};

const removeAttachment = () => {
    form.attachment = null;
    attachmentPreview.value = null;
    if (attachmentInput.value) {
        attachmentInput.value.value = null;
    }
};

const submit = () => {
    form.post(route('student.assignments.submit', props.assignment.id), {
        preserveScroll: true
    });
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

const canSubmit = () => {
    if (props.mySubmission?.graded_at) return false;
    if (isOverdue(props.assignment.due_date) && !props.assignment.allow_late_submissions) return false;
    return true;
};
</script>

<template>
    <Head :title="assignment.title" />

    <DashboardLayout :title="assignment.title" subtitle="Assignment Details">
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
            <!-- Assignment Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-slate-900 mb-2">{{ assignment.title }}</h2>
                        <p class="text-slate-700">{{ assignment.description }}</p>
                    </div>

                    <!-- Instructions -->
                    <div v-if="assignment.instructions" class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-3">Instructions</h3>
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <p class="text-slate-700 whitespace-pre-wrap">{{ assignment.instructions }}</p>
                        </div>
                    </div>

                    <!-- Assignment Attachment -->
                    <div v-if="assignment.attachment_url" class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-3">Assignment Materials</h3>
                        <a :href="assignment.attachment_url" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Assignment File
                        </a>
                    </div>

                    <!-- Submission Form -->
                    <div v-if="canSubmit()">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">
                            {{ mySubmission ? 'Update Your Submission' : 'Submit Your Work' }}
                        </h3>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Content -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Your Response *</label>
                                <textarea v-model="form.content" rows="8" required
                                          class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                          :class="{ 'border-red-300': form.errors.content }"
                                          placeholder="Type your response here..."></textarea>
                                <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                            </div>

                            <!-- Attachment -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Attachment (Optional)</label>
                                <p class="text-xs text-slate-500 mb-2">Upload your completed work (PDF, DOC, DOCX, TXT, ZIP - Max 10MB)</p>

                                <input
                                    type="file"
                                    ref="attachmentInput"
                                    class="hidden"
                                    @change="updateAttachmentPreview"
                                    accept=".pdf,.doc,.docx,.txt,.zip"
                                />

                                <div v-if="!attachmentPreview" class="mt-2">
                                    <button type="button"
                                            @click.prevent="selectAttachment"
                                            class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                                        Choose File
                                    </button>
                                </div>

                                <div v-else class="mt-2 flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-900">{{ attachmentPreview }}</p>
                                    </div>
                                    <button type="button" @click="removeAttachment" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                                <p class="text-sm text-slate-600">
                                    {{ mySubmission ? 'You can update your submission until it is graded.' : 'Make sure to review your work before submitting.' }}
                                </p>
                                <button type="submit" :disabled="form.processing"
                                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ form.processing ? 'Submitting...' : (mySubmission ? 'Update Submission' : 'Submit Assignment') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Already Graded Message -->
                    <div v-else-if="mySubmission?.graded_at" class="p-6 bg-green-50 rounded-lg text-center">
                        <svg class="w-12 h-12 mx-auto text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Assignment Graded</h3>
                        <p class="text-slate-600 mb-4">This assignment has been graded by your teacher.</p>
                        <Link :href="route('student.assignments.viewSubmission', assignment.id)"
                              class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View Your Grade
                        </Link>
                    </div>

                    <!-- Overdue Message -->
                    <div v-else class="p-6 bg-red-50 rounded-lg text-center">
                        <svg class="w-12 h-12 mx-auto text-red-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Assignment Overdue</h3>
                        <p class="text-slate-600">The due date for this assignment has passed and late submissions are not allowed.</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Assignment Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Assignment Info</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Subject</p>
                            <p class="text-sm font-medium text-slate-900">{{ assignment.subject?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Teacher</p>
                            <p class="text-sm font-medium text-slate-900">{{ assignment.teacher?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Total Points</p>
                            <p class="text-sm font-medium text-slate-900">{{ assignment.total_points }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Due Date</p>
                            <p class="text-sm font-medium text-slate-900">{{ formatDate(assignment.due_date) }}</p>
                            <p v-if="isOverdue(assignment.due_date)" class="text-xs text-red-600 mt-1">Overdue</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Late Submissions</p>
                            <p class="text-sm font-medium text-slate-900">{{ assignment.allow_late_submissions ? 'Allowed' : 'Not Allowed' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submission Status -->
                <div v-if="mySubmission" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Your Submission</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Status</p>
                            <span :class="['inline-block px-2 py-1 text-xs font-medium rounded-full', mySubmission.graded_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
                                {{ mySubmission.graded_at ? 'Graded' : 'Pending Review' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Submitted At</p>
                            <p class="text-sm font-medium text-slate-900">{{ formatDate(mySubmission.submitted_at) }}</p>
                        </div>
                        <div v-if="mySubmission.graded_at">
                            <p class="text-xs text-slate-500 mb-1">Score</p>
                            <p class="text-2xl font-bold text-green-600">{{ mySubmission.score }}/{{ assignment.total_points }}</p>
                            <p class="text-sm text-slate-600">{{ ((mySubmission.score / assignment.total_points) * 100).toFixed(1) }}%</p>
                        </div>
                        <Link :href="route('student.assignments.viewSubmission', assignment.id)"
                              class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-center text-sm">
                            View Full Submission
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
