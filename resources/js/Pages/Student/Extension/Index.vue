<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    enrollment: Object,
    accessDurations: Array,
    paymentMethods: Array,
    auth: Object
});

// Calculate days remaining
const accessDaysRemaining = computed(() => {
    if (!props.enrollment?.access_expires_at) return 0;
    const expiryDate = new Date(props.enrollment.access_expires_at);
    const today = new Date();
    const diffTime = expiryDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return Math.max(0, diffDays);
});

const selectedDuration = ref(null);
const selectedPaymentMethod = ref(null);
const showPaymentDetails = ref(false);

const extensionForm = useForm({
    duration_id: null,
    payment_method: null,
    proof_screenshot: null
});

const handleDurationSelect = (duration) => {
    selectedDuration.value = duration;
    extensionForm.duration_id = duration.id;
    showPaymentDetails.value = false;
};

const handlePaymentMethodSelect = (method) => {
    selectedPaymentMethod.value = method;
    extensionForm.payment_method = method.code;
    showPaymentDetails.value = true;
};

const handleFileUpload = (event) => {
    extensionForm.proof_screenshot = event.target.files[0];
};

const submitExtension = () => {
    extensionForm.post(route('student.extension.store'));
};
</script>

<template>
    <Head title="Extend Access" />
    
    <DashboardLayout 
        title="Extend Your Access" 
        subtitle="Continue your learning journey by extending your subject access">
        
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Extend Your Subject Access</h1>
                <p class="text-gray-600">Continue your learning journey by extending your access period</p>
            </div>

            <!-- Current Status -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Access Status</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-blue-600 font-semibold text-lg mb-2">{{ enrollment?.status || 'N/A' }}</div>
                        <div class="text-sm text-blue-600">Current Status</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-green-600 font-semibold text-lg mb-2">{{ accessDaysRemaining }} days</div>
                        <div class="text-sm text-green-600">Access Remaining</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-purple-600 font-semibold text-lg mb-2">{{ enrollment?.selected_subjects?.length || 0 }}</div>
                        <div class="text-sm text-purple-600">Enrolled Subjects</div>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">Current access expires:</span>
                        <span :class="['font-semibold', accessExpired ? 'text-red-600' : 'text-green-600']">
                            {{ formatDate(enrollment?.access_expires_at) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Extension Options -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Extension Options</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="option in extensionOptions" 
                        :key="option.months"
                        :class="[
                            'border-2 rounded-xl p-6 cursor-pointer transition-all',
                            selectedOption === option.months ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'
                        ]"
                        @click="selectedOption = option.months"
                    >
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 mb-2">{{ option.months }} Month{{ option.months > 1 ? 's' : '' }}</div>
                            <div class="text-sm text-gray-600 mb-4">{{ option.description }}</div>
                            <div class="text-lg font-semibold text-blue-600 mb-2">{{ formatPrice(option.price) }}</div>
                            <div class="text-xs text-gray-500">{{ formatPricePerSubject(option.price) }} per subject</div>
                            
                            <!-- Popular badge -->
                            <div v-if="option.popular" class="mt-3">
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Most Popular</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div v-if="selectedOption" class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Select Payment Method</h2>
                
                <div class="space-y-3">
                    <div 
                        v-for="method in paymentMethods" 
                        :key="method.id"
                        :class="[
                            'border-2 rounded-xl p-4 cursor-pointer transition-all',
                            selectedPaymentMethod === method.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'
                        ]"
                        @click="selectedPaymentMethod = method.id"
                    >
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl">{{ method.icon }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ method.name }}</h3>
                                <p class="text-sm text-gray-600">{{ method.type }} - {{ method.currency }}</p>
                                <p v-if="method.instructions" class="text-xs text-gray-500 mt-1">{{ method.instructions }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div v-if="selectedOption && selectedPaymentMethod" class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Summary</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-700">Extension Period:</span>
                        <span class="font-semibold">{{ selectedOption }} month{{ selectedOption > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-700">Current Subjects:</span>
                        <span class="font-semibold">{{ enrollment?.selected_subjects?.length || 0 }} subjects</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-700">New Expiry Date:</span>
                        <span class="font-semibold text-green-600">{{ getNewExpiryDate() }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 text-lg font-bold">
                        <span>Total Amount:</span>
                        <span class="text-blue-600">{{ formatPrice(getTotalPrice()) }}</span>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h3 class="font-semibold text-yellow-800 mb-2">💡 Payment Instructions</h3>
                    <ol class="text-sm text-yellow-700 space-y-1">
                        <li>1. Make payment using your selected method above</li>
                        <li>2. Keep your payment reference/transaction ID</li>
                        <li>3. Upload payment proof in the form below</li>
                        <li>4. Access will be extended after verification (usually 24-48 hours)</li>
                    </ol>
                </div>
            </div>

            <!-- Payment Form -->
            <div v-if="selectedOption && selectedPaymentMethod" class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Complete Extension Request</h2>
                
                <form @submit.prevent="submitExtension" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Reference/Transaction ID *</label>
                        <input 
                            v-model="form.payment_reference"
                            type="text" 
                            required
                            placeholder="Enter your payment reference number..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <p class="text-xs text-gray-500 mt-1">The reference number from your payment confirmation</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Payment Proof *</label>
                        <input 
                            type="file" 
                            @change="handleFileUpload"
                            accept=".jpg,.jpeg,.png,.pdf" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Screenshot or receipt (JPG, PNG, PDF - max 5MB)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (Optional)</label>
                        <textarea 
                            v-model="form.notes"
                            rows="3" 
                            placeholder="Any additional information..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                    </div>

                    <div class="flex items-center space-x-3">
                        <input 
                            v-model="form.terms_accepted" 
                            type="checkbox" 
                            required
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <label class="text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 underline">terms and conditions</a> for subject extension
                        </label>
                    </div>

                    <div class="pt-6">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-semibold text-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Processing Extension Request...</span>
                            <span v-else>Submit Extension Request ({{ formatPrice(getTotalPrice()) }})</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>