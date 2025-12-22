<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    achievements: Object,
    stats: Object,
    types: Array,
    levels: Array,
    filters: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Filter state
const selectedType = ref(props.filters.type || '');
const selectedLevel = ref(props.filters.level || '');
const searchQuery = ref(props.filters.search || '');

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();
    if (selectedType.value) params.set('type', selectedType.value);
    if (selectedLevel.value) params.set('level', selectedLevel.value);
    if (searchQuery.value) params.set('search', searchQuery.value);
    
    router.get(route('achievements.index'), Object.fromEntries(params));
};

const resetFilters = () => {
    selectedType.value = '';
    selectedLevel.value = '';
    searchQuery.value = '';
    router.get(route('achievements.index'));
};

// Utility functions
const getTypeIcon = (type) => {
    const icons = {
        academic: 'M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197',
        sports: 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0M15 17a2 2 0 104 0',
        conduct: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        leadership: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        extracurricular: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'
    };
    return icons[type] || icons.extracurricular;
};

const getLevelColor = (level) => {
    const colors = {
        bronze: 'from-amber-600 to-yellow-600',
        silver: 'from-slate-400 to-slate-600', 
        gold: 'from-yellow-400 to-yellow-600',
        platinum: 'from-purple-400 to-indigo-600'
    };
    return colors[level] || 'from-blue-400 to-blue-600';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Student Achievements" />
    
    <DashboardLayout title="Student Achievements" subtitle="Celebrating academic excellence and personal growth">
        <div class="space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ stats.total_achievements.toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">Total Achievements</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ stats.featured_achievements.toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">Featured</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ stats.total_points.toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">Total Points</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ stats.recent_achievements.toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">This Week</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search achievements..."
                            class="pl-11 pr-4 py-3 bg-white/80 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-full sm:w-80"
                        />
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Type Filter -->
                    <select
                        v-model="selectedType"
                        @change="applyFilters"
                        class="px-4 py-3 bg-white/80 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option value="">All Types</option>
                        <option v-for="type in types" :key="type" :value="type" class="capitalize">
                            {{ type.replace('_', ' ') }}
                        </option>
                    </select>

                    <!-- Level Filter -->
                    <select
                        v-model="selectedLevel"
                        @change="applyFilters"
                        class="px-4 py-3 bg-white/80 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option value="">All Levels</option>
                        <option v-for="level in levels" :key="level" :value="level" class="capitalize">
                            {{ level }}
                        </option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="resetFilters"
                        class="px-4 py-3 bg-slate-200 text-slate-700 rounded-2xl hover:bg-slate-300 transition-colors duration-200"
                    >
                        Reset Filters
                    </button>
                    
                    <Link
                        :href="route('achievements.leaderboard')"
                        class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    >
                        View Leaderboard
                    </Link>
                </div>
            </div>

            <!-- Achievements Grid -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Achievement Gallery</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ achievements.total }} achievements found</p>
                </div>

                <div v-if="achievements.data.length === 0" class="p-12 text-center">
                    <div class="w-24 h-24 bg-slate-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-800 mb-2">No achievements found</h3>
                    <p class="text-slate-600">Try adjusting your filters or search terms.</p>
                </div>

                <div v-else class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="achievement in achievements.data"
                            :key="achievement.id"
                            :href="route('achievements.show', achievement.id)"
                            class="group relative bg-slate-50 rounded-2xl overflow-hidden hover:bg-slate-100 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1"
                        >
                            <!-- Featured Badge -->
                            <div v-if="achievement.is_featured" class="absolute top-3 right-3 z-10">
                                <div class="px-2 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-full">
                                    Featured
                                </div>
                            </div>

                            <!-- Achievement Header -->
                            <div :class="['h-32 bg-gradient-to-br', getLevelColor(achievement.level), 'flex items-center justify-center relative']">
                                <svg class="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getTypeIcon(achievement.type)"></path>
                                </svg>
                                <div class="absolute bottom-2 left-3 text-white text-xs font-semibold capitalize">
                                    {{ achievement.level }}
                                </div>
                            </div>

                            <!-- Achievement Content -->
                            <div class="p-5">
                                <h3 class="font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors duration-200 mb-2 line-clamp-2">
                                    {{ achievement.title }}
                                </h3>
                                
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                        {{ achievement.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-slate-600">{{ achievement.user.name }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500 capitalize">{{ achievement.type.replace('_', ' ') }}</span>
                                    <div class="flex items-center text-slate-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h.5a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h.5z"></path>
                                        </svg>
                                        <span>{{ formatDate(achievement.achieved_date) }}</span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                                    <div class="flex items-center text-indigo-600">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm font-medium">{{ achievement.points }} points</span>
                                    </div>
                                    
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="achievements.last_page > 1" class="mt-8 flex justify-center">
                        <nav class="flex items-center space-x-2">
                            <Link
                                v-if="achievements.prev_page_url"
                                :href="achievements.prev_page_url"
                                class="p-3 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </Link>

                            <span class="px-4 py-2 text-sm text-slate-600">
                                Page {{ achievements.current_page }} of {{ achievements.last_page }}
                            </span>

                            <Link
                                v-if="achievements.next_page_url"
                                :href="achievements.next_page_url"
                                class="p-3 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>