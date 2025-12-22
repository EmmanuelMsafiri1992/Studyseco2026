<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    stats: Object,
    activeStudents: Array,
    expiredStudents: Array,
    recentActivities: Array,
    teacher: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDateShort = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

const getProgressPercentage = (enrollment) => {
    // Mock calculation - in real app you'd calculate based on actual progress
    return Math.round(Math.random() * 80 + 10);
};

const getStudentName = (enrollment) => {
    return enrollment.user?.name || enrollment.name || 'Unknown Student';
};

const getDaysUntilExpiry = (enrollment) => {
    const now = new Date();
    const expiry = new Date(enrollment.access_expires_at);
    const diffTime = expiry - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const getExpiryStatus = (enrollment) => {
    const days = getDaysUntilExpiry(enrollment);
    if (days < 0) return { text: 'Expired', color: 'red' };
    if (days <= 7) return { text: `${days} days left`, color: 'yellow' };
    return { text: `${days} days left`, color: 'green' };
};
</script>

<template>
    <Head title="Teacher Dashboard" />
    
    <DashboardLayout :title="`Welcome back, ${teacher.name}`" subtitle="Manage your assigned students and tutoring sessions">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Students</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total_assigned }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">👨‍🎓</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Students</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.active_students }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">✅</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Expired Access</p>
                        <p class="text-2xl font-bold text-red-600">{{ stats.expired_students }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">⏰</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Trial Students</p>
                        <p class="text-2xl font-bold text-orange-600">{{ stats.trial_students }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">🆓</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">New This Week</p>
                        <p class="text-2xl font-bold text-purple-600">{{ stats.recent_assignments }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">📈</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Subjects</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.subjects_covered }}</p>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">📚</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Active Students -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Active Students</h3>
                        <Link :href="route('teacher.students')" class="text-sm text-indigo-600 hover:text-indigo-800">
                            View All
                        </Link>
                    </div>
                    
                    <div v-if="activeStudents.length === 0" class="p-6 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-2xl">👨‍🎓</span>
                        </div>
                        <h4 class="text-lg font-medium text-slate-800 mb-2">No Active Students</h4>
                        <p class="text-slate-600">You don't have any active students assigned yet.</p>
                    </div>
                    
                    <div v-else class="divide-y divide-slate-200">
                        <div v-for="student in activeStudents.slice(0, 6)" :key="student.id" 
                             class="p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {{ getStudentName(student).charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900">{{ getStudentName(student) }}</div>
                                        <div class="text-sm text-slate-500">{{ student.email }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div :class="[
                                        'text-xs font-medium px-2 py-1 rounded-full',
                                        getExpiryStatus(student).color === 'green' ? 'bg-green-100 text-green-800' :
                                        getExpiryStatus(student).color === 'yellow' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-red-100 text-red-800'
                                    ]">
                                        {{ getExpiryStatus(student).text }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ student.selected_subjects?.length || 0 }} subjects
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Recent Activities</h3>
                    </div>
                    
                    <div class="divide-y divide-slate-200">
                        <div v-for="activity in recentActivities" :key="activity.id" 
                             class="p-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-sm flex-shrink-0">
                                    {{ activity.icon }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-900">{{ activity.message }}</p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ activity.student }} • {{ formatDate(activity.timestamp) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Quick Actions</h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <Link :href="route('teacher.students')" 
                              class="w-full flex items-center justify-center px-4 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            View All Students
                        </Link>
                        
                        <button class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h.5a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h.5z"></path>
                            </svg>
                            Schedule Session
                        </button>
                        
                        <button class="w-full flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                            </svg>
                            Send Message
                        </button>
                        
                        <Link href="/ai-training" 
                              class="w-full flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                            </svg>
                            Upload Materials
                        </Link>
                    </div>
                </div>
            </div>

        </div>

        <!-- Expired Students (if any) -->
        <div v-if="expiredStudents.length > 0" class="mt-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                        <span class="text-red-500 mr-2">⚠️</span>
                        Students with Expired Access
                    </h3>
                </div>
                
                <div class="divide-y divide-slate-200">
                    <div v-for="student in expiredStudents.slice(0, 3)" :key="student.id" 
                         class="p-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-red-400 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ getStudentName(student).charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900">{{ getStudentName(student) }}</div>
                                    <div class="text-sm text-slate-500">{{ student.email }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-800">
                                    Expired {{ Math.abs(getDaysUntilExpiry(student)) }} days ago
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ student.selected_subjects?.length || 0 }} subjects
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<style scoped>
/* Add any custom styles if needed */
</style>