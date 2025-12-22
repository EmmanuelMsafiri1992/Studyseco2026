<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    attempt: Object,
    exam: Object,
    answers: Object,
    isPassing: Boolean
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
    if (props.attempt.percentage >= 75) return 'text-blue-600';
    if (props.attempt.percentage >= 60) return 'text-yellow-600';
    return 'text-red-600';
};

const getPerformanceMessage = () => {
    const percentage = props.attempt.percentage;
    if (percentage >= 90) return 'Outstanding Performance!';
    if (percentage >= 75) return 'Great Job!';
    if (percentage >= 60) return 'Good Effort!';
    return 'Keep Practicing!';
};
</script>

<template>
    <Head title="Mock Exam Result" />

    <DashboardLayout title="MANEB Exam Result" :subtitle="exam.title">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('student.mock-exams.index')"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Mock Exams
            </Link>
        </div>

        <div class="max-w-5xl mx-auto">
            <!-- Score Card -->
            <div class="bg-gradient-to-br from-white to-indigo-50 rounded-2xl shadow-lg border-2 border-indigo-200 p-10 mb-6">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-slate-900 mb-3">{{ exam.title }}</h2>
                    <p class="text-lg text-slate-600">{{ exam.subject?.name }}</p>
                </div>

                <!-- Main Score Display -->
                <div class="text-center mb-8">
                    <p :class="['text-8xl font-bold mb-2', getScoreColor()]">{{ attempt.score }}</p>
                    <p class="text-2xl text-slate-600 mb-2">out of {{ exam.total_marks }} marks</p>
                    <p class="text-5xl font-bold text-slate-900 mb-4">{{ attempt.percentage.toFixed(1) }}%</p>

                    <div class="w-full max-w-2xl mx-auto bg-slate-200 rounded-full h-4 mb-6">
                        <div :class="['h-4 rounded-full transition-all', isPassing ? 'bg-green-600' : 'bg-red-600']"
                             :style="`width: ${attempt.percentage}%`"></div>
                    </div>

                    <!-- Pass/Fail Badge -->
                    <div v-if="isPassing" class="inline-flex items-center gap-3 px-6 py-3 bg-green-100 text-green-800 rounded-xl border-2 border-green-300 shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-left">
                            <div class="text-2xl font-bold">PASSED</div>
                            <div class="text-sm">{{ getPerformanceMessage() }}</div>
                        </div>
                    </div>
                    <div v-else class="inline-flex items-center gap-3 px-6 py-3 bg-red-100 text-red-800 rounded-xl border-2 border-red-300 shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <div class="text-left">
                            <div class="text-2xl font-bold">DID NOT PASS</div>
                            <div class="text-sm">Required: {{ exam.passing_marks }} marks</div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-4 gap-4 pt-8 border-t-2 border-indigo-200">
                    <div class="text-center p-4 bg-white rounded-xl shadow-sm">
                        <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-slate-500 mb-1">Time Taken</p>
                        <p class="text-lg font-bold text-slate-900">{{ Math.round((new Date(attempt.completed_at) - new Date(attempt.started_at)) / 60000) }} min</p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl shadow-sm">
                        <svg class="w-8 h-8 mx-auto mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-slate-500 mb-1">Total Questions</p>
                        <p class="text-lg font-bold text-slate-900">{{ Object.keys(answers || {}).length }}</p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl shadow-sm">
                        <svg class="w-8 h-8 mx-auto mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-slate-500 mb-1">Passing Mark</p>
                        <p class="text-lg font-bold text-slate-900">{{ exam.passing_marks }}</p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl shadow-sm">
                        <svg class="w-8 h-8 mx-auto mb-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-slate-500 mb-1">Completed</p>
                        <p class="text-sm font-bold text-slate-900">{{ formatDate(attempt.completed_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Weak Areas Analysis -->
            <div v-if="attempt.weak_areas && attempt.weak_areas.length > 0"
                 class="bg-white rounded-2xl shadow-sm border-2 border-yellow-200 p-8 mb-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-slate-900 mb-2">Areas Needing Improvement</h3>
                        <p class="text-slate-600 mb-4">Focus on these topics to improve your performance:</p>
                        <div class="flex flex-wrap gap-3">
                            <span v-for="area in attempt.weak_areas" :key="area"
                                  class="px-4 py-2 bg-yellow-50 text-yellow-800 rounded-lg text-sm font-semibold border-2 border-yellow-300">
                                📚 {{ area }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message (No Weak Areas) -->
            <div v-else-if="isPassing" class="bg-white rounded-2xl shadow-sm border-2 border-green-200 p-8 mb-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-green-700 mb-2">Excellent Performance!</h3>
                        <p class="text-slate-600">You showed strong understanding across all topics. Keep up the great work!</p>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-6">
                <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Next Steps
                </h3>
                <ul class="space-y-3">
                    <li v-if="!isPassing" class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-slate-700">Review the topics where you struggled and practice more questions</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-slate-700">Check your teaching materials for additional study resources</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-slate-700">Attempt more mock exams to track your progress</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-slate-700">Reach out to your teacher if you need additional help</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 justify-center">
                <Link :href="route('student.mock-exams.show', exam.id)"
                      class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors font-semibold shadow-lg">
                    Retake Exam
                </Link>
                <Link :href="route('student.mock-exams.index')"
                      class="px-6 py-3 bg-white text-slate-700 border-2 border-slate-300 rounded-lg hover:bg-slate-50 transition-colors font-semibold">
                    Back to Exams
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>
