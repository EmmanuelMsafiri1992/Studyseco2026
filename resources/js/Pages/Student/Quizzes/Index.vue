<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    quizzes: Array
});

const getQuizStatusBadge = (quiz) => {
    if (!quiz.can_attempt && quiz.attempts_used >= quiz.max_attempts) {
        return { class: 'bg-gray-100 text-gray-800', text: 'Completed' };
    }
    if (quiz.last_attempt?.status === 'in_progress') {
        return { class: 'bg-yellow-100 text-yellow-800', text: 'In Progress' };
    }
    if (quiz.attempts_used > 0) {
        return { class: 'bg-blue-100 text-blue-800', text: `${quiz.attempts_used}/${quiz.max_attempts} Attempts` };
    }
    return { class: 'bg-green-100 text-green-800', text: 'Available' };
};
</script>

<template>
    <Head title="My Quizzes" />

    <DashboardLayout title="My Quizzes" subtitle="View and take available quizzes">
        <!-- Quizzes List -->
        <div class="grid grid-cols-1 gap-6">
            <div v-for="quiz in quizzes" :key="quiz.id"
                 class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-semibold text-slate-900">{{ quiz.title }}</h3>
                            <span :class="['px-3 py-1 text-xs font-medium rounded-full', getQuizStatusBadge(quiz).class]">
                                {{ getQuizStatusBadge(quiz).text }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ quiz.description }}</p>

                        <div class="flex items-center gap-6 text-sm text-slate-500 mb-4">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ quiz.subject?.name }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ quiz.teacher?.name }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ quiz.duration_minutes }} min
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                {{ quiz.total_points }} points (Pass: {{ quiz.passing_score }})
                            </div>
                        </div>

                        <!-- Best Score -->
                        <div v-if="quiz.best_score !== null" class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 rounded-lg">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700">Best Score: {{ quiz.best_score }}/{{ quiz.total_points }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="ml-4">
                        <Link :href="route('student.quizzes.show', quiz.id)"
                              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            {{ quiz.can_attempt ? 'Start Quiz' : 'View Details' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!quizzes || quizzes.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No quizzes available</h3>
                <p class="text-slate-600">Your teachers haven't published any quizzes yet.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
