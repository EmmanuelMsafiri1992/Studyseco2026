<script setup>
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    game: Object,
    myScore: Object,
    leaderboard: Array,
    recentSessions: Array,
});

const startGame = () => {
    router.post(route('student.games.start', props.game.id));
};
</script>

<template>
    <DashboardLayout :title="game.name">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('student.games.index')"
                        class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                    >
                        ← Back to Games
                    </Link>
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                                {{ game.name }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ game.subject?.name }} | {{ game.game_type_label }}
                            </p>
                        </div>
                        <button
                            @click="startGame"
                            class="px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Start Game
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Game Description -->
                        <div class="bg-white shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">About This Game</h3>
                                <p class="text-sm text-gray-600">{{ game.description }}</p>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Maximum Score</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ game.max_score }}</p>
                                    </div>
                                    <div v-if="game.time_limit">
                                        <p class="text-sm text-gray-500">Time Limit</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ game.time_limit }}s</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leaderboard -->
                        <div class="bg-white shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Top Players</h3>
                                    <Link
                                        :href="route('student.games.leaderboard', game.id)"
                                        class="text-sm text-indigo-600 hover:text-indigo-900"
                                    >
                                        View Full Leaderboard →
                                    </Link>
                                </div>
                                <div v-if="leaderboard.length > 0" class="space-y-3">
                                    <div
                                        v-for="(entry, index) in leaderboard"
                                        :key="entry.id"
                                        class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <span
                                                :class="[
                                                    'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
                                                    index === 0 ? 'bg-yellow-100 text-yellow-800' :
                                                    index === 1 ? 'bg-gray-100 text-gray-800' :
                                                    index === 2 ? 'bg-orange-100 text-orange-800' :
                                                    'bg-gray-50 text-gray-600'
                                                ]"
                                            >
                                                {{ index + 1 }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ entry.student_name }}
                                            </span>
                                        </div>
                                        <span class="text-sm font-semibold text-indigo-600">
                                            {{ entry.high_score }}
                                        </span>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-500 text-center py-4">
                                    No scores yet. Be the first to play!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- My Stats -->
                        <div class="bg-white shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">My Stats</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500">High Score</p>
                                        <p class="text-2xl font-bold text-indigo-600">
                                            {{ myScore?.high_score || 0 }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Times Played</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ myScore?.total_plays || 0 }}
                                        </p>
                                    </div>
                                    <div v-if="myScore?.last_played_at">
                                        <p class="text-sm text-gray-500">Last Played</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ new Date(myScore.last_played_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Sessions -->
                        <div class="bg-white shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Sessions</h3>
                                <div v-if="recentSessions.length > 0" class="space-y-2">
                                    <div
                                        v-for="session in recentSessions"
                                        :key="session.id"
                                        class="flex justify-between text-sm py-2 border-b border-gray-200 last:border-0"
                                    >
                                        <span class="text-gray-500">
                                            {{ new Date(session.completed_at).toLocaleDateString() }}
                                        </span>
                                        <span class="font-semibold text-gray-900">
                                            {{ session.score }}
                                        </span>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-500 text-center py-2">
                                    No sessions yet
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
