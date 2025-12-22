<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    enrollment: Object,
    availableSubjects: Array,
    accessDurations: Array,
    enrollmentStatus: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const enrollmentData = props.enrollment || null;
const subjects = props.availableSubjects || [];
const status = props.enrollmentStatus || null;

// Form handling
const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: '',
    location: '',
    subjects: [],
    access_duration_id: 'trial',
});

const submitEnrollment = () => {
    form.post('/enroll', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect will be handled by the backend
        },
        onError: (errors) => {
            console.error('Enrollment errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Student Enrollment" />
    
    <DashboardLayout title="Enrollment Management" subtitle="Manage your subject enrollment">
        <div class="space-y-8">
            <!-- Enrollment Status Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Enrollment Status</h2>
                    <div class="flex items-center space-x-4">
                        <div v-if="status" :class="{
                            'bg-green-100 text-green-800': status.status === 'approved' || enrollmentData?.is_trial,
                            'bg-yellow-100 text-yellow-800': status.status === 'pending',
                            'bg-red-100 text-red-800': status.status === 'rejected',
                            'bg-blue-100 text-blue-800': enrollmentData?.is_trial
                        }" class="px-4 py-2 rounded-full text-sm font-medium">
                            {{ enrollmentData?.is_trial ? '🆓 Free Trial' : status.status.charAt(0).toUpperCase() + status.status.slice(1) }}
                        </div>
                    </div>
                </div>

                <div v-if="enrollmentData" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-slate-50 rounded-2xl p-6">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Subjects Enrolled</h3>
                        </div>
                        <p class="text-3xl font-bold text-slate-800 mb-2">{{ status?.subjects_count || 0 }}</p>
                        <p class="text-sm text-slate-600">Active subjects</p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-6">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Access Remaining</h3>
                        </div>
                        <p class="text-3xl font-bold text-slate-800 mb-2">{{ status?.access_days_remaining || 0 }}</p>
                        <p class="text-sm text-slate-600">Days left</p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-6">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Account Type</h3>
                        </div>
                        <p class="text-3xl font-bold text-slate-800 mb-2">{{ enrollmentData?.is_trial ? 'Free Trial' : 'Premium' }}</p>
                        <p class="text-sm text-slate-600">{{ enrollmentData?.is_trial ? '7 Days Free Trial' : 'Full Access' }}</p>
                    </div>
                </div>

                <div v-else class="space-y-8">
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-800 mb-4">No Active Enrollment</h3>
                        <p class="text-slate-600 mb-8">You haven't enrolled in any subjects yet. Start your learning journey today!</p>
                    </div>

                    <!-- Enrollment Form -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl p-8 border border-indigo-100">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-slate-800 mb-4">🎓 Start Your Learning Journey</h2>
                            <p class="text-slate-600">Choose your subjects and access duration to get started</p>
                        </div>

                        <form @submit.prevent="submitEnrollment" class="space-y-6">
                            <!-- Personal Information -->
                            <div class="bg-white rounded-2xl p-6 space-y-4">
                                <h3 class="text-lg font-semibold text-slate-800 mb-4">📝 Personal Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                                        <input 
                                            type="text" 
                                            name="name" 
                                            v-model="form.name"
                                            required
                                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Your full name"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            v-model="form.email"
                                            required
                                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="your.email@example.com"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                                        <input 
                                            type="tel" 
                                            name="phone" 
                                            v-model="form.phone"
                                            required
                                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="+265 xxx xxx xxx"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Location</label>
                                        <input 
                                            type="text" 
                                            name="location" 
                                            v-model="form.location"
                                            required
                                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="City, Country"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Selection -->
                            <div class="bg-white rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-slate-800 mb-4">📚 Choose Your Subjects</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div 
                                        v-for="subject in subjects" 
                                        :key="subject.id"
                                        class="relative"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :id="`subject-${subject.id}`" 
                                            :value="subject.id"
                                            v-model="form.subjects"
                                            class="sr-only peer"
                                        >
                                        <label 
                                            :for="`subject-${subject.id}`" 
                                            class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all duration-200"
                                        >
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <span class="text-2xl">{{ subject.icon }}</span>
                                                    <div>
                                                        <h4 class="font-medium text-slate-800">{{ subject.name }}</h4>
                                                        <p class="text-xs text-slate-500">{{ subject.department }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="w-5 h-5 border-2 border-slate-300 rounded peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="flex items-center space-x-2 text-blue-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-sm font-medium">Select at least one subject to continue</p>
                                    </div>
                                </div>

                                <!-- Subjects Error -->
                                <div v-if="form.errors.subjects" class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                                    <p class="text-sm text-red-600">{{ form.errors.subjects }}</p>
                                </div>
                            </div>

                            <!-- Access Duration -->
                            <div class="bg-white rounded-2xl p-6">
                                <h3 class="text-lg font-semibold text-slate-800 mb-4">⏰ Choose Access Duration</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <!-- Free Trial Option -->
                                    <div class="relative">
                                        <input 
                                            type="radio" 
                                            id="trial-option" 
                                            value="trial"
                                            v-model="form.access_duration_id"
                                            class="sr-only peer"
                                        >
                                        <label 
                                            for="trial-option" 
                                            class="block p-6 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-green-300 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all duration-200"
                                        >
                                            <div class="text-center">
                                                <div class="text-3xl mb-2">🆓</div>
                                                <h4 class="font-bold text-slate-800 mb-2">Free Trial</h4>
                                                <p class="text-2xl font-bold text-green-600 mb-1">7 Days</p>
                                                <p class="text-sm text-slate-600">Perfect for trying out</p>
                                                <div class="mt-3 px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full inline-block">
                                                    Most Popular
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Paid Options -->
                                    <div 
                                        v-for="duration in accessDurations" 
                                        :key="duration.id"
                                        class="relative"
                                    >
                                        <input 
                                            type="radio" 
                                            :id="`duration-${duration.id}`" 
                                            :value="duration.id"
                                            v-model="form.access_duration_id"
                                            class="sr-only peer"
                                        >
                                        <label 
                                            :for="`duration-${duration.id}`" 
                                            class="block p-6 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all duration-200"
                                        >
                                            <div class="text-center">
                                                <div class="text-3xl mb-2">💎</div>
                                                <h4 class="font-bold text-slate-800 mb-2">{{ duration.name }}</h4>
                                                <p class="text-2xl font-bold text-indigo-600 mb-1">MWK {{ duration.price?.toLocaleString() }}</p>
                                                <p class="text-sm text-slate-600">{{ duration.description }}</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex flex-col space-y-4 pt-6">
                                <button 
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                >
                                    <div class="flex items-center justify-center space-x-2">
                                        <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <span>{{ form.processing ? 'Processing...' : 'Start Learning Journey' }}</span>
                                    </div>
                                </button>
                                
                                <p class="text-center text-sm text-slate-500">
                                    ✅ Start with 7 days free trial • ✅ No commitment required • ✅ Cancel anytime
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div v-if="enrollmentData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Link href="/student/subjects" class="block group">
                        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors">My Subjects</h3>
                                    <p class="text-sm text-slate-600">View enrolled subjects</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link :href="enrollmentData?.is_trial ? '/account-settings' : '/student/extension'" class="block group">
                        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-800 group-hover:text-amber-600 transition-colors">
                                        {{ enrollmentData?.is_trial ? 'Upgrade Account' : 'Extend Access' }}
                                    </h3>
                                    <p class="text-sm text-slate-600">
                                        {{ enrollmentData?.is_trial ? 'Upgrade to Premium first' : 'Extend your access period' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Add More Subjects - Always show but with different behavior -->
                    <Link v-if="enrollmentData && !enrollmentData?.is_trial && status?.status === 'approved'" 
                          :href="route('student.enrollment.increase-subjects')" 
                          class="block group">
                        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">Add More Subjects</h3>
                                    <p class="text-sm text-slate-600">Expand your learning</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Show disabled version if conditions not met -->
                    <div v-else-if="enrollmentData" class="block cursor-not-allowed opacity-60">
                        <div class="bg-gray-100 rounded-3xl p-6 shadow-xl border border-gray-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gray-400 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-600">Add More Subjects</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ enrollmentData?.is_trial ? 'Upgrade to premium first' : 
                                           status?.status !== 'approved' ? 'Enrollment needs approval' : 'Available for premium users' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Link href="/profile" class="block group">
                        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-800 group-hover:text-rose-600 transition-colors">Profile Settings</h3>
                                    <p class="text-sm text-slate-600">Update your information</p>
                                </div>
                            </div>
                        </div>
                    </Link>
            </div>
        </div>
    </DashboardLayout>
</template>