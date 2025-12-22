<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    session: Object,
    game: Object,
    config: Object,
});

const score = ref(0);
const timeRemaining = ref(props.game.time_limit || 0);
const gameStarted = ref(false);
const timerInterval = ref(null);

const startGame = () => {
    gameStarted.value = true;
    if (props.game.time_limit) {
        timerInterval.value = setInterval(() => {
            timeRemaining.value--;
            if (timeRemaining.value <= 0) {
                submitScore();
            }
        }, 1000);
    }
};

const submitScore = () => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }

    router.post(route('student.games.submit', props.session.id), {
        score: score.value,
        session_data: {
            time_taken: props.game.time_limit ? props.game.time_limit - timeRemaining.value : 0,
        },
    });
};

onMounted(() => {
    startGame();
});

onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});
</script>

<template>
    <DashboardLayout :title="`Playing: ${game.name}`">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Game Header -->
                <div class="bg-white shadow sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ game.name }}</h2>
                                <p class="text-sm text-gray-500">{{ game.subject?.name }}</p>
                            </div>
                            <div class="flex items-center space-x-6">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Score</p>
                                    <p class="text-2xl font-bold text-indigo-600">{{ score }}</p>
                                </div>
                                <div v-if="game.time_limit" class="text-center">
                                    <p class="text-sm text-gray-500">Time Left</p>
                                    <p
                                        :class="[
                                            'text-2xl font-bold',
                                            timeRemaining <= 10 ? 'text-red-600' : 'text-gray-900'
                                        ]"
                                    >
                                        {{ timeRemaining }}s
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Game Area -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Game Placeholder - {{ game.game_type_label }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-6">
                                This is a placeholder for the actual game implementation.
                                In a real application, you would implement the specific game logic here
                                (trivia questions, math problems, word puzzles, or memory games).
                            </p>

                            <!-- Demo Score Controls -->
                            <div class="space-y-4">
                                <p class="text-sm text-gray-500">Demo: Click to increase score</p>
                                <button
                                    @click="score += 10"
                                    class="px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
                                >
                                    Answer Correct (+10 points)
                                </button>
                            </div>

                            <div class="mt-8">
                                <button
                                    @click="submitScore"
                                    class="px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Submit Score & Finish
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
