<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    games: Array,
});

const searchQuery = ref('');

const filteredGames = computed(() => {
    if (!searchQuery.value) return props.games;

    const query = searchQuery.value.toLowerCase();
    return props.games.filter(game =>
        game.name.toLowerCase().includes(query) ||
        game.description.toLowerCase().includes(query) ||
        game.subject?.name.toLowerCase().includes(query)
    );
});
</script>

<template>
    <DashboardLayout title="Educational Games">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Educational Games
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Practice and test your knowledge through fun educational games
                    </p>
                </div>

                <!-- Search -->
                <div class="mb-6">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search games..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>

                <!-- Games Grid -->
                <div v-if="filteredGames.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="game in filteredGames"
                        :key="game.id"
                        class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300"
                    >
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ game.name }}
                                </h3>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    {{ game.game_type_label }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 mb-4">
                                {{ game.subject?.name }}
                            </p>

                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ game.description }}
                            </p>

                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Your High Score:</span>
                                    <span class="font-semibold text-indigo-600">
                                        {{ game.my_high_score }} / {{ game.max_score }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Times Played:</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ game.my_total_plays }}
                                    </span>
                                </div>
                                <div v-if="game.time_limit" class="flex justify-between text-sm">
                                    <span class="text-gray-500">Time Limit:</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ game.time_limit }}s
                                    </span>
                                </div>
                            </div>

                            <Link
                                :href="route('student.games.show', game.id)"
                                class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Play Now
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-12 text-center">
                        <p class="text-gray-500">
                            {{ searchQuery ? 'No games found matching your search.' : 'No games available yet.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
