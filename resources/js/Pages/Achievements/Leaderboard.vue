<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    topStudents: Array,
    achievementTypes: Array,
    recentAchievements: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Active tab
const activeTab = ref('leaderboard');

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

const getTypeColor = (type) => {
    const colors = {
        academic: 'from-blue-500 to-indigo-600',
        sports: 'from-green-500 to-emerald-600',
        conduct: 'from-purple-500 to-violet-600',
        leadership: 'from-red-500 to-rose-600',
        extracurricular: 'from-yellow-500 to-orange-600'
    };
    return colors[type] || colors.academic;
};

const getRankBadge = (index) => {
    if (index === 0) return '🥇';
    if (index === 1) return '🥈';
    if (index === 2) return '🥉';
    return `#${index + 1}`;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Achievement Leaderboard" />
    
    <DashboardLayout title="Achievement Leaderboard" subtitle="Top performers and achievement statistics">
        <div class="space-y-6">
            <!-- Navigation Tabs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-2">
                <div class="flex space-x-2">
                    <button
                        @click="activeTab = 'leaderboard'"
                        :class="[
                            'flex-1 px-6 py-3 rounded-2xl font-semibold transition-all duration-200',
                            activeTab === 'leaderboard' 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' 
                                : 'text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Top Students
                    </button>
                    <button
                        @click="activeTab = 'categories'"
                        :class="[
                            'flex-1 px-6 py-3 rounded-2xl font-semibold transition-all duration-200',
                            activeTab === 'categories' 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' 
                                : 'text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Categories
                    </button>
                    <button
                        @click="activeTab = 'recent'"
                        :class="[
                            'flex-1 px-6 py-3 rounded-2xl font-semibold transition-all duration-200',
                            activeTab === 'recent' 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' 
                                : 'text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Recent Awards
                    </button>
                </div>
            </div>

            <!-- Top Students Tab -->
            <div v-if="activeTab === 'leaderboard'" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Top Achieving Students</h2>
                    <p class="text-sm text-slate-600 mt-1">Ranked by total achievement points</p>
                </div>

                <!-- Top 3 Podium -->
                <div class="p-8 bg-gradient-to-br from-slate-50 to-indigo-50">
                    <div class="flex justify-center items-end space-x-8 mb-8">
                        <!-- 2nd Place -->
                        <div v-if="topStudents[1]" class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-r from-slate-400 to-slate-500 rounded-full flex items-center justify-center text-2xl font-bold text-white mb-3 shadow-lg">
                                {{ topStudents[1].name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="text-4xl mb-2">🥈</div>
                            <h3 class="font-semibold text-slate-800">{{ topStudents[1].name }}</h3>
                            <p class="text-indigo-600 font-bold">{{ topStudents[1].achievements_sum_points?.toLocaleString() || 0 }} pts</p>
                            <p class="text-sm text-slate-500">{{ topStudents[1].achievements_count || 0 }} achievements</p>
                        </div>

                        <!-- 1st Place -->
                        <div v-if="topStudents[0]" class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center text-3xl font-bold text-white mb-3 shadow-xl transform scale-110">
                                {{ topStudents[0].name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="text-6xl mb-2">🥇</div>
                            <h3 class="font-bold text-slate-800 text-lg">{{ topStudents[0].name }}</h3>
                            <p class="text-indigo-600 font-bold text-xl">{{ topStudents[0].achievements_sum_points?.toLocaleString() || 0 }} pts</p>
                            <p class="text-sm text-slate-500">{{ topStudents[0].achievements_count || 0 }} achievements</p>
                        </div>

                        <!-- 3rd Place -->
                        <div v-if="topStudents[2]" class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-r from-amber-600 to-amber-700 rounded-full flex items-center justify-center text-2xl font-bold text-white mb-3 shadow-lg">
                                {{ topStudents[2].name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="text-4xl mb-2">🥉</div>
                            <h3 class="font-semibold text-slate-800">{{ topStudents[2].name }}</h3>
                            <p class="text-indigo-600 font-bold">{{ topStudents[2].achievements_sum_points?.toLocaleString() || 0 }} pts</p>
                            <p class="text-sm text-slate-500">{{ topStudents[2].achievements_count || 0 }} achievements</p>
                        </div>
                    </div>
                </div>

                <!-- Full Leaderboard -->
                <div class="p-6">
                    <div class="space-y-3">
                        <div 
                            v-for="(student, index) in topStudents" 
                            :key="student.id"
                            class="flex items-center p-4 bg-slate-50 rounded-2xl hover:bg-slate-100 transition-colors duration-200"
                        >
                            <!-- Rank -->
                            <div class="w-16 text-center">
                                <span v-if="index < 3" class="text-2xl">{{ getRankBadge(index) }}</span>
                                <span v-else class="text-lg font-semibold text-slate-600">#{{ index + 1 }}</span>
                            </div>

                            <!-- Student Info -->
                            <div class="flex items-center flex-1 min-w-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full flex items-center justify-center text-lg font-semibold text-white mr-4 flex-shrink-0">
                                    {{ student.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-800 truncate">{{ student.name }}</h3>
                                    <p class="text-sm text-slate-600">Student</p>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="text-right">
                                <p class="text-lg font-bold text-indigo-600">{{ student.achievements_sum_points?.toLocaleString() || 0 }}</p>
                                <p class="text-sm text-slate-500">{{ student.achievements_count || 0 }} achievements</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievement Categories Tab -->
            <div v-if="activeTab === 'categories'" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Achievement Categories</h2>
                    <p class="text-sm text-slate-600 mt-1">Distribution across different achievement types</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="category in achievementTypes" 
                            :key="category.type"
                            class="group bg-slate-50 rounded-2xl overflow-hidden hover:bg-slate-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1"
                        >
                            <!-- Category Header -->
                            <div :class="['h-32 bg-gradient-to-br', getTypeColor(category.type), 'flex items-center justify-center']">
                                <svg class="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getTypeIcon(category.type)"></path>
                                </svg>
                            </div>
                            
                            <!-- Category Content -->
                            <div class="p-6">
                                <h3 class="font-semibold text-slate-800 mb-4 capitalize">
                                    {{ category.type.replace('_', ' ') }}
                                </h3>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-indigo-600">{{ category.count }}</p>
                                        <p class="text-sm text-slate-600">Achievements</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-green-600">{{ category.total_points?.toLocaleString() || 0 }}</p>
                                        <p class="text-sm text-slate-600">Total Points</p>
                                    </div>
                                </div>

                                <!-- View Category Link -->
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <Link 
                                        :href="route('achievements.index', { type: category.type })"
                                        class="flex items-center justify-center text-indigo-600 hover:text-indigo-700 font-medium text-sm transition-colors duration-200"
                                    >
                                        View All
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Achievements Tab -->
            <div v-if="activeTab === 'recent'" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Recent Achievements</h2>
                    <p class="text-sm text-slate-600 mt-1">Latest awards and recognitions</p>
                </div>

                <div class="p-6">
                    <div v-if="recentAchievements.length === 0" class="text-center py-12">
                        <div class="w-24 h-24 bg-slate-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-slate-800 mb-2">No recent achievements</h3>
                        <p class="text-slate-600">Check back later for new awards!</p>
                    </div>

                    <div v-else class="space-y-4">
                        <Link
                            v-for="achievement in recentAchievements"
                            :key="achievement.id"
                            :href="route('achievements.show', achievement.id)"
                            class="group flex items-center p-4 bg-slate-50 rounded-2xl hover:bg-slate-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1"
                        >
                            <!-- Achievement Icon -->
                            <div :class="['w-16 h-16 rounded-xl bg-gradient-to-br', getLevelColor(achievement.level), 'flex items-center justify-center mr-4 flex-shrink-0']">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(achievement.type)"></path>
                                </svg>
                            </div>

                            <!-- Achievement Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors duration-200 mb-1">
                                    {{ achievement.title }}
                                </h3>
                                <div class="flex items-center gap-4 text-sm text-slate-600">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full flex items-center justify-center text-xs font-semibold text-white mr-2">
                                            {{ achievement.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span>{{ achievement.user.name }}</span>
                                    </div>
                                    <span class="capitalize">{{ achievement.type.replace('_', ' ') }}</span>
                                    <span class="capitalize">{{ achievement.level }}</span>
                                </div>
                            </div>

                            <!-- Date & Points -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm text-slate-500 mb-1">{{ formatDate(achievement.achieved_date) }}</p>
                                <div class="flex items-center text-indigo-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-medium">{{ achievement.points }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Back to Achievements Button -->
            <div class="flex justify-center">
                <Link
                    :href="route('achievements.index')"
                    class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                >
                    View All Achievements
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>