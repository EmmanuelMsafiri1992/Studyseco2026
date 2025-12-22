<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    quiz: Object,
    stats: Object,
    recentAttempts: Array
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

const getStatusBadgeClass = (status) => {
    const classes = {
        'published': 'bg-green-100 text-green-800',
        'draft': 'bg-gray-100 text-gray-800',
        'archived': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="quiz.title" />

    <DashboardLayout :title="quiz.title" subtitle="Quiz Overview">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('teacher.quizzes.index')"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Quizzes
            </Link>
        </div>

        <!-- Quiz Details Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-4">
                        <h2 class="text-2xl font-bold text-slate-900">{{ quiz.title }}</h2>
                        <span :class="['px-3 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(quiz.status)]">
                            {{ quiz.status }}
                        </span>
                    </div>
                    <p v-if="quiz.description" class="text-slate-700 mb-4">{{ quiz.description }}</p>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <Link :href="route('teacher.quizzes.edit', quiz.id)"
                          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Quiz
                    </Link>
                </div>
            </div>

            <!-- Quiz Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Subject</p>
                    <p class="text-base font-medium text-slate-900">{{ quiz.subject?.name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Questions</p>
                    <p class="text-base font-medium text-slate-900">{{ quiz.questions?.length || 0 }}</p>
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
                    <p class="text-sm text-slate-500 mb-1">Max Attempts</p>
                    <p class="text-base font-medium text-slate-900">{{ quiz.max_attempts }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Shuffle Questions</p>
                    <p class="text-base font-medium text-slate-900">{{ quiz.shuffle_questions ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Show Answers</p>
                    <p class="text-base font-medium text-slate-900">{{ quiz.show_correct_answers ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Total Attempts</p>
                        <p class="text-3xl font-bold text-slate-900">{{ stats.total_attempts }}</p>
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
                        <p class="text-sm text-slate-500 mb-1">Unique Students</p>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.total_students }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Average Score</p>
                        <p class="text-3xl font-bold text-purple-600">{{ stats.average_score ? Math.round(stats.average_score) : 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Pass Rate</p>
                        <p class="text-3xl font-bold text-green-600">{{ stats.pass_rate }}%</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attempts -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-slate-900">Recent Attempts</h3>
                <Link :href="route('teacher.quizzes.results', quiz.id)"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                    View All Results
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </Link>
            </div>

            <div v-if="recentAttempts && recentAttempts.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Student</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Completed At</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Score</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Percentage</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attempt in recentAttempts" :key="attempt.id" class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-700">{{ attempt.student?.name?.charAt(0) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-slate-900">{{ attempt.student?.name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-slate-600">{{ formatDate(attempt.completed_at) }}</td>
                            <td class="py-3 px-4 text-sm font-medium text-slate-900">{{ attempt.score }}/{{ quiz.total_points }}</td>
                            <td class="py-3 px-4 text-sm font-medium text-slate-900">{{ attempt.percentage }}%</td>
                            <td class="py-3 px-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', attempt.score >= quiz.passing_score ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                    {{ attempt.score >= quiz.passing_score ? 'Passed' : 'Failed' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No attempts yet</h3>
                <p class="text-slate-600">Students haven't taken this quiz yet.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
