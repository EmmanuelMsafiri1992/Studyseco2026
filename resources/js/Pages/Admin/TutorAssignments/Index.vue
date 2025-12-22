<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    enrollments: Object,
    tutors: Array,
    stats: Object
});

const showAssignModal = ref(false);
const selectedEnrollments = ref([]);
const currentEnrollment = ref(null);

const assignForm = useForm({
    enrollment_id: null,
    tutor_id: ''
});

const bulkAssignForm = useForm({
    enrollment_ids: [],
    tutor_id: ''
});

const openAssignModal = (enrollment = null) => {
    currentEnrollment.value = enrollment;
    if (enrollment) {
        assignForm.enrollment_id = enrollment.id;
        assignForm.tutor_id = enrollment.assigned_tutor_id || '';
    } else {
        assignForm.reset();
        bulkAssignForm.enrollment_ids = selectedEnrollments.value;
    }
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    assignForm.reset();
    bulkAssignForm.reset();
    currentEnrollment.value = null;
};

const submitAssignment = () => {
    if (currentEnrollment.value) {
        // Single assignment
        assignForm.post(route('admin.tutor-assignments.assign'), {
            onSuccess: () => {
                closeAssignModal();
            }
        });
    } else {
        // Bulk assignment
        bulkAssignForm.post(route('admin.tutor-assignments.bulk-assign'), {
            onSuccess: () => {
                closeAssignModal();
                selectedEnrollments.value = [];
            }
        });
    }
};

const unassignTutor = (enrollment) => {
    if (confirm(`Are you sure you want to unassign the tutor from ${enrollment.user?.name || enrollment.name}?`)) {
        router.post(route('admin.tutor-assignments.unassign'), {
            enrollment_id: enrollment.id
        });
    }
};

const toggleSelection = (enrollmentId) => {
    const index = selectedEnrollments.value.indexOf(enrollmentId);
    if (index > -1) {
        selectedEnrollments.value.splice(index, 1);
    } else {
        selectedEnrollments.value.push(enrollmentId);
    }
};

const toggleSelectAll = () => {
    const enrollmentsData = props.enrollments?.data || [];
    if (selectedEnrollments.value.length === enrollmentsData.length) {
        selectedEnrollments.value = [];
    } else {
        selectedEnrollments.value = enrollmentsData.map(e => e.id);
    }
};

const getTutorName = (enrollment) => {
    return enrollment.assigned_tutor?.name || 'Not Assigned';
};

const getTutorColor = (enrollment) => {
    return enrollment.assigned_tutor ? 'green' : 'red';
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'Never';
};

const getStudentName = (enrollment) => {
    return enrollment.user?.name || enrollment.name || 'Unknown Student';
};

const getSubjectsCount = (enrollment) => {
    return enrollment.selected_subjects?.length || 0;
};
</script>

<template>
    <Head title="Tutor Assignments" />
    
    <DashboardLayout title="Tutor Assignments" subtitle="Manage teacher-student assignments and tutoring relationships">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Students</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total_students?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">👨‍🎓</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Assigned Students</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.assigned_students?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">✅</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Unassigned Students</p>
                        <p class="text-2xl font-bold text-red-600">{{ stats.unassigned_students?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">❌</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Available Tutors</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total_tutors?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">👩‍🏫</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <button v-if="selectedEnrollments.length > 0" 
                        @click="openAssignModal()"
                        class="px-4 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Bulk Assign ({{ selectedEnrollments.length }})</span>
                </button>
            </div>

            <div class="text-sm text-slate-600">
                {{ enrollments.total }} total enrollments
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Student-Tutor Assignments</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       :checked="selectedEnrollments.length === (enrollments?.data?.length || 0) && (enrollments?.data?.length || 0) > 0"
                                       :indeterminate="selectedEnrollments.length > 0 && selectedEnrollments.length < (enrollments?.data?.length || 0)"
                                       @change="toggleSelectAll"
                                       class="rounded border-slate-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Subjects</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Assigned Tutor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Assignment Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Access Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="enrollment in enrollments?.data || []" :key="enrollment.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       :checked="selectedEnrollments.includes(enrollment.id)"
                                       @change="toggleSelection(enrollment.id)"
                                       class="rounded border-slate-300">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm mr-3">
                                        {{ getStudentName(enrollment).charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ getStudentName(enrollment) }}</div>
                                        <div class="text-sm text-slate-500">{{ enrollment.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    {{ getSubjectsCount(enrollment) }} subjects
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    enrollment.assigned_tutor 
                                        ? 'bg-green-100 text-green-800' 
                                        : 'bg-red-100 text-red-800'
                                ]">
                                    {{ getTutorName(enrollment) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ formatDate(enrollment.tutor_assigned_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    enrollment.is_access_expired 
                                        ? 'bg-red-100 text-red-800' 
                                        : 'bg-green-100 text-green-800'
                                ]">
                                    {{ enrollment.is_access_expired ? 'Expired' : 'Active' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button @click="openAssignModal(enrollment)"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        {{ enrollment.assigned_tutor ? 'Reassign' : 'Assign' }}
                                    </button>
                                    <button v-if="enrollment.assigned_tutor"
                                            @click="unassignTutor(enrollment)"
                                            class="text-red-600 hover:text-red-900">
                                        Unassign
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        Showing {{ enrollments?.from || 0 }} to {{ enrollments?.to || 0 }} of {{ enrollments?.total || 0 }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link v-if="enrollments?.prev_page_url" :href="enrollments.prev_page_url" 
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                            Previous
                        </Link>
                        <Link v-if="enrollments?.next_page_url" :href="enrollments.next_page_url"
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 shadow-2xl border border-slate-200 max-w-md w-full mx-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-900">
                        {{ currentEnrollment ? 'Assign Tutor' : 'Bulk Assign Tutor' }}
                    </h3>
                    <button @click="closeAssignModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Single Assignment -->
                <form v-if="currentEnrollment" @submit.prevent="submitAssignment" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Student</label>
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <div class="font-medium">{{ getStudentName(currentEnrollment) }}</div>
                            <div class="text-sm text-slate-600">{{ currentEnrollment.email }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Assign Tutor</label>
                        <select v-model="assignForm.tutor_id" required 
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a tutor</option>
                            <option v-for="tutor in tutors" :key="tutor.id" :value="tutor.id">
                                {{ tutor.name }} - {{ tutor.email }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeAssignModal" 
                                class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="assignForm.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            {{ assignForm.processing ? 'Assigning...' : 'Assign Tutor' }}
                        </button>
                    </div>
                </form>

                <!-- Bulk Assignment -->
                <form v-else @submit.prevent="submitAssignment" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Selected Students</label>
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <div class="font-medium">{{ selectedEnrollments.length }} students selected</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Assign Tutor</label>
                        <select v-model="bulkAssignForm.tutor_id" required 
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a tutor</option>
                            <option v-for="tutor in tutors" :key="tutor.id" :value="tutor.id">
                                {{ tutor.name }} - {{ tutor.email }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeAssignModal" 
                                class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="bulkAssignForm.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            {{ bulkAssignForm.processing ? 'Assigning...' : 'Bulk Assign Tutor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>