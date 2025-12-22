<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    enrollments: Object,
    enrollmentStats: Object
});

const showRejectModal = ref(false);
const enrollmentToReject = ref(null);

const rejectForm = useForm({
    admin_notes: '',
    rejection_reason: ''
});

const confirmReject = (enrollment) => {
    enrollmentToReject.value = enrollment;
    showRejectModal.value = true;
};

const rejectEnrollment = () => {
    if (!rejectForm.rejection_reason) {
        alert('Please provide a rejection reason.');
        return;
    }
    
    if (enrollmentToReject.value) {
        rejectForm.patch(route('admin.enrollments.reject', enrollmentToReject.value.id), {
            onSuccess: () => {
                showRejectModal.value = false;
                enrollmentToReject.value = null;
                rejectForm.reset();
            },
            onError: (errors) => {
                console.error('Error rejecting enrollment:', errors);
                alert('Failed to reject enrollment. Please try again.');
            }
        });
    }
};

const approveEnrollment = (enrollment) => {
    router.patch(route('admin.enrollments.approve', enrollment.id), {}, {
        onSuccess: () => {
            // Page will be refreshed with updated data
        },
        onError: (errors) => {
            console.error('Error approving enrollment:', errors);
            alert('Failed to approve enrollment. Please try again.');
        }
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'expired': 'bg-gray-100 text-gray-800'
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'verified': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800'
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Enrollments Management" />

    <DashboardLayout 
        title="Enrollments Management" 
        subtitle="Review and manage student enrollments">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-blue-500 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Total Enrollments</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ enrollmentStats.total }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-yellow-500 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Pending</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ enrollmentStats.pending }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-green-500 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Approved</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ enrollmentStats.approved }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-red-500 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Rejected</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ enrollmentStats.rejected }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
            <div class="p-6 border-b border-slate-200/50">
                <h2 class="text-xl font-bold text-slate-800">All Enrollments</h2>
                <p class="text-sm text-slate-500 mt-1">Review and manage student enrollments</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Student</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Contact</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Payment</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Date</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/50">
                        <tr v-for="enrollment in enrollments.data" :key="enrollment.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-slate-900">{{ enrollment.name }}</div>
                                    <div class="text-sm text-slate-500">{{ enrollment.country }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm text-slate-900">{{ enrollment.email }}</div>
                                    <div class="text-sm text-slate-500">{{ enrollment.phone }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full" 
                                      :class="enrollment.is_trial ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                                    {{ enrollment.is_trial ? 'Free Trial' : 'Paid' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="enrollment.payments && enrollment.payments.length > 0">
                                    <span :class="getPaymentStatusBadge(enrollment.payments[0].status)" 
                                          class="px-3 py-1 text-xs font-semibold rounded-full">
                                        {{ enrollment.payments[0].status }}
                                    </span>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ enrollment.payments[0].payment_method?.name || 'N/A' }}
                                    </div>
                                </div>
                                <div v-else-if="enrollment.is_trial">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Trial
                                    </span>
                                </div>
                                <span v-else class="text-sm text-slate-400">No payment</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getStatusBadge(enrollment.status)" class="px-3 py-1 text-xs font-semibold rounded-full capitalize">
                                    {{ enrollment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ new Date(enrollment.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('admin.enrollments.show', enrollment.id)" 
                                          class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        View
                                    </Link>
                                    
                                    <button v-if="enrollment.status === 'pending'" 
                                            @click="approveEnrollment(enrollment)"
                                            class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Approve
                                    </button>
                                    
                                    <button v-if="enrollment.status === 'pending'" 
                                            @click="confirmReject(enrollment)"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="enrollments.links && enrollments.links.length > 3" class="px-6 py-4 border-t border-slate-200/50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">
                        Showing {{ enrollments.from || 0 }} to {{ enrollments.to || 0 }} of {{ enrollments.total || 0 }} results
                    </p>
                    <div class="flex items-center space-x-2">
                        <template v-for="(link, index) in enrollments.links" :key="`link-${index}`">
                            <component 
                                :is="link.url ? Link : 'span'"
                                :href="link.url || undefined"
                                :class="[
                                    'px-3 py-2 text-sm rounded-lg transition-colors duration-150',
                                    link.active 
                                        ? 'bg-indigo-500 text-white' 
                                        : link.url 
                                            ? 'text-slate-600 hover:bg-slate-100' 
                                            : 'text-slate-400 cursor-not-allowed'
                                ]"
                                v-html="link.label">
                            </component>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Confirmation Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Enrollment</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to reject "{{ enrollmentToReject?.name }}"'s enrollment?
                </p>
                
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason *
                    </label>
                    <textarea 
                        v-model="rejectForm.rejection_reason"
                        id="rejection_reason"
                        rows="3"
                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                        placeholder="Please provide a reason for rejection..."
                        required
                    ></textarea>
                </div>

                <div class="mb-4">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Admin Notes (Optional)
                    </label>
                    <textarea 
                        v-model="rejectForm.admin_notes"
                        id="admin_notes"
                        rows="2"
                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                        placeholder="Additional notes..."
                    ></textarea>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button @click="showRejectModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button @click="rejectEnrollment" 
                            :disabled="rejectForm.processing || !rejectForm.rejection_reason"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-2xl hover:bg-red-700 transition-colors disabled:opacity-50">
                        {{ rejectForm.processing ? 'Rejecting...' : 'Reject Enrollment' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>