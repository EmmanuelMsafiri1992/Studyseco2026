<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    payments: Object,
    stats: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'admin', profile_photo_url: null };

const showVerificationModal = ref(false);
const selectedPayment = ref(null);
const showScreenshotModal = ref(false);
const selectedScreenshot = ref(null);

const verificationForm = useForm({
    status: '',
    action: '',
    admin_notes: '',
    notes: '',
    rejection_reason: '',
});

const formatAmount = (amount, currency = 'MWK') => {
    const currencySymbol = currency === 'MWK' ? 'MWK ' : 
                          currency === 'ZAR' ? 'ZAR ' : 
                          currency === 'USD' ? 'USD ' : 
                          currency + ' ';
    return currencySymbol + new Intl.NumberFormat().format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const openVerificationModal = (payment) => {
    selectedPayment.value = payment;
    verificationForm.reset();
    verificationForm.status = '';
    showVerificationModal.value = true;
};

const closeVerificationModal = () => {
    showVerificationModal.value = false;
    selectedPayment.value = null;
    verificationForm.reset();
};

const viewScreenshot = (screenshotUrl) => {
    selectedScreenshot.value = screenshotUrl;
    showScreenshotModal.value = true;
};

const submitVerification = () => {
    // Map frontend status to backend action
    const action = verificationForm.status === 'approved' ? 'approve' : 'reject';
    
    // Update form data directly
    verificationForm.action = action;
    verificationForm.notes = verificationForm.admin_notes;
    
    // Only include rejection_reason for reject action
    if (action === 'reject') {
        verificationForm.rejection_reason = verificationForm.rejection_reason || '';
    } else {
        // Remove rejection_reason field entirely when approving
        delete verificationForm.rejection_reason;
    }

    verificationForm.post(route('payments.verify', selectedPayment.value?.id), {
        onSuccess: () => {
            closeVerificationModal();
        },
        onError: (errors) => {
            console.error('Verification errors:', errors);
        }
    });
};

const pendingPayments = computed(() => {
    return props.payments?.data?.filter(payment => payment.status === 'pending') || [];
});

const recentlyVerified = computed(() => {
    return props.payments?.data?.filter(payment => payment.status !== 'pending').slice(0, 5) || [];
});

const getPaymentMethodName = (method) => {
    // Handle both string codes and objects
    if (typeof method === 'object' && method !== null) {
        return method.name || method.code || 'Unknown Method';
    }
    
    return {
        'tnm_mpamba': 'TNM Mpamba',
        'airtel_money': 'Airtel Money',
        'bank_transfer': 'Bank Transfer',
        'mukuru': 'Mukuru',
        'worldremit': 'WorldRemit',
        'western_union': 'Western Union',
        'moneygram': 'MoneyGram',
        'paypal': 'PayPal',
        'wise': 'Wise (TransferWise)',
        'remitly': 'Remitly'
    }[method] || (typeof method === 'string' ? method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Unknown Method');
};

const getStatusColor = (status) => {
    return {
        'pending': 'amber',
        'approved': 'green',
        'rejected': 'red'
    }[status] || 'gray';
};
</script>

<template>
    <Head title="Payment Verification" />
    
    <DashboardLayout title="Payment Verification" subtitle="Verify student payments manually">
        <!-- Main Content -->
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-amber-600 text-sm font-medium">Pending Verification</p>
                            <p class="text-2xl font-bold text-amber-800">{{ pendingPayments.length }}</p>
                        </div>
                        <div class="w-10 h-10 bg-amber-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-sm font-medium">Approved Today</p>
                            <p class="text-2xl font-bold text-green-800">{{ payments?.data?.filter(p => p.status === 'approved' && new Date(p.verified_at).toDateString() === new Date().toDateString()).length || 0 }}</p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-600 text-sm font-medium">Rejected</p>
                            <p class="text-2xl font-bold text-red-800">{{ payments?.data?.filter(p => p.status === 'rejected')?.length || 0 }}</p>
                        </div>
                        <div class="w-10 h-10 bg-red-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-600 text-sm font-medium">Total Revenue</p>
                            <p class="text-2xl font-bold text-indigo-800">{{ formatAmount((payments?.data?.filter(p => p.status === 'approved') || []).reduce((sum, p) => sum + parseFloat(p.calculated_amount || p.amount), 0)) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-indigo-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Pending Payment Verification</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ pendingPayments.length }} payments waiting for verification</p>
                </div>

                <div v-if="pendingPayments.length > 0" class="divide-y divide-slate-200/50">
                    <div v-for="payment in pendingPayments" :key="payment.id"
                         class="p-6 hover:bg-slate-50/50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Student Avatar -->
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                    {{ payment.enrollment?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>

                                <div>
                                    <h3 class="font-semibold text-slate-800">
                                        {{ payment.enrollment?.user?.name || 'Unknown User' }}
                                        <span v-if="payment.payment_type === 'subject_increase'" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Subject Addition</span>
                                    </h3>
                                    <div class="flex items-center space-x-4 text-sm text-slate-600">
                                        <span>{{ payment.payment_method_name || getPaymentMethodName(payment.payment_method_code || payment.payment_method) }}</span>
                                        <span>{{ formatAmount(payment.calculated_amount || payment.amount, payment.currency) }}</span>
                                        <span>{{ payment.access_duration_days }} days access</span>
                                        <span>{{ formatDate(payment.created_at) }}</span>
                                    </div>
                                    <div v-if="payment.reference_number" class="text-xs text-slate-500 mt-1">
                                        Ref: {{ payment.reference_number }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <!-- View Screenshot Button -->
                                <button v-if="payment.payment_proof_path" 
                                        @click="viewScreenshot(route('payments.proof', payment.id))"
                                        class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium transition-colors duration-200">
                                    View Screenshot
                                </button>

                                <!-- Verify Button -->
                                <button @click="openVerificationModal(payment)"
                                        class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md">
                                    Verify Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Pending Payments -->
                <div v-else class="p-12 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">All caught up!</h3>
                    <p class="text-slate-500">No payments waiting for verification.</p>
                </div>
            </div>

            <!-- Recently Verified -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Recently Verified</h2>
                    <p class="text-sm text-slate-500 mt-1">Latest verification activities</p>
                </div>

                <div v-if="recentlyVerified.length > 0" class="divide-y divide-slate-200/50">
                    <div v-for="payment in recentlyVerified" :key="payment.id"
                         class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Student Avatar -->
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                    {{ payment.enrollment?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>

                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ payment.enrollment?.user?.name || 'Unknown User' }}</h3>
                                    <div class="flex items-center space-x-4 text-sm text-slate-600">
                                        <span>{{ formatAmount(payment.calculated_amount || payment.amount, payment.currency) }}</span>
                                        <span>{{ formatDate(payment.verified_at) }}</span>
                                        <span v-if="payment.verified_by_user">by {{ payment.verified_by_user?.name || 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <!-- Status Badge -->
                                <div :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    payment.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]">
                                    {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Verification Modal -->
        <div v-if="showVerificationModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-4 sm:p-8 shadow-2xl border border-slate-200/50 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Verify Payment</h3>
                        <p class="text-sm text-slate-600">{{ selectedPayment?.enrollment?.user?.name || 'Unknown User' }}</p>
                    </div>
                    <button @click="closeVerificationModal" class="p-2 hover:bg-slate-100 rounded-xl transition-colors duration-200">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Payment Details -->
                <div class="bg-slate-50 rounded-2xl p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500">Payment Method:</span>
                            <span class="font-medium text-slate-800 ml-2">{{ selectedPayment?.payment_method_name || getPaymentMethodName(selectedPayment?.payment_method_code || selectedPayment?.payment_method) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Amount:</span>
                            <span class="font-medium text-slate-800 ml-2">{{ formatAmount(selectedPayment?.calculated_amount || selectedPayment?.amount, selectedPayment?.currency) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Access Duration:</span>
                            <span class="font-medium text-slate-800 ml-2">{{ selectedPayment?.access_duration_days }} days</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Payment Type:</span>
                            <span class="font-medium text-slate-800 ml-2">{{ selectedPayment?.payment_type === 'subject_increase' ? 'Subject Addition' : 'Regular Payment' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Submitted:</span>
                            <span class="font-medium text-slate-800 ml-2">{{ formatDate(selectedPayment?.created_at) }}</span>
                        </div>
                        <div v-if="selectedPayment?.reference_number" class="col-span-2">
                            <span class="text-slate-500">Reference Number:</span>
                            <span class="font-medium text-slate-800 ml-2">{{ selectedPayment?.reference_number }}</span>
                        </div>
                        <div v-if="selectedPayment?.payment_proof_path" class="col-span-2">
                            <span class="text-slate-500">Payment Proof:</span>
                            <button @click="viewScreenshot(route('payments.proof', selectedPayment.id))" 
                                    class="ml-2 px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium transition-colors duration-200">
                                View Screenshot
                            </button>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitVerification">
                    <!-- Verification Decision -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-4">Verification Decision</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="button"
                                    @click="verificationForm.status = 'approved'"
                                    :class="[
                                        'p-4 rounded-2xl border-2 transition-all duration-200 text-left',
                                        verificationForm.status === 'approved' 
                                            ? 'border-green-500 bg-green-50' 
                                            : 'border-slate-200 hover:border-green-300'
                                    ]">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-green-800">Approve Payment</h4>
                                        <p class="text-sm text-green-600">Grant system access</p>
                                    </div>
                                </div>
                            </button>

                            <button type="button"
                                    @click="verificationForm.status = 'rejected'"
                                    :class="[
                                        'p-4 rounded-2xl border-2 transition-all duration-200 text-left',
                                        verificationForm.status === 'rejected' 
                                            ? 'border-red-500 bg-red-50' 
                                            : 'border-slate-200 hover:border-red-300'
                                    ]">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-red-800">Reject Payment</h4>
                                        <p class="text-sm text-red-600">Deny system access</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Rejection Reason (if rejecting) -->
                    <div v-if="verificationForm.status === 'rejected'" class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Rejection Reason*</label>
                        <textarea v-model="verificationForm.rejection_reason" rows="3" required
                                  class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:bg-white transition-all duration-200"
                                  placeholder="Please provide a reason for rejecting this payment..."></textarea>
                        <div v-if="verificationForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">{{ verificationForm.errors.rejection_reason }}</div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Admin Notes (Optional)</label>
                        <textarea v-model="verificationForm.admin_notes" rows="3"
                                  class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                  placeholder="Additional notes about this verification..."></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeVerificationModal"
                                class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-semibold transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="verificationForm.processing || !verificationForm.status"
                                :class="[
                                    'px-6 py-3 rounded-2xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none',
                                    verificationForm.status === 'approved' 
                                        ? 'bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700'
                                        : 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700'
                                ]">
                            <span v-if="verificationForm.processing">Processing...</span>
                            <span v-else>{{ verificationForm.status === 'approved' ? 'Approve Payment' : 'Reject Payment' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Screenshot Modal -->
        <div v-if="showScreenshotModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50" @click="showScreenshotModal = false">
            <div class="max-w-4xl max-h-[90vh] p-4">
                <img :src="selectedScreenshot" alt="Payment proof" class="max-w-full max-h-full rounded-2xl shadow-2xl">
                <div class="text-center mt-4">
                    <button @click="showScreenshotModal = false" 
                            class="px-4 py-2 bg-white/90 hover:bg-white text-slate-800 rounded-lg font-medium transition-colors duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>