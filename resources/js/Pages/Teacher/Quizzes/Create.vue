<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';

const props = defineProps({
    subjects: Array
});

const form = useForm({
    subject_id: '',
    title: '',
    description: '',
    duration_minutes: 30,
    total_points: 100,
    passing_score: 70,
    show_correct_answers: true,
    shuffle_questions: false,
    max_attempts: 1,
    status: 'draft',
    available_from: '',
    available_until: '',
});

const submit = () => {
    form.post(route('teacher.quizzes.store'));
};
</script>

<template>
    <Head title="Create Quiz" />

    <DashboardLayout title="Create Quiz" subtitle="Create a new quiz for your students">
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
                    <InputLabel for="title" value="Quiz Title *" />
                    <TextInput
                        id="title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.title"
                        placeholder="e.g., Chapter 5 Quiz"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <InputLabel for="description" value="Description" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Brief description of the quiz..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <!-- Duration and Points -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <InputLabel for="duration_minutes" value="Duration (minutes) *" />
                        <TextInput
                            id="duration_minutes"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.duration_minutes"
                            min="1"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.duration_minutes" />
                    </div>

                    <div>
                        <InputLabel for="total_points" value="Total Points *" />
                        <TextInput
                            id="total_points"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.total_points"
                            min="1"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.total_points" />
                    </div>

                    <div>
                        <InputLabel for="passing_score" value="Passing Score *" />
                        <TextInput
                            id="passing_score"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.passing_score"
                            min="0"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.passing_score" />
                    </div>
                </div>

                <!-- Availability -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <InputLabel for="available_from" value="Available From" />
                        <input
                            id="available_from"
                            type="datetime-local"
                            v-model="form.available_from"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <InputError class="mt-2" :message="form.errors.available_from" />
                    </div>

                    <div>
                        <InputLabel for="available_until" value="Available Until" />
                        <input
                            id="available_until"
                            type="datetime-local"
                            v-model="form.available_until"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <InputError class="mt-2" :message="form.errors.available_until" />
                    </div>
                </div>

                <!-- Settings -->
                <div class="mb-6 space-y-4">
                    <div>
                        <InputLabel for="max_attempts" value="Maximum Attempts *" />
                        <TextInput
                            id="max_attempts"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.max_attempts"
                            min="1"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.max_attempts" />
                    </div>

                    <div class="flex items-center">
                        <input
                            id="show_correct_answers"
                            type="checkbox"
                            v-model="form.show_correct_answers"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="show_correct_answers" class="ml-2 text-sm text-slate-700">
                            Show correct answers after submission
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="shuffle_questions"
                            type="checkbox"
                            v-model="form.shuffle_questions"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="shuffle_questions" class="ml-2 text-sm text-slate-700">
                            Shuffle questions for each student
                        </label>
                    </div>

                    <div>
                        <InputLabel for="status" value="Status *" />
                        <select id="status" v-model="form.status"
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="draft">Draft (Not visible to students)</option>
                            <option value="published">Published (Visible to students)</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                    <a :href="route('teacher.quizzes.index')"
                       class="px-4 py-2 text-slate-700 hover:text-slate-900 transition-colors">
                        Cancel
                    </a>
                    <PrimaryButton :disabled="form.processing">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Creating...' : 'Create Quiz & Add Questions' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
