<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    payment: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusColor = (status) => {
    switch(status) {
        case 'pending': return 'amber';
        case 'verified': return 'green';
        case 'rejected': return 'red';
        default: return 'gray';
    }
};

const getPaymentMethodName = (payment) => {
    if (payment.paymentMethod) {
        return payment.paymentMethod.name;
    }
    
    // Fallback to code mapping
    const methodMap = {
        'tnm_mpamba': 'TNM Mpamba',
        'airtel_money': 'Airtel Money',
        'mukuru': 'Mukuru',
        'worldremit': 'WorldRemit',
        'western_union': 'Western Union',
        'moneygram': 'MoneyGram',
        'bank_transfer': 'Bank Transfer'
    };
    
    return methodMap[payment.payment_method] || 'Unknown Method';
};

const downloadReceipt = () => {
    window.open(route('payments.receipt', props.payment.id), '_blank');
};
</script>

<template>
    <Head :title="`Payment #${payment.id}`" />
    
    <DashboardLayout :title="`Payment Details`" :subtitle="`Payment #${payment.id}`">
        <div class="space-y-8">
            <!-- Payment Header -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Payment #{{ payment.id }}</h1>
                        <p class="text-slate-600">Submitted on {{ formatDate(payment.created_at) }}</p>
                    </div>
                    <div :class="[
                        'px-4 py-2 rounded-full text-sm font-semibold',
                        payment.status === 'pending' ? 'bg-amber-100 text-amber-800' :
                        payment.status === 'verified' ? 'bg-green-100 text-green-800' :
                        'bg-red-100 text-red-800'
                    ]">
                        {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                    </div>
                </div>

                <!-- Amount Display -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 text-white mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-2">{{ formatAmount(payment.calculated_amount || payment.amount, payment.currency) }}</div>
                        <div class="text-indigo-100">
                            {{ payment.payment_type === 'subject_increase' ? 'Subject Addition Payment' : 'Access Payment' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                <h2 class="text-xl font-bold text-slate-800 mb-6">Payment Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Payment Method</label>
                            <div class="text-slate-800 font-semibold">{{ getPaymentMethodName(payment) }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Reference Number</label>
                            <div class="text-slate-800 font-mono">{{ payment.reference_number }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Access Duration</label>
                            <div class="text-slate-800">{{ payment.access_duration_days || 30 }} days</div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Student</label>
                            <div class="text-slate-800">{{ payment.enrollment?.user?.name || payment.enrollment?.name }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Email</label>
                            <div class="text-slate-800">{{ payment.enrollment?.email }}</div>
                        </div>
                        
                        <div v-if="payment.verified_at">
                            <label class="block text-sm font-medium text-slate-500 mb-1">Verified Date</label>
                            <div class="text-slate-800">{{ formatDate(payment.verified_at) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div v-if="payment.admin_notes" class="mt-6 p-4 bg-slate-50 rounded-xl">
                    <label class="block text-sm font-medium text-slate-500 mb-2">Admin Notes</label>
                    <div class="text-slate-800">{{ payment.admin_notes }}</div>
                </div>

                <!-- Rejection Reason -->
                <div v-if="payment.status === 'rejected' && payment.admin_notes" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <label class="block text-sm font-medium text-red-700 mb-2">Rejection Reason</label>
                    <div class="text-red-800">{{ payment.admin_notes }}</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <Link :href="route('payments.index')" 
                      class="inline-flex items-center px-6 py-3 bg-slate-500 hover:bg-slate-600 text-white font-medium rounded-2xl transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Payments
                </Link>

                <div v-if="payment.status === 'verified'" class="space-x-4">
                    <button @click="downloadReceipt"
                            class="inline-flex items-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-2xl transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Receipt
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>