<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    subjects: Array,
});

const form = useForm({
    name: '',
    description: '',
    subject_id: '',
    game_type: 'trivia',
    max_score: 100,
    time_limit: null,
    is_active: true,
});

const gameTypes = [
    { value: 'trivia', label: 'Trivia Quiz' },
    { value: 'math_challenge', label: 'Math Challenge' },
    { value: 'word_puzzle', label: 'Word Puzzle' },
    { value: 'memory', label: 'Memory Game' },
];

const submit = () => {
    form.post(route('admin.games.store'));
};
</script>

<template>
    <DashboardLayout title="Create Game">
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Create New Game
                    </h2>
                </div>

                <div class="bg-white shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6 p-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Game Name
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">
                                Subject
                            </label>
                            <select
                                id="subject_id"
                                v-model="form.subject_id"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">Select a subject</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.subject_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.subject_id }}
                            </p>
                        </div>

                        <!-- Game Type -->
                        <div>
                            <label for="game_type" class="block text-sm font-medium text-gray-700">
                                Game Type
                            </label>
                            <select
                                id="game_type"
                                v-model="form.game_type"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option v-for="type in gameTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.game_type" class="mt-1 text-sm text-red-600">
                                {{ form.errors.game_type }}
                            </p>
                        </div>

                        <!-- Max Score -->
                        <div>
                            <label for="max_score" class="block text-sm font-medium text-gray-700">
                                Maximum Score
                            </label>
                            <input
                                id="max_score"
                                v-model.number="form.max_score"
                                type="number"
                                min="1"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                            <p v-if="form.errors.max_score" class="mt-1 text-sm text-red-600">
                                {{ form.errors.max_score }}
                            </p>
                        </div>

                        <!-- Time Limit -->
                        <div>
                            <label for="time_limit" class="block text-sm font-medium text-gray-700">
                                Time Limit (seconds) - Optional
                            </label>
                            <input
                                id="time_limit"
                                v-model.number="form.time_limit"
                                type="number"
                                min="1"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            />
                            <p v-if="form.errors.time_limit" class="mt-1 text-sm text-red-600">
                                {{ form.errors.time_limit }}
                            </p>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (visible to students)
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a
                                :href="route('admin.games.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                Create Game
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
