<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    extension: Object,
});

const showRejectModal = ref(false);
const rejectionReason = ref('');

// Methods
const formatCurrency = (amount, currency) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: currency === 'MWK' ? 0 : 2
    }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-yellow-100 text-yellow-800',
        verified: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const approveExtension = () => {
    if (confirm(`Approve extension for ${props.extension.enrollment.name}?`)) {
        router.post(route('admin.extensions.approve', props.extension.id));
    }
};

const rejectExtension = () => {
    if (rejectionReason.value.trim()) {
        router.post(route('admin.extensions.reject', props.extension.id), {
            rejection_reason: rejectionReason.value
        }, {
            onSuccess: () => {
                showRejectModal.value = false;
                rejectionReason.value = '';
            }
        });
    }
};

const viewPaymentProof = () => {
    window.open(`/storage/${props.extension.payment_proof_path}`, '_blank');
};
</script>

<template>
    <Head title="Extension Details" />
    
    <DashboardLayout title="Extension Details" subtitle="View and manage extension request">
        <div class="space-y-6">
            <!-- Back Button -->
            <div>
                <Link 
                    :href="route('admin.extensions.index')"
                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Extensions
                </Link>
            </div>

            <!-- Extension Details Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-white">Extension Request #{{ extension.id }}</h3>
                        <span :class="['px-3 py-1 rounded-full text-sm font-medium', 
                                      extension.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                      extension.status === 'verified' ? 'bg-green-100 text-green-800' :
                                      'bg-red-100 text-red-800']">
                            {{ extension.status.charAt(0).toUpperCase() + extension.status.slice(1) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Student Information -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Student Information</h4>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-lg font-medium text-indigo-600">
                                            {{ extension.enrollment.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <h5 class="text-lg font-medium text-gray-900">{{ extension.enrollment.name }}</h5>
                                        <p class="text-sm text-gray-500">{{ extension.enrollment.email }}</p>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-600">Enrollment Number:</span>
                                        <span class="text-sm text-gray-900">{{ extension.enrollment.enrollment_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-600">Region:</span>
                                        <span class="text-sm text-gray-900">{{ extension.enrollment.region || 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-600">Current Access Expires:</span>
                                        <span class="text-sm text-gray-900">
                                            {{ extension.enrollment.access_expires_at ? formatDate(extension.enrollment.access_expires_at) : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Extension Details -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Extension Details</h4>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Duration:</span>
                                    <span class="text-sm text-gray-900">
                                        {{ extension.extension_months }} month{{ extension.extension_months > 1 ? 's' : '' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Amount:</span>
                                    <span class="text-sm text-gray-900">
                                        {{ formatCurrency(extension.amount, extension.currency) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Payment Method:</span>
                                    <span class="text-sm text-gray-900">{{ extension.payment_method?.name || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Reference Number:</span>
                                    <span class="text-sm text-gray-900 font-mono">{{ extension.reference_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Request Date:</span>
                                    <span class="text-sm text-gray-900">{{ formatDate(extension.created_at) }}</span>
                                </div>
                                <div v-if="extension.verified_at" class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-600">Processed Date:</span>
                                    <span class="text-sm text-gray-900">{{ formatDate(extension.verified_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Proof -->
                    <div v-if="extension.payment_proof_path" class="mt-8">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Proof</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Payment Proof Document</p>
                                        <p class="text-sm text-gray-500">Click to view the uploaded payment proof</p>
                                    </div>
                                </div>
                                <button 
                                    @click="viewPaymentProof"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    View Proof
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div v-if="extension.admin_notes" class="mt-8">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Admin Notes</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-900">{{ extension.admin_notes }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="extension.status === 'pending'" class="mt-8 flex justify-end space-x-4">
                        <button 
                            @click="showRejectModal = true"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Reject
                        </button>
                        <button 
                            @click="approveExtension"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            Approve Extension
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rejection Modal -->
            <div v-if="showRejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Extension Request</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Please provide a reason for rejecting this extension request:
                        </p>
                        <textarea 
                            v-model="rejectionReason"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter rejection reason..."
                            required
                        ></textarea>
                        <div class="flex justify-end space-x-3 mt-4">
                            <button 
                                @click="showRejectModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="rejectExtension"
                                :disabled="!rejectionReason.trim()"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
                            >
                                Reject Extension
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>