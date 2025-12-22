<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    achievement: Object,
    related: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

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
        month: 'long',
        day: 'numeric'
    });
};

const formatDateRelative = (date) => {
    const now = new Date();
    const achievedDate = new Date(date);
    const diffTime = Math.abs(now - achievedDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return '1 day ago';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
};
</script>

<template>
    <Head :title="achievement.title" />
    
    <DashboardLayout :title="achievement.title" subtitle="Achievement Details">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Achievement Header -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div :class="['h-64 bg-gradient-to-br', getLevelColor(achievement.level), 'flex items-center justify-center relative']">
                    <!-- Featured Badge -->
                    <div v-if="achievement.is_featured" class="absolute top-6 right-6">
                        <div class="px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded-full shadow-lg">
                            ⭐ Featured Achievement
                        </div>
                    </div>
                    
                    <!-- Back Button -->
                    <div class="absolute top-6 left-6">
                        <Link :href="route('achievements.index')" class="p-3 bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 rounded-xl transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </Link>
                    </div>

                    <!-- Achievement Icon -->
                    <div class="text-center">
                        <svg class="w-32 h-32 text-white/80 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" :d="getTypeIcon(achievement.type)"></path>
                        </svg>
                        <div class="text-white text-lg font-semibold capitalize">
                            {{ achievement.level }} Level
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                        <div class="flex-1">
                            <h1 class="text-4xl font-bold text-slate-900 mb-6">{{ achievement.title }}</h1>
                            
                            <!-- Achievement Meta -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full flex items-center justify-center text-lg font-semibold text-white mr-4">
                                            {{ achievement.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ achievement.user.name }}</p>
                                            <p class="text-sm text-slate-600">Student</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center text-slate-600">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h.5a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h.5z"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium">{{ formatDate(achievement.achieved_date) }}</p>
                                            <p class="text-sm">{{ formatDateRelative(achievement.achieved_date) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center text-slate-600">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(achievement.type)"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium capitalize">{{ achievement.type.replace('_', ' ') }}</p>
                                            <p class="text-sm" v-if="achievement.category">{{ achievement.category }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center text-indigo-600">
                                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <p class="font-semibold">{{ achievement.points }} Points</p>
                                            <p class="text-sm text-slate-600">Achievement value</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="achievement.description" class="prose prose-slate max-w-none mb-8">
                                <h3 class="text-xl font-semibold text-slate-800 mb-3">About this Achievement</h3>
                                <p class="text-slate-700 leading-relaxed">{{ achievement.description }}</p>
                            </div>

                            <!-- Criteria Met -->
                            <div v-if="achievement.criteria_met && achievement.criteria_met.length > 0" class="mb-8">
                                <h3 class="text-xl font-semibold text-slate-800 mb-4">Achievement Criteria</h3>
                                <div class="bg-green-50 rounded-2xl p-6">
                                    <div class="space-y-3">
                                        <div v-for="(criteria, index) in achievement.criteria_met" :key="index" class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-slate-700">{{ criteria }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Awarding Notes -->
                            <div v-if="achievement.awarding_notes" class="bg-blue-50 rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-slate-800 mb-3">Recognition Notes</h3>
                                <p class="text-slate-700 leading-relaxed mb-4">{{ achievement.awarding_notes }}</p>
                                <div class="flex items-center text-sm text-slate-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Awarded by {{ achievement.awarded_by.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Achievements -->
            <div v-if="related.length > 0" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Related Achievements</h2>
                    <p class="text-sm text-slate-600 mt-1">Other achievements you might be interested in</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link 
                            v-for="item in related" 
                            :key="item.id"
                            :href="route('achievements.show', item.id)"
                            class="group bg-slate-50 rounded-2xl overflow-hidden hover:bg-slate-100 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1"
                        >
                            <!-- Achievement Header -->
                            <div :class="['h-24 bg-gradient-to-br', getLevelColor(item.level), 'flex items-center justify-center']">
                                <svg class="w-8 h-8 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(item.type)"></path>
                                </svg>
                            </div>
                            
                            <!-- Achievement Content -->
                            <div class="p-4">
                                <h3 class="font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors duration-200 mb-2 line-clamp-1">
                                    {{ item.title }}
                                </h3>
                                
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-6 h-6 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full flex items-center justify-center text-xs font-semibold text-white">
                                        {{ item.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-slate-600">{{ item.user.name }}</span>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500 capitalize">{{ item.level }}</span>
                                    <span class="text-indigo-600 font-medium">{{ item.points }} pts</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4">
                <Link
                    :href="route('achievements.index')"
                    class="px-8 py-4 bg-slate-200 text-slate-700 rounded-2xl font-semibold hover:bg-slate-300 transition-colors duration-200"
                >
                    Back to All Achievements
                </Link>
                
                <Link
                    :href="route('achievements.leaderboard')"
                    class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                >
                    View Leaderboard
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>