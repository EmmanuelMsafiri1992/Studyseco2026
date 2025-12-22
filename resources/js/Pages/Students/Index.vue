<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    students: Object,
    stats: Object,
    filters: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Filter state
const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedStatus.value) params.set('status', selectedStatus.value);
    
    router.get(route('students.index'), Object.fromEntries(params));
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    router.get(route('students.index'));
};

// Utility functions
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadgeClass = (status) => {
    return status === 'Active' 
        ? 'bg-green-100 text-green-700'
        : 'bg-red-100 text-red-700';
};

const confirmDelete = (student) => {
    if (confirm(`Are you sure you want to delete ${student.name}? This action cannot be undone.`)) {
        router.delete(route('students.destroy', student.id));
    }
};
</script>

<template>
    <Head title="Student Management" />
    
    <DashboardLayout title="Student Management" subtitle="Manage and monitor student accounts">
        <div class="space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ (stats?.total_students || 0).toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">Total Students</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 713.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ (stats?.active_students || 0).toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">Active Students</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-slate-800">{{ (stats?.new_this_month || 0).toLocaleString() }}</p>
                            <p class="text-sm text-slate-600">New This Month</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input v-model="searchQuery" @input="applyFilters" type="text" placeholder="Search students..." class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                            <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <select v-model="selectedStatus" @change="applyFilters" class="bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white appearance-none">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button @click="resetFilters" class="text-slate-500 hover:text-slate-700 px-4 py-3 rounded-2xl text-sm transition-colors">
                            Clear Filters
                        </button>
                        <Link :href="route('students.create')" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Add Student</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Students</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ students?.total || 0 }} students found</p>
                </div>

                <div v-if="!students?.data?.length" class="p-12 text-center">
                    <div class="w-24 h-24 bg-slate-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-800 mb-2">No students found</h3>
                    <p class="text-slate-600">Students will appear here once they are added to the system.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-slate-800">Student</th>
                                <th class="text-left py-4 px-6 font-semibold text-slate-800">Contact</th>
                                <th class="text-left py-4 px-6 font-semibold text-slate-800">Status</th>
                                <th class="text-left py-4 px-6 font-semibold text-slate-800">Subjects</th>
                                <th class="text-left py-4 px-6 font-semibold text-slate-800">Joined</th>
                                <th class="text-left py-4 px-6 font-semibold text-slate-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr 
                                v-for="student in students.data" 
                                :key="student.id"
                                class="hover:bg-slate-50 transition-colors duration-200"
                            >
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                            {{ student.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-slate-800">{{ student.name }}</h3>
                                            <p class="text-sm text-slate-600">ID: {{ student.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="text-sm text-slate-800">{{ student.email }}</p>
                                        <p class="text-sm text-slate-600">{{ student.phone || 'No phone' }}</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span :class="['inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full', getStatusBadgeClass(student.enrollment_status)]">
                                        {{ student.enrollment_status || 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-slate-600">{{ student.subjects_count || 0 }} subjects</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-slate-600">{{ formatDate(student.created_at) }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="route('students.show', student.id)"
                                            class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200"
                                            title="View Student"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('students.edit', student.id)"
                                            class="p-2 text-slate-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200"
                                            title="Edit Student"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </Link>
                                        <button
                                            @click="confirmDelete(student)"
                                            class="p-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
                                            title="Delete Student"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

