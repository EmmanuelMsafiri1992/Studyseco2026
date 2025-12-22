<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    enrollment: Object,
    enrollmentDetails: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const enrollment = props.enrollment || null;
const details = props.enrollmentDetails || null;
</script>

<template>
    <Head title="Enrollment Details" />
    
    <DashboardLayout title="Enrollment Details" subtitle="View your enrollment information">
        <div class="space-y-8">
            <!-- Back Button -->
            <div class="flex items-center space-x-4">
                <Link href="/student/enrollment" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Enrollment
                </Link>
            </div>

            <!-- Enrollment Information -->
            <div v-if="details" class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-slate-800">Enrollment Information</h2>
                    <div :class="{
                        'bg-green-100 text-green-800': details.status === 'approved' || details.is_trial,
                        'bg-yellow-100 text-yellow-800': details.status === 'pending',
                        'bg-red-100 text-red-800': details.status === 'rejected',
                        'bg-blue-100 text-blue-800': details.is_trial
                    }" class="px-4 py-2 rounded-full text-sm font-medium">
                        {{ details.is_trial ? '🆓 Free Trial' : details.status.charAt(0).toUpperCase() + details.status.slice(1) }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Basic Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Enrollment ID</label>
                                <p class="text-slate-800 font-mono bg-slate-50 px-3 py-2 rounded-lg">#{{ details.id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Enrolled On</label>
                                <p class="text-slate-800">{{ new Date(details.created_at).toLocaleDateString() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Account Type</label>
                                <p class="text-slate-800">Premium {{ details.is_trial ? '(7 Days Free Trial)' : '(Full Access)' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Total Subjects</label>
                                <p class="text-slate-800">{{ details.subjects?.length || 0 }} subjects enrolled</p>
                            </div>
                        </div>
                    </div>

                    <!-- Access Information -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Access Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Access Status</label>
                                <div class="flex items-center space-x-2">
                                    <div :class="{
                                        'w-3 h-3 rounded-full bg-green-500': !details.is_access_expired,
                                        'w-3 h-3 rounded-full bg-red-500': details.is_access_expired
                                    }"></div>
                                    <p class="text-slate-800">{{ details.is_access_expired ? 'Expired' : 'Active' }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Access Expires</label>
                                <p class="text-slate-800">
                                    {{ details.access_expires_at ? new Date(details.access_expires_at).toLocaleDateString() : 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Days Remaining</label>
                                <p class="text-slate-800 text-2xl font-bold" :class="{
                                    'text-green-600': details.access_days_remaining > 7,
                                    'text-amber-600': details.access_days_remaining <= 7 && details.access_days_remaining > 0,
                                    'text-red-600': details.access_days_remaining <= 0
                                }">
                                    {{ details.access_days_remaining || 0 }}
                                </p>
                            </div>

                            <div v-if="details.access_days_remaining <= 0" class="mt-4">
                                <Link href="/student/extension" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white font-medium rounded-xl hover:bg-amber-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Extend Access
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrolled Subjects -->
                <div v-if="details.subjects && details.subjects.length > 0" class="mt-8 pt-8 border-t border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Enrolled Subjects</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="subjectId in details.subjects" :key="subjectId" 
                             class="bg-slate-50 rounded-2xl p-4 flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">Subject ID: {{ subjectId }}</p>
                                <p class="text-sm text-slate-600">Enrolled</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-4 mt-8 pt-8 border-t border-slate-200">
                    <Link v-if="!details.is_access_expired" href="/student/subjects" 
                          class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-2xl hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        View My Subjects
                    </Link>

                    <Link href="/student/extension" 
                          class="inline-flex items-center px-6 py-3 bg-amber-600 text-white font-medium rounded-2xl hover:bg-amber-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Extend Access
                    </Link>

                    <Link href="/profile" 
                          class="inline-flex items-center px-6 py-3 bg-slate-600 text-white font-medium rounded-2xl hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile Settings
                    </Link>
                </div>
            </div>

            <!-- No Enrollment Found -->
            <div v-else class="bg-white/80 backdrop-blur-sm rounded-3xl p-12 shadow-xl border border-slate-200/50 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-4">No Enrollment Found</h3>
                <p class="text-slate-600 mb-8">You don't have any active enrollment. Start your learning journey today!</p>
                <Link href="/#enrollment" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-2xl hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Enroll Now
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>