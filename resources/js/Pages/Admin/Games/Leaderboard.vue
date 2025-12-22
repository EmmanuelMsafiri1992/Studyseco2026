<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    game: Object,
    leaderboard: Array,
});
</script>

<template>
    <DashboardLayout :title="`Leaderboard - ${game.name}`">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.games.index')"
                        class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                    >
                        ← Back to Games
                    </Link>
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        {{ game.name }} - Leaderboard
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ game.subject?.name }} | {{ game.game_type_label }}
                    </p>
                </div>

                <!-- Leaderboard -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div v-if="leaderboard.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rank
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        High Score
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Plays
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Last Played
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(entry, index) in leaderboard" :key="entry.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
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
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ entry.student_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ entry.student_email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-indigo-600">
                                            {{ entry.high_score }} / {{ game.max_score }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ entry.total_plays }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(entry.last_played_at).toLocaleDateString() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-4 py-12 text-center">
                        <p class="text-gray-500">No scores yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
