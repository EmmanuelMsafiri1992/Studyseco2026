<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    game: Object,
    leaderboard: Array,
    myScore: Object,
    myRank: Number,
});
</script>

<template>
    <DashboardLayout :title="`Leaderboard - ${game.name}`">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('student.games.show', game.id)"
                        class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                    >
                        ← Back to Game
                    </Link>
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        {{ game.name }} - Leaderboard
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ game.subject?.name }} | {{ game.game_type_label }}
                    </p>
                </div>

                <!-- My Rank Card -->
                <div v-if="myScore" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-indigo-600 font-medium">Your Rank</p>
                            <p class="text-2xl font-bold text-indigo-900">#{{ myRank }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-indigo-600 font-medium">Your High Score</p>
                            <p class="text-2xl font-bold text-indigo-900">
                                {{ myScore.high_score }} / {{ game.max_score }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div v-if="leaderboard.length > 0">
                        <ul class="divide-y divide-gray-200">
                            <li
                                v-for="(entry, index) in leaderboard"
                                :key="entry.id"
                                :class="[
                                    'px-6 py-4',
                                    myScore && entry.student_id === myScore.student_id ? 'bg-indigo-50' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold',
                                                index === 0 ? 'bg-yellow-100 text-yellow-800' :
                                                index === 1 ? 'bg-gray-100 text-gray-800' :
                                                index === 2 ? 'bg-orange-100 text-orange-800' :
                                                'bg-gray-50 text-gray-600'
                                            ]"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ entry.student_name }}
                                                <span
                                                    v-if="myScore && entry.student_id === myScore.student_id"
                                                    class="ml-2 text-xs text-indigo-600 font-semibold"
                                                >
                                                    (You)
                                                </span>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ entry.total_plays }} {{ entry.total_plays === 1 ? 'play' : 'plays' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-indigo-600">
                                            {{ entry.high_score }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ Math.round((entry.high_score / game.max_score) * 100) }}%
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div v-else class="px-4 py-12 text-center">
                        <p class="text-gray-500">No scores yet. Be the first to play!</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex gap-3">
                    <Link
                        :href="route('student.games.show', game.id)"
                        class="flex-1 text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        Play Game
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
    </DashboardLayout>
</template>
