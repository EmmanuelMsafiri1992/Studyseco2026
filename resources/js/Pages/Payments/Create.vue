<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    hasPendingPayment: Boolean,
    paymentMethods: Array,
    accessDurations: Array,
    availableSubjects: Array,
    enrollment: Object,
    isUpgrade: Boolean,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

// Debug: Log what subjects are received from backend
console.log('Available subjects from backend:', {
    count: props.availableSubjects?.length || 0,
    ids: props.availableSubjects?.map(s => s.id) || [],
    subjects: props.availableSubjects
});

const form = useForm({
    payment_method: '',
    amount: '',
    reference_number: '',
    proof_screenshot: null,
    access_duration_id: null,
    selected_subjects: [],  // Start with empty array to avoid stale data
    upgrade: props.isUpgrade || false,
    terms_accepted: false,
    conduct_agreed: false,
});

const screenshotInput = ref(null);
const screenshotPreview = ref(null);
const showProcessing = ref(false);
const showTermsModal = ref(false);

// Ensure selected subjects are always integers
const selectedSubjectsAsIntegers = computed(() => {
    return form.selected_subjects.map(id => parseInt(id)).filter(id => !isNaN(id));
});

const selectedAccessOption = computed(() => {
    return props.accessDurations.find(option => option.id === form.access_duration_id);
});

const getPaymentInstructions = (method) => {
    const instructions = [];
    
    if (method.instructions) {
        instructions.push(method.instructions);
    }
    
    if (method.account_details) {
        instructions.push(method.account_details);
    }
    
    // Add step to take screenshot as evidence
    instructions.push('Take a screenshot of your payment confirmation as proof');
    
    return instructions.length > 0 ? instructions : ['Complete payment using this method', 'Upload proof of payment'];
};

const filteredPaymentMethods = computed(() => {
    // Since backend now handles filtering correctly, we can simply return all methods
    // The backend already filters by region + international methods
    return props.paymentMethods;
});

const getSelectedMethod = computed(() => {
    return filteredPaymentMethods.value.find(method => method.code === form.payment_method);
});

const selectScreenshot = () => {
    screenshotInput.value.click();
};

const updateScreenshotPreview = () => {
    const screenshot = screenshotInput.value.files[0];
    
    if (!screenshot) return;
    
    const reader = new FileReader();
    
    reader.onload = (e) => {
        screenshotPreview.value = e.target.result;
    };
    
    reader.readAsDataURL(screenshot);
    form.proof_screenshot = screenshot;
};

const submit = () => {
    // Validate required fields before submission
    if (!form.payment_method) {
        alert('Please select a payment method');
        return;
    }
    
    if (!form.access_duration_id) {
        alert('Please select an access duration');
        return;
    }
    
    if ((props.isUpgrade || form.upgrade) && form.selected_subjects.length === 0) {
        alert('Please select at least one subject for your premium account');
        return;
    }
    
    if (!form.amount) {
        alert('Please select an access duration to see the amount');
        return;
    }
    
    if (!form.reference_number || form.reference_number.trim() === '') {
        alert('Please enter a payment reference number');
        return;
    }

    if (!form.terms_accepted) {
        alert('Please read and accept the payment terms and conditions');
        return;
    }

    if (!form.conduct_agreed) {
        alert('Please agree to the community conduct guidelines');
        return;
    }

    // Ensure selected_subjects are integers and clean
    const cleanedSubjects = [...new Set(form.selected_subjects.filter(id => id != null))].map(id => parseInt(id));
    form.selected_subjects = cleanedSubjects;
    
    console.log('Final form data before submission:', {
        payment_method: form.payment_method,
        access_duration_id: form.access_duration_id,
        selected_subjects: form.selected_subjects,
        selected_subjects_array: [...form.selected_subjects],
        selected_subjects_types: form.selected_subjects.map(id => typeof id),
        amount: form.amount,
        upgrade: form.upgrade,
        reference_number: form.reference_number
    });

    // Show processing animation
    showProcessing.value = true;
    
    // Submit the form
    form.post(route('payments.store'), {
        onSuccess: (page) => {
            showProcessing.value = false;
            console.log('Payment submitted successfully');
        },
        onError: (errors) => {
            showProcessing.value = false;
            console.error('Payment errors:', errors);
            console.log('Form data being sent:', {
                payment_method: form.payment_method,
                access_duration_id: form.access_duration_id,
                selected_subjects: form.selected_subjects,
                amount: form.amount,
                upgrade: form.upgrade,
                reference_number: form.reference_number,
                proof_screenshot: form.proof_screenshot ? 'File selected' : 'No file'
            });
            
            // Show specific error messages
            if (errors.selected_subjects) {
                alert('Subject selection error: ' + JSON.stringify(errors.selected_subjects));
            } else if (errors.payment_method) {
                alert('Payment method error: ' + JSON.stringify(errors.payment_method));
            } else if (errors.access_duration_id) {
                alert('Access duration error: ' + JSON.stringify(errors.access_duration_id));
            } else if (errors.amount) {
                alert('Amount error: ' + JSON.stringify(errors.amount));
            } else if (errors.reference_number) {
                alert('Reference number error: ' + JSON.stringify(errors.reference_number));
            } else if (errors.proof_screenshot) {
                alert('Proof screenshot error: ' + JSON.stringify(errors.proof_screenshot));
            } else if (errors.error) {
                alert('Error: ' + JSON.stringify(errors.error));
            } else {
                // Show all errors for debugging
                alert('Validation errors: ' + JSON.stringify(errors));
            }
        },
        onFinish: () => {
            showProcessing.value = false;
        }
    });
};

const setAccessDuration = (duration) => {
    form.access_duration_id = duration.id;
    form.amount = duration.price.toString();
};
</script>

<template>
    <Head title="Submit Payment" />
    
    <DashboardLayout title="Submit Payment" subtitle="Make payment for system access">
        <!-- Processing Overlay -->
        <div v-if="showProcessing" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-200/50 max-w-md w-full mx-4 text-center">
                <div class="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mx-auto mb-4"></div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Processing Payment...</h3>
                <p class="text-slate-600">Please wait while we process your payment submission.</p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Pending Payment Warning -->
            <div v-if="hasPendingPayment" class="mb-6 p-6 bg-amber-50 border border-amber-200 rounded-2xl">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-amber-800">Payment Under Review</h3>
                        <p class="text-sm text-amber-700">You have a payment awaiting verification. Please check your payment history for updates.</p>
                    </div>
                </div>
            </div>

            <!-- Ultra Compact Single Card Layout -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-slate-200/50 max-w-5xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-xl font-bold text-slate-800">Make Payment</h1>
                    <Link :href="route('payments.index')" 
                          class="text-sm px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-all duration-200">
                        Payment History
                    </Link>
                </div>

                <!-- Upgrade Banner -->
                <div v-if="props.isUpgrade" class="mb-4 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-purple-800">Upgrading to Premium</h3>
                            <p class="text-sm text-purple-700">Select subjects and duration for your premium account</p>
                        </div>
                    </div>
                </div>

                <!-- Subject Selection (for upgrades or new premium accounts) -->
                <div v-if="props.isUpgrade || (enrollment?.is_trial && availableSubjects?.length > 0)" class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-700 mb-3">📚 Select Subjects</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div 
                            v-for="subject in availableSubjects" 
                            :key="subject.id"
                            class="relative"
                        >
                            <input 
                                type="checkbox" 
                                :id="`subject-${subject.id}`" 
                                :value="subject.id"
                                v-model="form.selected_subjects"
                                class="sr-only peer"
                                @change="console.log('Subject selected:', subject.id, 'Current selection:', form.selected_subjects)"
                            >
                            <label 
                                :for="`subject-${subject.id}`" 
                                class="flex items-center p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-indigo-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all duration-200"
                            >
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xl">{{ subject.icon }}</span>
                                        <div>
                                            <h4 class="font-medium text-slate-800 text-sm">{{ subject.name }}</h4>
                                            <p class="text-xs text-slate-500">{{ subject.department }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div :class="[
                                    'w-4 h-4 border-2 rounded flex items-center justify-center transition-all duration-200',
                                    form.selected_subjects.includes(subject.id) 
                                        ? 'border-indigo-500 bg-indigo-500' 
                                        : 'border-slate-300'
                                ]">
                                    <svg :class="[
                                        'w-2 h-2 text-white transition-opacity duration-200',
                                        form.selected_subjects.includes(subject.id) ? 'opacity-100' : 'opacity-0'
                                    ]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div v-if="form.errors.selected_subjects" class="mt-2 p-2 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm text-red-600">{{ form.errors.selected_subjects }}</p>
                    </div>
                </div>

                <!-- Main Form in 2 Rows -->
                <div class="space-y-4">
                    <!-- First Row: Duration, Method, Amount -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Access Duration -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-2">📅 Access Duration</h3>
                            <div class="space-y-1">
                                <div v-for="duration in accessDurations" :key="duration.id"
                                     @click="setAccessDuration(duration)"
                                     :class="[
                                         'p-2 rounded-lg border cursor-pointer transition-all text-sm',
                                         form.access_duration_id === duration.id 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-slate-200 hover:border-slate-300'
                                     ]">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-slate-800">{{ duration.name }}</span>
                                        <span class="font-bold text-indigo-600">{{ duration.formatted_price }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.access_duration_id" class="mt-1 text-xs text-red-600">{{ form.errors.access_duration_id }}</div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-2">💳 Payment Method</h3>
                            <div class="space-y-1">
                                <div v-for="method in filteredPaymentMethods" :key="method.code"
                                     @click.prevent="form.payment_method = method.code"
                                     :class="[
                                         'p-2 rounded-lg border cursor-pointer transition-all flex items-center space-x-2 text-sm',
                                         form.payment_method === method.code 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-slate-200 hover:border-slate-300'
                                     ]">
                                    <div :class="[
                                        'w-6 h-6 rounded flex items-center justify-center text-white text-xs',
                                        method.color || 'bg-indigo-500'
                                    ]">
                                        {{ method.icon || '💳' }}
                                    </div>
                                    <span class="font-medium text-slate-800 flex-1">{{ method.name }}</span>
                                    <div v-if="form.payment_method === method.code" class="w-3 h-3 bg-indigo-500 rounded-full"></div>
                                </div>
                            </div>
                            <div v-if="form.errors.payment_method" class="mt-1 text-xs text-red-600">{{ form.errors.payment_method }}</div>
                        </div>

                        <!-- Payment Details -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-2">📝 Payment Details</h3>
                            <div class="space-y-2">
                                <div>
                                    <input v-model="form.amount" type="text" readonly placeholder="Amount"
                                           class="w-full bg-slate-100 px-3 py-2 rounded-lg text-sm font-semibold border-0">
                                </div>
                                <div>
                                    <input v-model="form.reference_number" type="text" placeholder="Reference Number *"
                                           class="w-full bg-white px-3 py-2 rounded-lg text-sm border border-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-400" required>
                                    <div v-if="form.errors.reference_number" class="mt-1 text-xs text-red-600">{{ form.errors.reference_number }}</div>
                                </div>
                                <div class="border border-slate-300 rounded-lg p-2 text-center">
                                    <div v-if="screenshotPreview" class="mb-1">
                                        <img :src="screenshotPreview" alt="Preview" class="w-12 h-12 object-cover rounded mx-auto">
                                    </div>
                                    <button type="button" @click.prevent="selectScreenshot" 
                                            class="text-xs px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded transition-colors">
                                        {{ screenshotPreview ? 'Change Proof' : 'Upload Proof' }}
                                    </button>
                                    <input ref="screenshotInput" type="file" class="hidden" @change="updateScreenshotPreview" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="bg-slate-50/50 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-slate-800 mb-3">Terms and Conditions</h4>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <input 
                                    type="checkbox" 
                                    id="terms_accepted" 
                                    v-model="form.terms_accepted"
                                    class="mt-0.5 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                                <label for="terms_accepted" class="text-sm text-slate-700">
                                    I agree to the 
                                    <button type="button" @click="showTermsModal = true" class="text-indigo-600 hover:text-indigo-800 underline">
                                        payment terms and conditions
                                    </button>
                                    including refund policy (14-day cancellation window)
                                </label>
                            </div>
                            <div class="flex items-start space-x-3">
                                <input 
                                    type="checkbox" 
                                    id="conduct_agreed" 
                                    v-model="form.conduct_agreed"
                                    class="mt-0.5 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                                <label for="conduct_agreed" class="text-sm text-slate-700">
                                    I agree to maintain respectful conduct in system interactions, community discussions, and understand that access will be granted within 24-48 hours after payment approval
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row: Instructions & Submit -->
                    <div v-if="getSelectedMethod" class="bg-slate-50/50 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-slate-800 mb-1">{{ getSelectedMethod.name }} Instructions</h4>
                                <div class="text-xs text-slate-600 space-y-1">
                                    <div v-for="(instruction, index) in getPaymentInstructions(getSelectedMethod)" :key="index" class="flex items-start space-x-1">
                                        <span class="w-4 h-4 bg-indigo-500 text-white rounded-full flex items-center justify-center text-xs font-bold mt-0.5 flex-shrink-0">{{ index + 1 }}</span>
                                        <span>{{ instruction }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <Link :href="route('payments.index')" 
                                      class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm transition-all duration-200">
                                    Cancel
                                </Link>
                                <button type="button"
                                        @click.prevent="submit"
                                        :disabled="form.processing || !form.payment_method || !form.access_duration_id || hasPendingPayment || ((props.isUpgrade || form.upgrade) && form.selected_subjects.length === 0) || !form.terms_accepted || !form.conduct_agreed"
                                        class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg text-sm hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="form.processing">Submitting...</span>
                                    <span v-else>Submit Payment</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions Modal -->
        <div v-if="showTermsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click="showTermsModal = false">
            <div class="bg-white rounded-2xl p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Payment Terms and Conditions</h2>
                    <button @click="showTermsModal = false" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="prose prose-sm max-w-none space-y-6">
                    <section>
                        <h3 class="text-lg font-semibold text-slate-800 mb-3">📋 Payment Terms</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <ul class="space-y-2 text-sm text-slate-700">
                                <li>• Payment must be completed before accessing premium features</li>
                                <li>• Your account will be upgraded to Premium once payment is verified</li>
                                <li>• Access will be granted within 24-48 hours after admin approval</li>
                                <li>• All prices are final and include applicable fees</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-lg font-semibold text-slate-800 mb-3">💸 Refund Policy</h3>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-green-800 mb-2">14-Day Money-Back Guarantee</p>
                                    <ul class="space-y-1 text-sm text-green-700">
                                        <li>• You can cancel and request a full refund within 14 days of enrollment with premium account</li>
                                        <li>• Refunds are only available during the first 14 days from the day of premium enrollment</li>
                                        <li>• After 14 days, no refunds or cancellations will be processed</li>
                                        <li>• Refund requests must be submitted through our support system</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-lg font-semibold text-slate-800 mb-3">👥 Community Guidelines</h3>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                            <ul class="space-y-2 text-sm text-slate-700">
                                <li>• Be respectful and kind to other students and teachers in all interactions</li>
                                <li>• Maintain professional conduct in groups and community discussions</li>
                                <li>• No harassment, bullying, or inappropriate behavior will be tolerated</li>
                                <li>• Respect intellectual property - no unauthorized downloading or sharing of materials</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-lg font-semibold text-slate-800 mb-3">⚠️ Prohibited Activities</h3>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-red-800 font-medium mb-2">The following activities may result in account termination:</p>
                            <ul class="space-y-1 text-sm text-red-700">
                                <li>• Attempting to bypass payment methods or hack the system</li>
                                <li>• Taking unauthorized screenshots or downloading videos</li>
                                <li>• Sharing account credentials with others</li>
                                <li>• Using the platform for illegal or unethical purposes</li>
                                <li>• Violating any terms of service or community guidelines</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-lg font-semibold text-slate-800 mb-3">🎓 School Registration & Exams</h3>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                            <ul class="space-y-2 text-sm text-slate-700">
                                <li>• Once your school selection is approved, you will receive contact details for a student representative</li>
                                <li>• You are responsible for contacting the school directly regarding exam dates and arrangements</li>
                                <li>• The platform provides learning materials but exam registration is handled separately by schools</li>
                                <li>• Failure to write exams due to your own actions will be recorded in the system</li>
                            </ul>
                        </div>
                    </section>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-slate-200">
                    <button @click="showTermsModal = false" class="px-6 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition-colors">
                        Close
                    </button>
                    <button @click="form.terms_accepted = true; showTermsModal = false" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200">
                        Accept Terms
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>