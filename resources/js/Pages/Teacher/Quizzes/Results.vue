<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    quiz: Object,
    attempts: Object
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
</script>

<template>
    <Head :title="`${quiz.title} - Results`" />

    <DashboardLayout :title="`${quiz.title}`" subtitle="Quiz Results">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('teacher.quizzes.show', quiz.id)"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Quiz
            </Link>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-6">All Attempts ({{ attempts.total }})</h3>

            <div v-if="attempts.data && attempts.data.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Student</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Attempt #</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Started At</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Completed At</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Score</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Percentage</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attempt in attempts.data" :key="attempt.id" class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-700">{{ attempt.student?.name?.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ attempt.student?.name }}</p>
                                        <p class="text-xs text-slate-500">{{ attempt.student?.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-slate-600">{{ attempt.attempt_number }}</td>
                            <td class="py-3 px-4 text-sm text-slate-600">{{ formatDate(attempt.started_at) }}</td>
                            <td class="py-3 px-4 text-sm text-slate-600">
                                {{ attempt.completed_at ? formatDate(attempt.completed_at) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-sm font-medium text-slate-900">
                                {{ attempt.score !== null ? `${attempt.score}/${quiz.total_points}` : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span v-if="attempt.percentage !== null" class="text-sm font-medium text-slate-900">
                                    {{ attempt.percentage }}%
                                </span>
                                <span v-else class="text-sm text-slate-500">-</span>
                            </td>
                            <td class="py-3 px-4">
                                <span v-if="attempt.status === 'completed'" :class="['px-2 py-1 text-xs font-medium rounded-full', attempt.score >= quiz.passing_score ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                    {{ attempt.score >= quiz.passing_score ? 'Passed' : 'Failed' }}
                                </span>
                                <span v-else-if="attempt.status === 'in_progress'" class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                    In Progress
                                </span>
                                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    {{ attempt.status }}
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

            <!-- Pagination -->
            <div v-if="attempts.data && attempts.data.length > 0" class="mt-6 flex items-center justify-between pt-4 border-t border-slate-200">
                <div class="text-sm text-slate-700">
                    Showing {{ attempts.from || 0 }} to {{ attempts.to || 0 }} of {{ attempts.total || 0 }} results
                </div>
                <div class="flex items-center space-x-2">
                    <Link v-if="attempts.prev_page_url" :href="attempts.prev_page_url"
                          class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                        Previous
                    </Link>
                    <Link v-if="attempts.next_page_url" :href="attempts.next_page_url"
                          class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
