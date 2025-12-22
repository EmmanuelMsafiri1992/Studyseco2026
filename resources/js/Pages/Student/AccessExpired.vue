<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    enrollment: Object,
    extensionOptions: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const enrollment = props.enrollment;
const daysExpired = enrollment ? Math.abs(enrollment.access_days_remaining) : 0;

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Subject Access Expired - StudySeco" />
    
    <DashboardLayout title="Subject Access Expired" subtitle="Extend your enrollment to continue learning">
        <div class="max-w-4xl mx-auto space-y-8">
            
            <!-- Alert Banner -->
            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-3xl p-8 shadow-2xl">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold mb-2">Your Subject Access Has Expired</h1>
                        <p class="text-red-100">
                            Your access expired {{ daysExpired }} {{ daysExpired === 1 ? 'day' : 'days' }} ago on 
                            <span class="font-semibold">{{ formatDate(enrollment?.access_expires_at) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subject Information -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6">Your Enrollment Details</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-2">Enrollment Status</h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-slate-800 font-medium">Expired</span>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-2">Subjects Enrolled</h3>
                        <p class="text-slate-800 font-medium">{{ enrollment?.selected_subjects?.length || 0 }} subjects</p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-2">Original Access Period</h3>
                        <p class="text-slate-800 font-medium">{{ enrollment?.access_duration_months || 0 }} months</p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-2">Expired On</h3>
                        <p class="text-slate-800 font-medium">{{ formatDate(enrollment?.access_expires_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Extension Options -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-slate-800 mb-3">Continue Your Learning Journey</h2>
                    <p class="text-slate-600 text-lg">Choose an extension option to regain immediate access to all your subject materials</p>
                </div>

                <!-- Extension Plans -->
                <div class="grid md:grid-cols-3 gap-6 mb-8" v-if="extensionOptions?.length">
                    <div 
                        v-for="option in extensionOptions" 
                        :key="option.id"
                        class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-6 hover:border-blue-300 transition-all duration-200 hover:shadow-lg"
                    >
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-slate-800 mb-2">{{ option.duration_months }} Month{{ option.duration_months > 1 ? 's' : '' }}</h3>
                            <div class="text-3xl font-bold text-blue-600 mb-2">
                                {{ option.currency }} {{ option.price.toLocaleString() }}
                            </div>
                            <p class="text-sm text-slate-600 mb-4">{{ option.description }}</p>
                            
                            <Link 
                                :href="route('student.extension')" 
                                class="w-full inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                Select Plan
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- No Extension Options Available -->
                <div v-else class="text-center py-8">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">Extension Options Not Available</h3>
                    <p class="text-slate-600 mb-6">Please contact our support team to discuss extension options for your enrollment.</p>
                    
                    <Link 
                        href="/complaints/create" 
                        class="inline-flex items-center bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold py-3 px-6 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                        </svg>
                        Contact Support
                    </Link>
                </div>
            </div>

            <!-- What You'll Regain Access To -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-8">
                <h3 class="text-xl font-bold text-slate-800 mb-6">What You'll Regain Access To</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-1">Interactive Lessons</h4>
                            <p class="text-sm text-slate-600">Video lessons, assignments, and practice materials for all your subjects</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-1">Digital Library</h4>
                            <p class="text-sm text-slate-600">Access to textbooks, past papers, and reference materials</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-2-2V10a2 2 0 012-2h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-1">AI Tutor Support</h4>
                            <p class="text-sm text-slate-600">24/7 AI assistance and live tutoring sessions</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-1">Community Features</h4>
                            <p class="text-sm text-slate-600">Student forums, study groups, and peer collaboration</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Information -->
            <div class="bg-gradient-to-r from-slate-50 to-blue-50 rounded-2xl p-6 border border-slate-200/50">
                <div class="text-center">
                    <h4 class="font-semibold text-slate-800 mb-2">Need Help?</h4>
                    <p class="text-sm text-slate-600 mb-4">
                        Our support team is here to help you with extension options and payment methods
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link 
                            href="/complaints/create" 
                            class="inline-flex items-center justify-center bg-white text-slate-700 font-medium py-2 px-4 rounded-lg border border-slate-300 hover:bg-slate-50 transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                            </svg>
                            Contact Support
                        </Link>
                        <Link 
                            :href="route('dashboard')" 
                            class="inline-flex items-center justify-center bg-blue-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Back to Dashboard
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Add any custom styles if needed */
</style>