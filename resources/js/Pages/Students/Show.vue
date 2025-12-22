<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    student: Object,
    activeEnrollment: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const showDeleteModal = ref(false);

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
        router.delete(route('students.destroy', props.student.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadgeClass = (status) => {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-700';
        case 'pending': return 'bg-yellow-100 text-yellow-700';
        case 'rejected': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head :title="`Student: ${student.name}`" />
    
    <DashboardLayout :title="`Student: ${student.name}`" subtitle="View student details and enrollment information">
        <div class="space-y-6">
            <!-- Student Info Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                            {{ student.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">{{ student.name }}</h1>
                            <p class="text-slate-600">Student ID: {{ student.id }}</p>
                            <p class="text-sm text-slate-500">Joined {{ formatDate(student.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Link
                            :href="route('students.edit', student.id)"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
                        >
                            Edit Student
                        </Link>
                        <button
                            @click="confirmDelete"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
                        >
                            Delete Student
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-slate-800 mb-3">Contact Information</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-slate-600">Email:</span>
                                <p class="font-medium text-slate-800">{{ student.email }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-slate-600">Phone:</span>
                                <p class="font-medium text-slate-800">{{ student.phone || 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800 mb-3">Account Status</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-slate-600">Status:</span>
                                <span :class="['inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ml-2', student.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700']">
                                    {{ student.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div v-if="student.last_login_at">
                                <span class="text-sm text-slate-600">Last Login:</span>
                                <p class="font-medium text-slate-800">{{ formatDate(student.last_login_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Enrollment Card -->
            <div v-if="activeEnrollment" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Active Enrollment</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="text-sm text-slate-600">Enrollment Number:</span>
                        <p class="font-medium text-slate-800">{{ activeEnrollment.enrollment_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-slate-600">Status:</span>
                        <span :class="['inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ml-2', getStatusBadgeClass(activeEnrollment.status)]">
                            {{ activeEnrollment.status }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm text-slate-600">Access Expires:</span>
                        <p class="font-medium text-slate-800">{{ formatDate(activeEnrollment.access_expires_at) }}</p>
                    </div>
                </div>
                
                <div v-if="activeEnrollment.selected_subjects && activeEnrollment.selected_subjects.length" class="mt-4">
                    <span class="text-sm text-slate-600">Selected Subjects:</span>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span 
                            v-for="subjectId in activeEnrollment.selected_subjects" 
                            :key="subjectId"
                            class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
                        >
                            Subject {{ subjectId }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Enrollments History -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Enrollment History</h2>
                
                <div v-if="!student.enrollments || !student.enrollments.length" class="text-center py-8">
                    <p class="text-slate-600">No enrollments found for this student.</p>
                </div>
                
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-slate-800">Enrollment #</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-800">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-800">Amount</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-800">Created</th>
                                <th class="text-left py-3 px-4 font-semibold text-slate-800">Access Period</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-for="enrollment in student.enrollments" :key="enrollment.id" class="hover:bg-slate-50">
                                <td class="py-3 px-4">
                                    <span class="font-medium text-slate-800">{{ enrollment.enrollment_number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="['inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full', getStatusBadgeClass(enrollment.status)]">
                                        {{ enrollment.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-slate-800">{{ enrollment.currency }} {{ enrollment.total_amount }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-slate-600 text-sm">{{ formatDate(enrollment.created_at) }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span v-if="enrollment.access_expires_at" class="text-slate-600 text-sm">
                                        Until {{ formatDate(enrollment.access_expires_at) }}
                                    </span>
                                    <span v-else class="text-slate-400 text-sm">No access</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back Button -->
            <div class="flex justify-start">
                <Link
                    :href="route('students.index')"
                    class="px-6 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors duration-200"
                >
                    ← Back to Students
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>