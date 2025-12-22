<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Filler } from 'chart.js';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

// Define the component's props and get the user object
const props = defineProps({
    auth: Object,
    stats: Object,
    recent_activities: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };
const stats = props.stats || {};

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Filler);

// Chart data
const enrollmentData = ref({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'New Enrollments',
        data: [12, 19, 15, 25, 22, 30],
        backgroundColor: 'rgba(99, 102, 241, 0.8)',
        borderColor: 'rgb(99, 102, 241)',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
    }]
});

const performanceData = ref({
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [{
        label: 'Average Score',
        data: [75, 82, 78, 85],
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderColor: 'rgb(16, 185, 129)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: 'rgb(16, 185, 129)',
        pointBorderColor: '#fff',
        pointBorderWidth: 3,
        pointRadius: 6,
    }]
});

const subjectDistribution = ref({
    labels: ['Mathematics', 'English', 'Science', 'History', 'Others'],
    datasets: [{
        data: [25, 20, 20, 15, 20],
        backgroundColor: [
            'rgba(99, 102, 241, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)'
        ],
        borderWidth: 0,
    }]
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            labels: {
                font: {
                    family: 'Inter, system-ui, sans-serif',
                    size: 12
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
});

const doughnutOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                usePointStyle: true,
                font: {
                    family: 'Inter, system-ui, sans-serif',
                    size: 12
                }
            }
        }
    },
    cutout: '60%'
});

const lineOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            max: 100,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
                callback: function(value) {
                    return value + '%';
                }
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    },
    elements: {
        point: {
            hoverRadius: 8
        }
    }
});

// Navigation methods
const navigateToEnrollment = () => {
    // Check enrollment status and navigate accordingly
    const enrollment = props.auth?.enrollment || null;
    
    if (!enrollment || props.stats?.enrollment_status === 'not_enrolled') {
        // Navigate to student enrollment management
        window.location.href = '/student/enrollment';
    } else if (props.stats?.enrollment_status === 'pending') {
        // Show enrollment details
        window.location.href = '/student/enrollment/details';
    } else {
        // Show enrollment details for approved/trial users
        window.location.href = '/student/enrollment/details';
    }
};

const navigateToSubjects = () => {
    const hasAccess = props.stats?.enrollment_status === 'approved' || props.stats?.enrollment_status === 'trial';
    
    if (hasAccess) {
        // Navigate to student-specific subjects page
        window.location.href = '/student/subjects';
    } else {
        // Navigate to student enrollment if no access
        window.location.href = '/student/enrollment';
    }
};

const navigateToAccessManagement = () => {
    const enrollment = props.auth?.enrollment || null;
    
    if (!enrollment || props.stats?.enrollment_status === 'not_enrolled') {
        // Navigate to student enrollment
        window.location.href = '/student/enrollment';
    } else if (props.stats?.is_trial) {
        // For trial users, redirect to account settings to upgrade first
        window.location.href = '/account-settings';
    } else if (props.stats?.access_remaining_days <= 0) {
        // Navigate to extension page if access expired (premium users only)
        window.location.href = '/student/extension';
    } else {
        // Show enrollment details with access information
        window.location.href = '/student/enrollment/details';
    }
};

const navigateToAccountSettings = () => {
    window.location.href = '/account-settings';
};

// Teacher navigation methods
const navigateToAssignedStudents = () => {
    window.location.href = '/teacher/students';
};

const navigateToActiveStudents = () => {
    // Navigate to students page with active filter
    window.location.href = '/teacher/students?filter=active';
};

const navigateToTeachingSubjects = () => {
    // Navigate to materials page to see subjects being taught
    window.location.href = '/teacher/materials';
};

const navigateToExpiredStudents = () => {
    // Navigate to students page with expired filter
    window.location.href = '/teacher/students?filter=expired';
};
</script>

<template>
    <Head title="Dashboard" />
    
    <DashboardLayout title="Dashboard" subtitle="Welcome to your StudySeco dashboard" :stats="stats">
        <!-- Main Dashboard Content -->
        
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Admin Stats -->
            <template v-if="user.role === 'admin'">
                <!-- Total Students -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Total Students</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2">{{ stats.total_students || 1240 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-emerald-600 text-sm font-semibold">+12%</span>
                                <span class="text-slate-500 text-xs ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6-12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Enrollments -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Active Enrollments</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2">{{ stats.active_enrollments || 856 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-emerald-600 text-sm font-semibold">+8%</span>
                                <span class="text-slate-500 text-xs ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Monthly Revenue</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2">MWK {{ (stats.monthly_revenue || 2840000).toLocaleString() }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-emerald-600 text-sm font-semibold">+15%</span>
                                <span class="text-slate-500 text-xs ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Pending Approvals</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2">{{ stats.pending_enrollments || 23 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-amber-600 text-sm font-semibold">Needs attention</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Teacher Stats -->
            <template v-else-if="user.role === 'teacher'">
                <!-- Total Students Assigned -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToAssignedStudents"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Assigned Students</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-indigo-600 transition-colors">{{ stats.total_assigned || 0 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-indigo-600 text-sm font-semibold">👨‍🎓 Total assigned</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Students -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToActiveStudents"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Active Students</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-emerald-600 transition-colors">{{ stats.active_students || 0 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-emerald-600 text-sm font-semibold">✅ Currently active</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Subjects Taught -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToTeachingSubjects"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Subjects Teaching</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-blue-600 transition-colors">{{ stats.subjects_covered || 0 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-blue-600 text-sm font-semibold">📚 Subjects covered</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Students Needing Extension -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToExpiredStudents"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Students Needing Extension</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-red-600 transition-colors">{{ stats.expired_students || 0 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-red-600 text-sm font-semibold">⏰ Expired access</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Student Stats -->
            <template v-else>
                <!-- Enrollment Status -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToEnrollment"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Enrollment Status</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 capitalize group-hover:text-indigo-600 transition-colors">{{ stats.enrollment_status === 'not_enrolled' ? 'Not enrolled' : stats.enrollment_status }}</p>
                            <div class="flex items-center mt-2">
                                <span :class="stats.enrollment_status === 'approved' || stats.enrollment_status === 'trial' ? 'text-emerald-600' : stats.enrollment_status === 'pending' ? 'text-amber-600' : 'text-red-600'" class="text-sm font-semibold">
                                    {{ stats.enrollment_status === 'approved' ? '✓ Active' : stats.enrollment_status === 'trial' ? '✓ Free Access' : stats.enrollment_status === 'pending' ? '⏳ Pending' : 'Not Enrolled' }}
                                </span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Subjects Enrolled -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToSubjects"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Subjects Enrolled</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-emerald-600 transition-colors">{{ stats.total_subjects || 0 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-emerald-600 text-sm font-semibold">{{ stats.total_subjects ? 'Active' : 'None' }}</span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Access Remaining -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToAccessManagement"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Access Days Left</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-amber-600 transition-colors">{{ stats.access_remaining_days || 0 }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-slate-500 text-xs">
                                    {{ stats.access_remaining_days > 0 ? 'Expires: N/A' : 'Expired' }}
                                </span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Account Type -->
                <div 
                    class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 cursor-pointer group"
                    @click="navigateToAccountSettings"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Account Type</p>
                            <p class="text-3xl font-bold text-slate-800 mt-2 group-hover:text-rose-600 transition-colors">
                                {{ stats.is_trial === true ? 'Free Trial' : (stats.enrollment_status === 'approved' ? 'Premium' : 'Not Enrolled') }}
                            </p>
                            <div class="flex items-center mt-2">
                                <span :class="stats.is_trial === true ? 'text-blue-600' : (stats.enrollment_status === 'approved' ? 'text-purple-600' : 'text-gray-600')" class="text-sm font-semibold">
                                    {{ stats.is_trial === true ? '🆓 7 Days Free Trial' : (stats.enrollment_status === 'approved' ? '⭐ Full Access' : '⏳ Enrollment Pending') }}
                                </span>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Enrollment Trends -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Enrollment Trends</h3>
                <div class="h-80">
                    <Bar :data="enrollmentData" :options="chartOptions" />
                </div>
            </div>

            <!-- Performance Analytics -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Student Performance</h3>
                <div class="h-80">
                    <Line :data="performanceData" :options="lineOptions" />
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Subject Distribution -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Subject Distribution</h3>
                <div class="h-64">
                    <Doughnut :data="subjectDistribution" :options="doughnutOptions" />
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 lg:col-span-2">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Recent Activities</h3>
                <div class="space-y-4">
                    <!-- Dynamic Activities for Students -->
                    <template v-if="user.role === 'student'">
                        <div v-if="recent_activities && recent_activities.length > 0" class="space-y-4">
                            <div 
                                v-for="activity in recent_activities" 
                                :key="activity.id"
                                class="flex items-center p-4 bg-slate-50/50 rounded-2xl"
                            >
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4" 
                                     :class="{
                                         'bg-emerald-100': activity.type === 'enrollment',
                                         'bg-blue-100': activity.type === 'payment',
                                         'bg-purple-100': activity.type === 'achievement',
                                         'bg-amber-100': activity.type === 'assignment'
                                     }">
                                    <!-- Enrollment Icon -->
                                    <svg v-if="activity.type === 'enrollment'" class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <!-- Payment Icon -->
                                    <svg v-else-if="activity.type === 'payment'" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <!-- Achievement Icon -->
                                    <svg v-else-if="activity.type === 'achievement'" class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                    <!-- Default Icon -->
                                    <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800">{{ activity.title }}</p>
                                    <p class="text-sm text-slate-600">{{ activity.description }}</p>
                                </div>
                                <span class="text-xs text-slate-500">{{ activity.created_at }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-slate-500 text-sm">No recent activities yet</p>
                            <p class="text-slate-400 text-xs mt-2">Your enrollment and payment activities will appear here</p>
                        </div>
                    </template>

                    <!-- Admin Activities (unchanged) -->
                    <template v-else-if="user.role === 'admin'">
                        <div class="flex items-center p-4 bg-slate-50/50 rounded-2xl">
                            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">New student enrollment</p>
                                <p class="text-sm text-slate-600">Sarah Mwangi enrolled in Mathematics & Physics</p>
                            </div>
                            <span class="text-xs text-slate-500">2 mins ago</span>
                        </div>

                        <div class="flex items-center p-4 bg-slate-50/50 rounded-2xl">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">Payment received</p>
                                <p class="text-sm text-slate-600">MWK 75,000 payment from James Phiri</p>
                            </div>
                            <span class="text-xs text-slate-500">15 mins ago</span>
                        </div>

                        <div class="flex items-center p-4 bg-slate-50/50 rounded-2xl">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">Trial expiring soon</p>
                                <p class="text-sm text-slate-600">5 students have trials expiring in 24 hours</p>
                            </div>
                            <span class="text-xs text-slate-500">1 hour ago</span>
                        </div>

                        <div class="flex items-center p-4 bg-slate-50/50 rounded-2xl">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">New content uploaded</p>
                                <p class="text-sm text-slate-600">Chemistry Chapter 5: Organic Compounds</p>
                            </div>
                            <span class="text-xs text-slate-500">2 hours ago</span>
                        </div>
                    </template>

                    <!-- Teacher Activities -->
                    <template v-else-if="user.role === 'teacher'">
                        <div v-if="recent_activities && recent_activities.length > 0" class="space-y-4">
                            <div 
                                v-for="activity in recent_activities" 
                                :key="activity.id"
                                class="flex items-center p-4 bg-slate-50/50 rounded-2xl"
                            >
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800">{{ activity.title }}</p>
                                    <p class="text-sm text-slate-600">{{ activity.description }}</p>
                                </div>
                                <span class="text-xs text-slate-500">{{ activity.created_at }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <p class="text-slate-500 text-sm">No recent activities yet</p>
                            <p class="text-slate-400 text-xs mt-2">Student assignments will appear here</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>