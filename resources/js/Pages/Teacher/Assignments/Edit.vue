<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';

const props = defineProps({
    assignment: Object,
    subjects: Array
});

const attachmentInput = ref(null);
const attachmentPreview = ref(props.assignment.attachment_url ? props.assignment.attachment_url.split('/').pop() : null);

const form = useForm({
    subject_id: props.assignment.subject_id,
    title: props.assignment.title,
    description: props.assignment.description,
    instructions: props.assignment.instructions,
    total_points: props.assignment.total_points,
    due_date: props.assignment.due_date ? props.assignment.due_date.slice(0, 16) : '',
    status: props.assignment.status,
    allow_late_submissions: props.assignment.allow_late_submissions || false,
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
    form.post(route('teacher.assignments.update', props.assignment.id), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Edit Assignment" />

    <DashboardLayout title="Edit Assignment" :subtitle="`Update ${assignment.title}`">
        <div class="max-w-4xl mx-auto">
            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <!-- Subject Selection -->
                <div class="mb-6">
                    <InputLabel for="subject_id" value="Subject *" />
                    <select id="subject_id" v-model="form.subject_id"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a subject</option>
                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                            {{ subject.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.subject_id" />
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <InputLabel for="title" value="Assignment Title *" />
                    <TextInput
                        id="title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.title"
                        placeholder="e.g., Chapter 5 Review Questions"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <InputLabel for="description" value="Description *" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Describe what students need to do..."
                        required
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <!-- Instructions -->
                <div class="mb-6">
                    <InputLabel for="instructions" value="Instructions (Optional)" />
                    <textarea
                        id="instructions"
                        v-model="form.instructions"
                        rows="4"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Additional instructions, guidelines, or requirements..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.instructions" />
                </div>

                <!-- Points and Due Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <InputLabel for="total_points" value="Total Points *" />
                        <TextInput
                            id="total_points"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.total_points"
                            min="1"
                            max="1000"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.total_points" />
                    </div>

                    <div>
                        <InputLabel for="due_date" value="Due Date *" />
                        <input
                            id="due_date"
                            type="datetime-local"
                            v-model="form.due_date"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.due_date" />
                    </div>
                </div>

                <!-- Attachment -->
                <div class="mb-6">
                    <InputLabel for="attachment" value="Attachment (Optional)" />
                    <p class="text-xs text-slate-500 mb-2">Upload assignment files, worksheets, or reference materials (PDF, DOC, DOCX, TXT, ZIP - Max 10MB)</p>

                    <!-- Existing Attachment -->
                    <div v-if="assignment.attachment_url && !form.attachment" class="mb-2 p-3 bg-blue-50 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Current attachment</p>
                                <a :href="assignment.attachment_url" target="_blank" class="text-xs text-blue-600 hover:underline">Download file</a>
                            </div>
                        </div>
                    </div>

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
                            {{ assignment.attachment_url ? 'Replace File' : 'Choose File' }}
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
                    <InputError class="mt-2" :message="form.errors.attachment" />
                </div>

                <!-- Options -->
                <div class="mb-6 space-y-4">
                    <!-- Status -->
                    <div>
                        <InputLabel for="status" value="Status *" />
                        <select id="status" v-model="form.status"
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="draft">Draft (Not visible to students)</option>
                            <option value="published">Published (Visible to students)</option>
                            <option value="closed">Closed (No submissions allowed)</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>

                    <!-- Allow Late Submissions -->
                    <div class="flex items-center">
                        <input
                            id="allow_late_submissions"
                            type="checkbox"
                            v-model="form.allow_late_submissions"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="allow_late_submissions" class="ml-2 text-sm text-slate-700">
                            Allow late submissions after due date
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                    <Link :href="route('teacher.assignments.index')"
                          class="px-4 py-2 text-slate-700 hover:text-slate-900 transition-colors">
                        Cancel
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Updating...' : 'Update Assignment' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
