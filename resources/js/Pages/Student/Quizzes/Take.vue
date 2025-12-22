<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    attempt: Object,
    quiz: Object,
    questions: Array,
    timeRemaining: Number
});

const answers = ref({});
const timeLeft = ref(props.timeRemaining);
const timerInterval = ref(null);

const form = useForm({
    answers: []
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
            submitQuiz();
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

const submitQuiz = () => {
    const answersArray = Object.entries(answers.value).map(([questionId, answer]) => ({
        question_id: parseInt(questionId),
        answer: answer.toString()
    }));

    form.answers = answersArray;
    form.post(route('student.quizzes.submit', props.attempt.id));
};

const confirmSubmit = () => {
    if (confirm('Are you sure you want to submit? You cannot change your answers after submission.')) {
        submitQuiz();
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
    <Head :title="`Take Quiz: ${quiz.title}`" />

    <DashboardLayout :title="quiz.title" subtitle="Answer all questions">
        <div class="max-w-4xl mx-auto">
            <!-- Timer Banner -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6 sticky top-4 z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Time Remaining</p>
                        <p :class="['text-2xl font-bold', timeLeftClass]">{{ formatTime(timeLeft) }}</p>
                    </div>
                    <div class="flex-1 mx-6">
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div :class="['h-2 rounded-full transition-all', timeLeftPercentage > 25 ? 'bg-green-600' : 'bg-red-600']"
                                 :style="`width: ${timeLeftPercentage}%`"></div>
                        </div>
                    </div>
                    <button @click="confirmSubmit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Submit Quiz
                    </button>
                </div>
            </div>

            <!-- Questions -->
            <div class="space-y-6">
                <div v-for="(question, index) in questions" :key="question.id"
                     class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-medium text-sm">
                            {{ index + 1 }}
                        </span>
                        <div class="flex-1">
                            <p class="text-lg font-medium text-slate-900 mb-1">{{ question.question }}</p>
                            <p class="text-sm text-slate-500">{{ question.points }} {{ question.points === 1 ? 'point' : 'points' }}</p>
                        </div>
                    </div>

                    <!-- Multiple Choice -->
                    <div v-if="question.type === 'multiple_choice'" class="ml-11 space-y-2">
                        <label v-for="(option, i) in question.options" :key="i"
                               class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="radio" :name="`question-${question.id}`" :value="option" v-model="answers[question.id]"
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 text-slate-700">{{ String.fromCharCode(65 + i) }}. {{ option }}</span>
                        </label>
                    </div>

                    <!-- True/False -->
                    <div v-else-if="question.type === 'true_false'" class="ml-11 space-y-2">
                        <label class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="radio" :name="`question-${question.id}`" value="True" v-model="answers[question.id]"
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 text-slate-700">True</span>
                        </label>
                        <label class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="radio" :name="`question-${question.id}`" value="False" v-model="answers[question.id]"
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 text-slate-700">False</span>
                        </label>
                    </div>

                    <!-- Short Answer -->
                    <div v-else-if="question.type === 'short_answer'" class="ml-11">
                        <input type="text" v-model="answers[question.id]"
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Enter your answer...">
                    </div>
                </div>
            </div>

            <!-- Submit Button (Bottom) -->
            <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">
                        Make sure you've answered all questions before submitting.
                    </p>
                    <button @click="confirmSubmit" :disabled="form.processing"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ form.processing ? 'Submitting...' : 'Submit Quiz' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
