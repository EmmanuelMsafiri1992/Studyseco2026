<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    paymentMethod: Object
});

const getRegionBadge = (region) => {
    const badges = {
        'malawi': 'bg-green-100 text-green-800',
        'south_africa': 'bg-blue-100 text-blue-800',
        'international': 'bg-purple-100 text-purple-800'
    };
    return badges[region] || 'bg-gray-100 text-gray-800';
};

const getTypeBadge = (type) => {
    const badges = {
        'mobile_money': 'bg-yellow-100 text-yellow-800',
        'bank': 'bg-green-100 text-green-800',
        'remittance': 'bg-blue-100 text-blue-800',
        'digital_wallet': 'bg-purple-100 text-purple-800'
    };
    return badges[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Payment Method: ${paymentMethod.name}`" />

    <DashboardLayout 
        title="Payment Method Details" 
        subtitle="View payment method information">

        <div class="max-w-4xl mx-auto">
            <!-- Header Actions -->
            <div class="flex justify-between items-center mb-6">
                <Link :href="route('admin.payment-methods.index')" 
                      class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Payment Methods
                </Link>
                
                <div class="flex space-x-3">
                    <Link :href="route('admin.payment-methods.edit', paymentMethod.id)" 
                          class="bg-gradient-to-r from-amber-500 to-orange-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span>Edit</span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Payment Method Details Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <!-- Header with Icon -->
                <div class="p-6 border-b border-slate-200/50">
                    <div class="flex items-center space-x-4">
                        <div class="text-4xl">{{ paymentMethod.icon }}</div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">{{ paymentMethod.name }}</h1>
                            <div class="flex items-center space-x-3 mt-2">
                                <span :class="getRegionBadge(paymentMethod.region)" class="px-3 py-1 text-xs font-semibold rounded-full capitalize">
                                    {{ paymentMethod.region.replace('_', ' ') }}
                                </span>
                                <span :class="getTypeBadge(paymentMethod.type)" class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ paymentMethod.type.replace('_', ' ') }}
                                </span>
                                <span :class="paymentMethod.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                      class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ paymentMethod.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Code</label>
                                <p class="text-slate-900 font-mono bg-slate-100 px-3 py-2 rounded-lg">{{ paymentMethod.code }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Currency</label>
                                <p class="text-slate-900 font-medium">{{ paymentMethod.currency }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Sort Order</label>
                                <p class="text-slate-900 font-medium">{{ paymentMethod.sort_order }}</p>
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Created</label>
                                <p class="text-slate-900">{{ new Date(paymentMethod.created_at).toLocaleDateString() }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Last Updated</label>
                                <p class="text-slate-900">{{ new Date(paymentMethod.updated_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="mt-6">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Payment Instructions</label>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <p class="text-slate-700 whitespace-pre-line leading-relaxed">{{ paymentMethod.instructions }}</p>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="mt-6" v-if="paymentMethod.requirements && paymentMethod.requirements.length > 0">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Requirements</label>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <ul class="space-y-2">
                                <li v-for="requirement in paymentMethod.requirements" :key="requirement" 
                                    class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-slate-700">{{ requirement.replace('_', ' ') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Statistics (if needed later) -->
                    <div class="mt-8 grid md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-4">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-blue-600 font-medium">Total Payments</p>
                                    <p class="text-2xl font-bold text-blue-900">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-4">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-green-600 font-medium">Successful</p>
                                    <p class="text-2xl font-bold text-green-900">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl p-4">
                            <div class="flex items-center">
                                <div class="p-2 bg-amber-100 rounded-lg">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-amber-600 font-medium">Pending</p>
                                    <p class="text-2xl font-bold text-amber-900">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>