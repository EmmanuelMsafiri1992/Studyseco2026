<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    mockExam: Object,
    lastAttempt: Object
});

const form = useForm({});

const startExam = () => {
    form.post(route('student.mock-exams.start', props.mockExam.id));
};
</script>

<template>
    <Head :title="mockExam.title" />

    <DashboardLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <div class="mb-6">
                <Link :href="route('student.mock-exams.index')"
                      class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Mock Exams
                </Link>
            </div>

            <!-- Exam Details Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ mockExam.title }}</h1>
                            <p class="text-indigo-100 text-lg">{{ mockExam.description }}</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg">
                            <div class="text-sm text-indigo-100">Subject</div>
                            <div class="font-semibold">{{ mockExam.subject?.name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Exam Info -->
                <div class="p-8">
                    <div class="grid grid-cols-4 gap-6 mb-8">
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-2xl font-bold text-slate-900">{{ mockExam.duration_minutes }}</div>
                            <div class="text-sm text-slate-600">Minutes</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <svg class="w-8 h-8 mx-auto mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="text-2xl font-bold text-slate-900">{{ mockExam.questions_count }}</div>
                            <div class="text-sm text-slate-600">Questions</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-xl">
                            <svg class="w-8 h-8 mx-auto mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <div class="text-2xl font-bold text-slate-900">{{ mockExam.total_marks }}</div>
                            <div class="text-sm text-slate-600">Total Marks</div>
                        </div>
                        <div class="text-center p-4 bg-orange-50 rounded-xl">
                            <svg class="w-8 h-8 mx-auto mb-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-2xl font-bold text-slate-900">{{ mockExam.passing_marks }}</div>
                            <div class="text-sm text-slate-600">Pass Mark</div>
                        </div>
                    </div>

                    <!-- Last Attempt Results -->
                    <div v-if="lastAttempt" class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Your Last Attempt
                        </h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-slate-600 mb-1">Score</div>
                                <div class="text-2xl font-bold text-slate-900">{{ lastAttempt.score }}/{{ mockExam.total_marks }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-600 mb-1">Percentage</div>
                                <div class="text-2xl font-bold" :class="lastAttempt.percentage >= 75 ? 'text-green-600' : 'text-orange-600'">
                                    {{ lastAttempt.percentage.toFixed(1) }}%
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-600 mb-1">Result</div>
                                <div class="text-lg font-bold" :class="lastAttempt.score >= mockExam.passing_marks ? 'text-green-600' : 'text-red-600'">
                                    {{ lastAttempt.score >= mockExam.passing_marks ? 'PASSED' : 'FAILED' }}
                                </div>
                            </div>
                        </div>

                        <!-- Weak Areas -->
                        <div v-if="lastAttempt.weak_areas && lastAttempt.weak_areas.length > 0" class="mt-4 pt-4 border-t border-blue-200">
                            <div class="text-sm font-medium text-slate-700 mb-2">Areas Needing Improvement:</div>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="area in lastAttempt.weak_areas" :key="area"
                                      class="px-3 py-1 bg-white text-orange-700 rounded-full text-sm font-medium border border-orange-200">
                                    {{ area }}
                                </span>
                            </div>
                        </div>

                        <Link :href="route('student.mock-exams.result', lastAttempt.id)"
                              class="mt-4 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            View Detailed Results
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Important Instructions -->
                    <div class="mb-8 p-6 bg-yellow-50 rounded-xl border-2 border-yellow-200">
                        <h3 class="text-lg font-semibold text-slate-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Important Instructions
                        </h3>
                        <ul class="space-y-2 text-sm text-slate-700">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>You have <strong>{{ mockExam.duration_minutes }} minutes</strong> to complete this exam</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>The exam cannot be paused once started</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>All questions must be answered before submission</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>You need <strong>{{ mockExam.passing_marks }} marks</strong> to pass this exam</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>After submission, you'll receive detailed feedback on weak areas</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Start Button -->
                    <div class="flex justify-center">
                        <button @click="startExam" :disabled="form.processing"
                                class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-lg font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Starting Exam...</span>
                            <span v-else>{{ lastAttempt ? 'Retake Exam' : 'Start Exam Now' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
