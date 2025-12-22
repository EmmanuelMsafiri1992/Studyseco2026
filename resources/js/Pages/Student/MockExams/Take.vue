<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    attempt: Object,
    exam: Object,
    questions: Array,
    timeRemaining: Number
});

const answers = ref({});
const timeLeft = ref(props.timeRemaining);
const timerInterval = ref(null);

const form = useForm({
    answers: {}
});

// Initialize answers
props.questions.forEach(q => {
    answers.value[q.id] = '';
});

// Timer
const startTimer = () => {
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            submitExam();
        }
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const timeLeftPercentage = computed(() => {
    return (timeLeft.value / props.timeRemaining) * 100;
});

const timeLeftClass = computed(() => {
    const percentage = timeLeftPercentage.value;
    if (percentage > 50) return 'text-green-600';
    if (percentage > 25) return 'text-yellow-600';
    return 'text-red-600';
});

const submitExam = () => {
    form.answers = answers.value;
    form.post(route('student.mock-exams.submit', props.attempt.id));
};

const confirmSubmit = () => {
    if (confirm('Are you sure you want to submit? You cannot change your answers after submission.')) {
        submitExam();
    }
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});
</script>

<template>
    <Head :title="`Take Exam: ${exam.title}`" />

    <DashboardLayout :title="exam.title" subtitle="MANEB Mock Examination">
        <div class="max-w-4xl mx-auto">
            <!-- Timer Banner -->
            <div class="bg-white rounded-2xl shadow-sm border-2 border-indigo-200 p-4 mb-6 sticky top-4 z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Time Remaining</p>
                        <p :class="['text-2xl font-bold', timeLeftClass]">{{ formatTime(timeLeft) }}</p>
                    </div>
                    <div class="flex-1 mx-6">
                        <div class="w-full bg-slate-200 rounded-full h-3">
                            <div :class="['h-3 rounded-full transition-all', timeLeftPercentage > 25 ? 'bg-green-600' : 'bg-red-600']"
                                 :style="`width: ${timeLeftPercentage}%`"></div>
                        </div>
                    </div>
                    <div class="text-right mr-4">
                        <p class="text-sm text-slate-600">Questions</p>
                        <p class="text-lg font-bold text-slate-900">{{ questions.length }}</p>
                    </div>
                    <button @click="confirmSubmit" :disabled="form.processing"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors font-semibold shadow-lg disabled:opacity-50">
                        Submit Exam
                    </button>
                </div>
            </div>

            <!-- Questions -->
            <div class="space-y-6">
                <div v-for="(question, index) in questions" :key="question.id"
                     class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-4 mb-4">
                        <span class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg">
                            {{ index + 1 }}
                        </span>
                        <div class="flex-1">
                            <p class="text-lg font-medium text-slate-900 mb-2 leading-relaxed">{{ question.question }}</p>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 rounded-lg">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                    {{ question.marks }} {{ question.marks === 1 ? 'mark' : 'marks' }}
                                </span>
                                <span v-if="question.topic" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 rounded-lg">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ question.topic }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Answer Options -->
                    <div class="ml-14 space-y-2">
                        <label v-for="(option, i) in question.options" :key="i"
                               class="flex items-center p-4 border-2 border-slate-200 rounded-xl hover:bg-indigo-50 hover:border-indigo-300 cursor-pointer transition-all"
                               :class="{'bg-indigo-50 border-indigo-400': answers[question.id] === option}">
                            <input type="radio" :name="`question-${question.id}`" :value="option" v-model="answers[question.id]"
                                   class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 text-slate-800 font-medium">{{ String.fromCharCode(65 + i) }}. {{ option }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button at Bottom -->
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center sticky bottom-4">
                <button @click="confirmSubmit" :disabled="form.processing"
                        class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-lg font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="form.processing">Submitting Exam...</span>
                    <span v-else>Submit Exam</span>
                </button>
                <p class="text-sm text-slate-600 mt-3">Make sure you have answered all questions before submitting</p>
            </div>
        </div>
    </DashboardLayout>
</template>
