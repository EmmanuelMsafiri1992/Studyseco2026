<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    enrollment: Object,
    subjectNames: Array
});

const showApproveModal = ref(false);
const showRejectModal = ref(false);

const approveForm = useForm({});
const rejectForm = useForm({
    admin_notes: '',
    rejection_reason: ''
});

const approveEnrollment = () => {
    approveForm.patch(route('admin.enrollments.approve', props.enrollment.id), {
        onSuccess: () => {
            showApproveModal.value = false;
        }
    });
};

const rejectEnrollment = () => {
    rejectForm.patch(route('admin.enrollments.reject', props.enrollment.id), {
        onSuccess: () => {
            showRejectModal.value = false;
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

const formatCurrency = (amount, currency) => {
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0
    });
    return formatter.format(amount);
};

const getPaymentProofUrl = () => {
    try {
        return route('admin.enrollments.payment-proof', props.enrollment.id);
    } catch (error) {
        // Fallback to direct URL if route is not available
        return `/admin/enrollments/${props.enrollment.id}/payment-proof`;
    }
};
</script>

<template>
    <Head :title="`Enrollment - ${enrollment.name}`" />

    <DashboardLayout 
        :title="`Enrollment Details - ${enrollment.name}`" 
        subtitle="Review enrollment information and manage approval">

        <div class="mb-6">
            <Link :href="route('admin.enrollments.index')" 
                  class="inline-flex items-center text-sm text-slate-600 hover:text-slate-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Enrollments
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Student Information -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Student Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Full Name</label>
                            <p class="text-slate-900 font-medium">{{ enrollment.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Email Address</label>
                            <p class="text-slate-900">{{ enrollment.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Phone Number</label>
                            <p class="text-slate-900">{{ enrollment.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Country</label>
                            <p class="text-slate-900">{{ enrollment.country }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-600">Address</label>
                            <p class="text-slate-900">{{ enrollment.address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Details -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Enrollment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Enrollment Type</label>
                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full" 
                                  :class="enrollment.is_trial ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                                {{ enrollment.is_trial ? 'Free Trial' : 'Paid' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Status</label>
                            <span :class="getStatusBadge(enrollment.status)" 
                                  class="inline-flex px-3 py-1 text-xs font-semibold rounded-full capitalize">
                                {{ enrollment.status }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Enrollment Date</label>
                            <p class="text-slate-900">{{ new Date(enrollment.created_at).toLocaleDateString() }}</p>
                        </div>
                        <div v-if="enrollment.approved_at">
                            <label class="block text-sm font-medium text-slate-600">Approved Date</label>
                            <p class="text-slate-900">{{ new Date(enrollment.approved_at).toLocaleDateString() }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600">Access Expires</label>
                            <p class="text-slate-900">{{ new Date(enrollment.access_expires_at).toLocaleDateString() }}</p>
                        </div>
                        <div v-if="!enrollment.is_trial">
                            <label class="block text-sm font-medium text-slate-600">Total Amount</label>
                            <p class="text-slate-900 font-medium">{{ formatCurrency(enrollment.total_amount, enrollment.currency) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Selected Subjects -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Selected Subjects</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div v-for="subject in subjectNames" :key="subject" 
                             class="px-3 py-2 bg-slate-100 rounded-2xl text-sm text-slate-700">
                            {{ subject }}
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div v-if="!enrollment.is_trial && enrollment.payments && enrollment.payments.length > 0" 
                     class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Payment Information</h3>
                    <div v-for="payment in enrollment.payments" :key="payment.id" class="mb-4 last:mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600">Payment Method</label>
                                <p class="text-slate-900">{{ payment.payment_method?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600">Payment Status</label>
                                <span :class="getPaymentStatusBadge(payment.status)" 
                                      class="inline-flex px-3 py-1 text-xs font-semibold rounded-full capitalize">
                                    {{ payment.status }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600">Reference Number</label>
                                <p class="text-slate-900 font-mono">{{ payment.reference_number || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600">Amount</label>
                                <p class="text-slate-900 font-medium">{{ formatCurrency(payment.amount, payment.currency) }}</p>
                            </div>
                        </div>
                        
                        <!-- Payment Proof -->
                        <div v-if="payment.payment_proof_path || enrollment.payment_proof_path" class="mt-4">
                            <label class="block text-sm font-medium text-slate-600 mb-2">Payment Proof</label>
                            <div class="p-4 bg-slate-50 rounded-2xl">
                                <a :href="getPaymentProofUrl()" 
                                   target="_blank"
                                   class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    View Payment Proof
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Actions</h3>
                    
                    <div class="space-y-3">
                        <button v-if="enrollment.status === 'pending'" 
                                @click="showApproveModal = true"
                                class="w-full bg-green-500 text-white px-4 py-3 rounded-2xl font-medium hover:bg-green-600 transition-colors">
                            Approve Enrollment
                        </button>
                        
                        <button v-if="enrollment.status === 'pending'" 
                                @click="showRejectModal = true"
                                class="w-full bg-red-500 text-white px-4 py-3 rounded-2xl font-medium hover:bg-red-600 transition-colors">
                            Reject Enrollment
                        </button>

                        <div v-if="enrollment.status !== 'pending'" 
                             class="p-4 bg-slate-100 rounded-2xl text-center text-slate-600">
                            <p class="text-sm">
                                This enrollment has been {{ enrollment.status }}.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status History -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Status History</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Created</span>
                            <span class="text-slate-900">{{ new Date(enrollment.created_at).toLocaleDateString() }}</span>
                        </div>
                        <div v-if="enrollment.approved_at" class="flex justify-between">
                            <span class="text-slate-600">Approved</span>
                            <span class="text-slate-900">{{ new Date(enrollment.approved_at).toLocaleDateString() }}</span>
                        </div>
                        <div v-if="enrollment.admin_notes" class="mt-4">
                            <label class="block text-sm font-medium text-slate-600 mb-1">Admin Notes</label>
                            <p class="text-slate-900 text-sm bg-slate-100 p-3 rounded-2xl">{{ enrollment.admin_notes }}</p>
                        </div>
                        <div v-if="enrollment.rejection_reason" class="mt-4">
                            <label class="block text-sm font-medium text-slate-600 mb-1">Rejection Reason</label>
                            <p class="text-slate-900 text-sm bg-red-50 p-3 rounded-2xl">{{ enrollment.rejection_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Confirmation Modal -->
        <div v-if="showApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Approve Enrollment</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to approve "{{ enrollment.name }}"'s enrollment? 
                    This will create a user account and send them login credentials.
                </p>
                
                <div class="flex justify-end space-x-4">
                    <button @click="showApproveModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button @click="approveEnrollment" 
                            :disabled="approveForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-2xl hover:bg-green-700 transition-colors disabled:opacity-50">
                        {{ approveForm.processing ? 'Approving...' : 'Approve Enrollment' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Confirmation Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Enrollment</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to reject "{{ enrollment.name }}"'s enrollment?
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