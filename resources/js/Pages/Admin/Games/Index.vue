<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
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

const deleteGame = (game) => {
    if (confirm(`Are you sure you want to delete "${game.name}"?`)) {
        router.delete(route('admin.games.destroy', game.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <DashboardLayout title="Games Management">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="md:flex md:items-center md:justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Games Management
                        </h2>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <Link
                            :href="route('admin.games.create')"
                            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Create New Game
                        </Link>
                    </div>
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

                <!-- Games List -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul v-if="filteredGames.length > 0" role="list" class="divide-y divide-gray-200">
                        <li v-for="game in filteredGames" :key="game.id">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-lg font-medium text-indigo-600 truncate">
                                                {{ game.name }}
                                            </p>
                                            <div class="ml-2 flex-shrink-0 flex">
                                                <p :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    game.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800'
                                                ]">
                                                    {{ game.is_active ? 'Active' : 'Inactive' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-2 sm:flex sm:justify-between">
                                            <div class="sm:flex">
                                                <p class="flex items-center text-sm text-gray-500">
                                                    <span class="font-medium mr-1">Subject:</span>
                                                    {{ game.subject?.name || 'N/A' }}
                                                </p>
                                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                    <span class="font-medium mr-1">Type:</span>
                                                    {{ game.game_type_label }}
                                                </p>
                                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                    <span class="font-medium mr-1">Max Score:</span>
                                                    {{ game.max_score }}
                                                </p>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-600">
                                            {{ game.description }}
                                        </p>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <span class="font-medium mr-2">Stats:</span>
                                            {{ game.total_plays }} plays | Avg Score: {{ game.average_score.toFixed(1) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-3">
                                    <Link
                                        :href="route('admin.games.edit', game.id)"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        :href="route('admin.games.leaderboard', game.id)"
                                        class="text-sm font-medium text-green-600 hover:text-green-900"
                                    >
                                        Leaderboard
                                    </Link>
                                    <button
                                        @click="deleteGame(game)"
                                        class="text-sm font-medium text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="px-4 py-12 text-center">
                        <p class="text-gray-500">No games found.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
