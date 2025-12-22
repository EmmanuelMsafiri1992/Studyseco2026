<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Filler
} from 'chart.js';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
  auth: Object,
  stats: Object,
  chartData: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Filler
);

// Real data from database
const enrollmentTrendData = ref({
  labels: props.chartData?.enrollment?.labels || ['No Data'],
  datasets: [{
    label: 'Monthly Enrollments',
    backgroundColor: 'rgba(99, 102, 241, 0.8)',
    borderColor: 'rgba(99, 102, 241, 1)',
    borderWidth: 2,
    data: props.chartData?.enrollment?.data || [0]
  }]
});

const paymentTrendData = ref({
  labels: props.chartData?.payment?.labels || ['No Data'],
  datasets: [{
    label: 'Revenue (K)',
    borderColor: 'rgb(16, 185, 129)',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    data: props.chartData?.payment?.data || [0],
    fill: true,
    tension: 0.4
  }]
});

const enrollmentStatusData = ref({
  labels: props.chartData?.enrollmentStatus?.labels || ['No Data'],
  datasets: [{
    data: props.chartData?.enrollmentStatus?.data || [1],
    backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(239, 68, 68, 0.8)']
  }]
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
  },
};
</script>

<template>
    <Head title="Analytics & Reports" />

    <DashboardLayout 
        title="Analytics & Reports" 
        subtitle="View comprehensive analytics and generate reports for your institution"
        :stats="stats">

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Students</p>
                        <p class="text-2xl font-bold text-slate-800">{{ stats?.total_students || 1850 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span :class="stats?.student_growth >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-semibold">
                        {{ stats?.student_growth >= 0 ? '↗' : '↘' }} {{ Math.abs(stats?.student_growth || 0) }}%
                    </span>
                    <span class="text-slate-500 text-sm ml-2">from last month</span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Teachers</p>
                        <p class="text-2xl font-bold text-slate-800">{{ stats?.total_teachers || 125 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span :class="stats?.teacher_growth >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-semibold">
                        {{ stats?.teacher_growth >= 0 ? '↗' : '↘' }} {{ Math.abs(stats?.teacher_growth || 0) }}%
                    </span>
                    <span class="text-slate-500 text-sm ml-2">from last month</span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Active Enrollments</p>
                        <p class="text-2xl font-bold text-slate-800">{{ stats?.active_enrollments || 1500 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-blue-600 text-sm font-semibold">Active</span>
                    <span class="text-slate-500 text-sm ml-2">enrollments</span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Pending Enrollments</p>
                        <p class="text-2xl font-bold text-slate-800">{{ stats?.pending_enrollments || 7 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-amber-600 text-sm font-semibold">Awaiting</span>
                    <span class="text-slate-500 text-sm ml-2">approval</span>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Enrollments Chart -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Monthly Enrollments</h3>
                <div style="height: 300px;">
                    <Bar :data="enrollmentTrendData" :options="chartOptions" />
                </div>
            </div>

            <!-- Revenue Trends Chart -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Monthly Revenue (Thousands)</h3>
                <div style="height: 300px;">
                    <Line :data="paymentTrendData" :options="chartOptions" />
                </div>
            </div>
        </div>

        <!-- Enrollment Status Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Enrollment Status</h3>
                <div style="height: 300px;">
                    <Doughnut :data="enrollmentStatusData" :options="chartOptions" />
                </div>
            </div>

            <!-- Quick Reports -->
            <div class="lg:col-span-2 bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Quick Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl hover:from-blue-100 hover:to-indigo-100 transition-all duration-200">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div class="text-left">
                            <p class="font-semibold text-slate-800">Student Performance</p>
                            <p class="text-sm text-slate-600">Generate detailed report</p>
                        </div>
                    </button>

                    <button class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl hover:from-green-100 hover:to-emerald-100 transition-all duration-200">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <div class="text-left">
                            <p class="font-semibold text-slate-800">Financial Report</p>
                            <p class="text-sm text-slate-600">Revenue and expenses</p>
                        </div>
                    </button>

                    <button class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-violet-50 rounded-2xl hover:from-purple-100 hover:to-violet-100 transition-all duration-200">
                        <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 11V10"></path>
                        </svg>
                        <div class="text-left">
                            <p class="font-semibold text-slate-800">Attendance Report</p>
                            <p class="text-sm text-slate-600">Monthly attendance data</p>
                        </div>
                    </button>

                    <button class="flex items-center p-4 bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl hover:from-orange-100 hover:to-amber-100 transition-all duration-200">
                        <svg class="w-8 h-8 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div class="text-left">
                            <p class="font-semibold text-slate-800">Teacher Report</p>
                            <p class="text-sm text-slate-600">Staff performance data</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>