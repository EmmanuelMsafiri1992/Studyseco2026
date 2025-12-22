<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    enrollment: Object,
    currentSubjects: Array,
    availableSubjects: Array,
    pricePerSubject: Number,
    currency: String,
    paymentMethods: Array,
});

// Use separate reactive ref for subjects to avoid Inertia form issues
const selectedSubjects = ref([]);

const form = useForm({
    additional_subjects: [],
    payment_method: '',
    reference_number: '',
    proof_screenshot: null,
});

const previewUrl = ref(null);

const totalCost = computed(() => {
    const count = selectedSubjects.value?.length || 0;
    const price = props.pricePerSubject || 0;
    return count * price;
});

const formatAmount = (amount) => {
    const currencySymbol = props.currency === 'MWK' ? 'MWK ' : 
                          props.currency === 'ZAR' ? 'R ' : 
                          props.currency === 'USD' ? '$ ' : props.currency + ' ';
    return currencySymbol + new Intl.NumberFormat().format(amount);
};

const toggleSubject = (subjectId) => {
    const index = selectedSubjects.value.indexOf(subjectId);
    if (index > -1) {
        // Remove subject if already selected
        selectedSubjects.value.splice(index, 1);
    } else {
        // Add subject if not selected
        selectedSubjects.value.push(subjectId);
    }
    
    // Sync with form for submission
    form.additional_subjects = [...selectedSubjects.value];
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    form.proof_screenshot = file;
    
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        previewUrl.value = null;
    }
};

const submitForm = () => {
    form.post(route('student.enrollment.store-subject-increase'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect will be handled by the backend
        },
        onError: (errors) => {
            console.error('Subject increase errors:', errors);
        }
    });
};

// Use payment methods from backend
const paymentMethodOptions = computed(() => {
    return props.paymentMethods || [];
});
</script>

<template>
    <Head title="Increase Subjects" />
    
    <DashboardLayout title="Add More Subjects" subtitle="Expand your learning with additional subjects">
        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Current Subjects Overview -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Current Enrollment</h2>
                    <div class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        {{ currentSubjects.length }} Subject{{ currentSubjects.length !== 1 ? 's' : '' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div 
                        v-for="subject in currentSubjects" 
                        :key="subject.id"
                        class="flex items-center p-4 bg-slate-50 rounded-xl border border-slate-200"
                    >
                        <span class="text-2xl mr-3">{{ subject.icon }}</span>
                        <div>
                            <h4 class="font-medium text-slate-800">{{ subject.name }}</h4>
                            <p class="text-xs text-slate-500">{{ subject.department }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center space-x-2 text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">
                            You can add more subjects to expand your learning opportunities. Each additional subject costs 
                            <strong>{{ formatAmount(pricePerSubject) }}</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Add Subjects Form -->
            <form @submit.prevent="submitForm" class="space-y-8" novalidate>
                <!-- Subject Selection -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                    <h2 class="text-2xl font-bold text-slate-800 mb-6">Select Additional Subjects</h2>
                    
                    
                    <div v-if="availableSubjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="subject in availableSubjects" 
                            :key="subject.id"
                            class="relative"
                        >
                            <div 
                                @click="toggleSubject(subject.id)"
                                :class="{
                                    'border-indigo-500 bg-indigo-50': selectedSubjects.includes(subject.id),
                                    'border-slate-200 hover:border-indigo-300': !selectedSubjects.includes(subject.id)
                                }"
                                class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                            >
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-2xl">{{ subject.icon }}</span>
                                        <div>
                                            <h4 class="font-medium text-slate-800">{{ subject.name }}</h4>
                                            <p class="text-xs text-slate-500">{{ subject.department }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div 
                                    :class="{
                                        'border-indigo-500 bg-indigo-500': selectedSubjects.includes(subject.id),
                                        'border-slate-300': !selectedSubjects.includes(subject.id)
                                    }"
                                    class="w-5 h-5 border-2 rounded flex items-center justify-center"
                                >
                                    <svg 
                                        v-if="selectedSubjects.includes(subject.id)" 
                                        class="w-3 h-3 text-white" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">All Subjects Enrolled</h3>
                        <p class="text-slate-600">You're already enrolled in all available subjects!</p>
                    </div>

                    <div v-if="form.errors.additional_subjects" class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm text-red-600">{{ form.errors.additional_subjects }}</p>
                    </div>

                    <!-- Cost Summary -->
                    <div v-if="form.additional_subjects.length > 0" class="mt-6 p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800">Additional Cost</h3>
                                <p class="text-sm text-slate-600">{{ form.additional_subjects.length }} additional subject{{ form.additional_subjects.length !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-indigo-600">{{ formatAmount(totalCost) }}</p>
                                <p class="text-sm text-slate-500">{{ formatAmount(pricePerSubject) }} per subject</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div v-if="form.additional_subjects.length > 0" class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                    <h2 class="text-2xl font-bold text-slate-800 mb-6">Payment Information</h2>
                    
                    <!-- Payment Method -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-4">Select Payment Method</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="method in paymentMethodOptions" 
                                :key="method.code"
                                class="relative"
                            >
                                <div 
                                    @click="form.payment_method = method.code"
                                    :class="{
                                        'border-indigo-500 bg-indigo-50': form.payment_method === method.code,
                                        'border-slate-200 hover:border-indigo-300': form.payment_method !== method.code
                                    }"
                                    class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
                                >
                                    <span class="text-2xl mr-3">{{ method.icon }}</span>
                                    <div>
                                        <span class="font-medium text-slate-800">{{ method.name }}</span>
                                        <p class="text-xs text-slate-500 mt-1">{{ method.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.payment_method" class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm text-red-600">{{ form.errors.payment_method }}</p>
                        </div>
                    </div>

                    <!-- Reference Number -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Payment Reference Number</label>
                        <input 
                            type="text" 
                            name="reference_number"
                            v-model="form.reference_number"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter transaction/reference number"
                            :required="form.payment_method !== 'trial' && form.payment_method !== ''"
                        >
                        <div v-if="form.errors.reference_number" class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm text-red-600">{{ form.errors.reference_number }}</p>
                        </div>
                    </div>

                    <!-- Proof Screenshot -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Payment Proof Screenshot</label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center px-6 py-3 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-indigo-400 transition-colors">
                                <svg class="w-6 h-6 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span class="text-sm text-slate-600">Upload Screenshot</span>
                                <input 
                                    type="file" 
                                    name="proof_screenshot"
                                    @change="handleFileUpload"
                                    accept="image/*"
                                    class="hidden"
                                    :required="form.payment_method !== 'trial' && form.payment_method !== ''"
                                >
                            </label>
                            
                            <div v-if="previewUrl" class="w-20 h-20 border border-slate-300 rounded-lg overflow-hidden">
                                <img :src="previewUrl" alt="Payment proof preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div v-if="form.errors.proof_screenshot" class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm text-red-600">{{ form.errors.proof_screenshot }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div v-if="form.additional_subjects.length > 0" class="flex flex-col space-y-4">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-2xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        <div class="flex items-center justify-center space-x-2">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>{{ form.processing ? 'Processing...' : 'Submit Payment for Verification' }}</span>
                        </div>
                    </button>
                    
                    <p class="text-center text-sm text-slate-500">
                        💳 Secure payment processing • ⚡ Quick verification • 📚 Instant access after approval
                    </p>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>