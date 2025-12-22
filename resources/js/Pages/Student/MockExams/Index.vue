<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    exams: Array
});

const getExamStatusBadge = (exam) => {
    if (exam.last_attempt) {
        const percentage = exam.last_attempt.percentage;
        if (percentage >= 75) {
            return { class: 'bg-green-100 text-green-800', text: `Passed: ${percentage.toFixed(1)}%` };
        } else {
            return { class: 'bg-orange-100 text-orange-800', text: `Attempted: ${percentage.toFixed(1)}%` };
        }
    }
    return { class: 'bg-blue-100 text-blue-800', text: 'Not Attempted' };
};
</script>

<template>
    <Head title="MANEB Mock Exams" />

    <DashboardLayout title="MANEB Mock Exams" subtitle="Practice with official MANEB-style examinations">
        <!-- Exams List -->
        <div class="grid grid-cols-1 gap-6">
            <div v-for="exam in exams" :key="exam.id"
                 class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-semibold text-slate-900">{{ exam.title }}</h3>
                            <span :class="['px-3 py-1 text-xs font-medium rounded-full', getExamStatusBadge(exam).class]">
                                {{ getExamStatusBadge(exam).text }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ exam.description }}</p>

                        <div class="flex items-center gap-6 text-sm text-slate-500 mb-4">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ exam.subject?.name }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ exam.duration_minutes }} minutes
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ exam.questions_count }} questions
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                {{ exam.total_marks }} marks (Pass: {{ exam.passing_marks }})
                            </div>
                        </div>

                        <!-- Last Attempt Details -->
                        <div v-if="exam.last_attempt" class="flex items-center gap-4 mb-3">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-blue-700">Score: {{ exam.last_attempt.score }}/{{ exam.total_marks }}</span>
                            </div>
                            <div v-if="exam.last_attempt.weak_areas && exam.last_attempt.weak_areas.length > 0"
                                 class="inline-flex items-center gap-2 px-3 py-1 bg-yellow-50 rounded-lg">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="text-sm font-medium text-yellow-700">{{ exam.last_attempt.weak_areas.length }} weak areas identified</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="ml-4">
                        <Link :href="route('student.mock-exams.show', exam.id)"
                              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            {{ exam.last_attempt ? 'Retake Exam' : 'Start Exam' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!exams || exams.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No mock exams available</h3>
                <p class="text-slate-600">Check back later for MANEB practice examinations.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
