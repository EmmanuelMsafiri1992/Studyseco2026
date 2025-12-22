<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    quiz: Object,
    attempts: Array,
    canAttempt: Boolean,
    inProgressAttempt: Object,
    bestScore: Number
});

const startQuiz = () => {
    if (confirm('Are you ready to start the quiz? The timer will begin immediately.')) {
        router.post(route('student.quizzes.start', props.quiz.id));
    }
};

const resumeQuiz = () => {
    router.get(route('student.quizzes.take', props.inProgressAttempt.id));
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
</script>

<template>
    <Head :title="quiz.title" />

    <DashboardLayout :title="quiz.title" subtitle="Quiz Details">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('student.quizzes.index')"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Quizzes
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Quiz Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">{{ quiz.title }}</h2>
                    <p v-if="quiz.description" class="text-slate-700 mb-6">{{ quiz.description }}</p>

                    <!-- Quiz Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Subject</p>
                            <p class="text-base font-medium text-slate-900">{{ quiz.subject?.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Teacher</p>
                            <p class="text-base font-medium text-slate-900">{{ quiz.teacher?.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Duration</p>
                            <p class="text-base font-medium text-slate-900">{{ quiz.duration_minutes }} minutes</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Total Points</p>
                            <p class="text-base font-medium text-slate-900">{{ quiz.total_points }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Passing Score</p>
                            <p class="text-base font-medium text-slate-900">{{ quiz.passing_score }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Questions</p>
                            <p class="text-base font-medium text-slate-900">{{ quiz.questions?.length || 0 }}</p>
                        </div>
                    </div>

                    <!-- In Progress Notice -->
                    <div v-if="inProgressAttempt" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800">You have an in-progress attempt</p>
                                <p class="text-xs text-yellow-700 mt-1">Started {{ formatDate(inProgressAttempt.started_at) }}</p>
                            </div>
                            <button @click="resumeQuiz"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                Resume Quiz
                            </button>
                        </div>
                    </div>

                    <!-- Start Quiz Button -->
                    <div v-else-if="canAttempt" class="p-6 bg-indigo-50 rounded-lg text-center">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Ready to start?</h3>
                        <p class="text-sm text-slate-600 mb-4">You have {{ quiz.max_attempts - attempts.filter(a => a.status === 'completed').length }} attempt(s) remaining.</p>
                        <button @click="startQuiz"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                            Start Quiz
                        </button>
                    </div>

                    <!-- No Attempts Left -->
                    <div v-else class="p-6 bg-slate-50 rounded-lg text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No attempts remaining</h3>
                        <p class="text-sm text-slate-600">You have used all {{ quiz.max_attempts }} attempts for this quiz.</p>
                    </div>
                </div>

                <!-- Previous Attempts -->
                <div v-if="attempts && attempts.length > 0" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Your Attempts</h3>
                    <div class="space-y-3">
                        <div v-for="attempt in attempts" :key="attempt.id"
                             class="border border-slate-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Attempt {{ attempt.attempt_number }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ formatDate(attempt.started_at) }}</p>
                                </div>
                                <div class="text-right">
                                    <p v-if="attempt.status === 'completed'" class="text-lg font-bold text-slate-900">
                                        {{ attempt.score }}/{{ quiz.total_points }}
                                    </p>
                                    <p v-if="attempt.percentage" class="text-sm text-slate-600">{{ attempt.percentage }}%</p>
                                    <span v-if="attempt.status === 'in_progress'" class="text-xs text-yellow-600">In Progress</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Best Score -->
                <div v-if="bestScore !== null" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Your Best Score</h3>
                    <div class="text-center">
                        <p class="text-4xl font-bold text-green-600">{{ bestScore }}</p>
                        <p class="text-sm text-slate-600 mt-1">out of {{ quiz.total_points }} points</p>
                        <div class="w-full bg-slate-200 rounded-full h-2 mt-4">
                            <div class="bg-green-600 h-2 rounded-full" :style="`width: ${(bestScore / quiz.total_points) * 100}%`"></div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Stats -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Quiz Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ quiz.duration_minutes }} minutes time limit</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ quiz.max_attempts }} attempt(s) allowed</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ quiz.questions?.length || 0 }} questions</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>{{ quiz.show_correct_answers ? 'Answers shown after' : 'Answers not shown' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
