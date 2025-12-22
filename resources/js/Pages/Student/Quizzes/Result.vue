<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    attempt: Object,
    quiz: Object,
    answers: Array,
    showCorrectAnswers: Boolean,
    isPassing: Boolean,
    grade: String,
    timeSpent: Number
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

const getScoreColor = () => {
    if (props.attempt.percentage >= 90) return 'text-green-600';
    if (props.attempt.percentage >= 70) return 'text-blue-600';
    if (props.attempt.percentage >= 50) return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Quiz Result" />

    <DashboardLayout title="Quiz Result" :subtitle="quiz.title">
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
            <!-- Results Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Score Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-slate-900 mb-2">{{ quiz.title }}</h2>
                        <p :class="['text-6xl font-bold mb-2', getScoreColor()]">{{ attempt.score }}</p>
                        <p class="text-lg text-slate-600 mb-1">out of {{ quiz.total_points }} points</p>
                        <p class="text-3xl font-semibold text-slate-900 mb-4">{{ attempt.percentage }}%</p>

                        <div class="w-full bg-slate-200 rounded-full h-3 mb-4">
                            <div :class="['h-3 rounded-full transition-all', isPassing ? 'bg-green-600' : 'bg-red-600']"
                                 :style="`width: ${attempt.percentage}%`"></div>
                        </div>

                        <div v-if="isPassing" class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium">Passed!</span>
                        </div>
                        <div v-else class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-800 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="font-medium">Did Not Pass</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-200">
                        <div class="text-center">
                            <p class="text-sm text-slate-500 mb-1">Time Spent</p>
                            <p class="text-lg font-semibold text-slate-900">{{ timeSpent || 0 }} min</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-slate-500 mb-1">Grade</p>
                            <p class="text-lg font-semibold text-slate-900">{{ grade }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-slate-500 mb-1">Passing Score</p>
                            <p class="text-lg font-semibold text-slate-900">{{ quiz.passing_score }}</p>
                        </div>
                    </div>
                </div>

                <!-- Question Review -->
                <div v-if="showCorrectAnswers && answers && answers.length > 0" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-xl font-semibold text-slate-900 mb-6">Question Review</h3>
                    <div class="space-y-6">
                        <div v-for="(answer, index) in answers" :key="answer.id"
                             class="border border-slate-200 rounded-lg p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <span :class="['flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-medium text-sm', answer.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                    {{ index + 1 }}
                                </span>
                                <div class="flex-1">
                                    <p class="text-base font-medium text-slate-900 mb-2">{{ answer.question?.question }}</p>

                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-slate-600">Your Answer:</span>
                                            <span :class="['text-sm font-medium', answer.is_correct ? 'text-green-600' : 'text-red-600']">
                                                {{ answer.student_answer }}
                                            </span>
                                            <span v-if="answer.is_correct" class="text-green-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                            <span v-else class="text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </span>
                                        </div>

                                        <div v-if="!answer.is_correct" class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-slate-600">Correct Answer:</span>
                                            <span class="text-sm font-medium text-green-600">{{ answer.question?.correct_answer }}</span>
                                        </div>

                                        <div v-if="answer.question?.explanation" class="mt-2 p-3 bg-blue-50 rounded-lg">
                                            <p class="text-sm text-blue-900">
                                                <span class="font-medium">Explanation:</span> {{ answer.question.explanation }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-2 text-sm text-slate-600">
                                        Points: {{ answer.points_earned }}/{{ answer.question?.points }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!showCorrectAnswers" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Answers Not Available</h3>
                    <p class="text-slate-600">Your teacher has chosen not to show correct answers for this quiz.</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Attempt Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Attempt Details</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Attempt Number</p>
                            <p class="text-base font-medium text-slate-900">{{ attempt.attempt_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Started At</p>
                            <p class="text-base font-medium text-slate-900">{{ formatDate(attempt.started_at) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Completed At</p>
                            <p class="text-base font-medium text-slate-900">{{ formatDate(attempt.completed_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <Link :href="route('student.quizzes.show', quiz.id)"
                              class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-center text-sm">
                            View Quiz Details
                        </Link>
                        <Link :href="route('student.quizzes.index')"
                              class="block w-full px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors text-center text-sm">
                            All Quizzes
                        </Link>
                    </div>
                </div>

                <!-- Performance Tips -->
                <div v-if="!isPassing" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-2">Tips for Improvement</h3>
                    <ul class="text-sm text-yellow-800 space-y-2">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Review the correct answers above</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Study the course materials</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Ask your teacher for help</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
