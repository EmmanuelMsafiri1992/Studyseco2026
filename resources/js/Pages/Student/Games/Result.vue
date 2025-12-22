<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    session: Object,
    game: Object,
    myScore: Object,
    rank: Number,
    isNewHighScore: Boolean,
});

const getPercentage = () => {
    return Math.round((props.session.score / props.game.max_score) * 100);
};

const getGrade = () => {
    const percentage = getPercentage();
    if (percentage >= 90) return 'Excellent!';
    if (percentage >= 80) return 'Great Job!';
    if (percentage >= 70) return 'Good Work!';
    if (percentage >= 60) return 'Keep Practicing!';
    return 'Try Again!';
};
</script>

<template>
    <DashboardLayout :title="`Results - ${game.name}`">
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Results Card -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <!-- New High Score Badge -->
                        <div v-if="isNewHighScore" class="mb-6 text-center">
                            <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-100 text-yellow-800">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="font-semibold">New High Score!</span>
                            </div>
                        </div>

                        <!-- Score Display -->
                        <div class="text-center mb-6">
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                                {{ getGrade() }}
                            </h2>
                            <div class="text-6xl font-bold text-indigo-600 mb-2">
                                {{ session.score }}
                            </div>
                            <p class="text-lg text-gray-500">
                                out of {{ game.max_score }} points ({{ getPercentage() }}%)
                            </p>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-500 mb-1">Your Rank</p>
                                <p class="text-2xl font-bold text-gray-900">#{{ rank }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-500 mb-1">High Score</p>
                                <p class="text-2xl font-bold text-indigo-600">
                                    {{ myScore?.high_score || session.score }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-500 mb-1">Time Taken</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ session.duration_seconds }}s
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-500 mb-1">Total Plays</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ myScore?.total_plays || 1 }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <Link
                                :href="route('student.games.show', game.id)"
                                class="flex-1 text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                            >
                                Play Again
                            </Link>
                            <Link
                                :href="route('student.games.leaderboard', game.id)"
                                class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                View Leaderboard
                            </Link>
                            <Link
                                :href="route('student.games.index')"
                                class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                All Games
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
