<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    enrollment: Object,
    verificationStatus: String
});

const emailVerificationForm = useForm({
    token: ''
});

const phoneVerificationForm = useForm({
    code: ''
});

const emailSent = ref(false);
const phoneSent = ref(false);
const emailVerified = ref(props.verificationStatus === 'verified' || props.verificationStatus === 'phone_verified');
const phoneVerified = ref(props.verificationStatus === 'verified' || props.verificationStatus === 'email_verified');
const loading = ref(false);
const message = ref('');
const error = ref('');

const sendEmailVerification = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post(`/verification/${props.enrollment.id}/email/send`);
        emailSent.value = true;
        message.value = response.data.message;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send verification email';
    } finally {
        loading.value = false;
    }
};

const sendPhoneVerification = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post(`/verification/${props.enrollment.id}/phone/send`);
        phoneSent.value = true;
        message.value = response.data.message;
        
        // In development, show the code
        if (response.data.code) {
            message.value += ` (Code: ${response.data.code})`;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send verification code';
    } finally {
        loading.value = false;
    }
};

const verifyEmail = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post(`/verification/${props.enrollment.id}/email/verify`, {
            token: emailVerificationForm.token
        });
        
        emailVerified.value = true;
        message.value = response.data.message;
        
        if (response.data.redirect) {
            window.location.href = response.data.redirect;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Invalid verification code';
    } finally {
        loading.value = false;
    }
};

const verifyPhone = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post(`/verification/${props.enrollment.id}/phone/verify`, {
            code: phoneVerificationForm.code
        });
        
        phoneVerified.value = true;
        message.value = response.data.message;
        
        if (response.data.redirect) {
            window.location.href = response.data.redirect;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Invalid verification code';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    // Auto-send verification codes for trial accounts
    if (props.enrollment.is_trial && !emailVerified.value) {
        sendEmailVerification();
    }
    
    if (props.enrollment.is_trial && !phoneVerified.value) {
        setTimeout(() => {
            sendPhoneVerification();
        }, 2000);
    }
});
</script>

<template>
    <Head title="Verify Your Account - StudySeco" />
    
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M9 12l2 2 4-4"/>
                        <path d="M21 12c-1.38 0-2.63.56-3.54 1.46A4 4 0 0 0 12 21a4 4 0 0 0-5.46-6.54A4 4 0 0 0 3 12a4 4 0 0 0 6.54-5.46A4 4 0 0 0 12 3a4 4 0 0 0 7 7 4 4 0 0 0 2 2z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Verify Your Account</h1>
                <p class="text-gray-600">
                    {{ enrollment.is_trial ? 'Complete verification to start your 7-day free trial' : 'Verify your email and phone to complete enrollment' }}
                </p>
            </div>

            <!-- Progress Indicators -->
            <div class="flex items-center justify-center space-x-4 mb-8">
                <div :class="['flex items-center space-x-2', emailVerified ? 'text-green-600' : 'text-gray-400']">
                    <div :class="['w-8 h-8 rounded-full border-2 flex items-center justify-center', emailVerified ? 'bg-green-100 border-green-500' : 'border-gray-300']">
                        <svg v-if="emailVerified" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                        <span v-else class="text-sm font-medium">1</span>
                    </div>
                    <span class="text-sm font-medium">Email</span>
                </div>
                
                <div class="w-8 h-0.5 bg-gray-300"></div>
                
                <div :class="['flex items-center space-x-2', phoneVerified ? 'text-green-600' : 'text-gray-400']">
                    <div :class="['w-8 h-8 rounded-full border-2 flex items-center justify-center', phoneVerified ? 'bg-green-100 border-green-500' : 'border-gray-300']">
                        <svg v-if="phoneVerified" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                        <span v-else class="text-sm font-medium">2</span>
                    </div>
                    <span class="text-sm font-medium">Phone</span>
                </div>
            </div>

            <!-- Verification Form -->
            <div class="bg-white rounded-2xl shadow-xl p-6 space-y-6">
                <!-- Success/Error Messages -->
                <div v-if="message" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">{{ message }}</p>
                </div>
                
                <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">{{ error }}</p>
                </div>

                <!-- Email Verification -->
                <div v-if="!emailVerified" class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">📧 Email Verification</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            We need to verify your email address: <strong>{{ enrollment.email }}</strong>
                        </p>
                        
                        <div v-if="!emailSent">
                            <button @click="sendEmailVerification" :disabled="loading"
                                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span v-if="loading">Sending...</span>
                                <span v-else>Send Verification Email</span>
                            </button>
                        </div>
                        
                        <div v-else class="space-y-4">
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">📨 Verification email sent! Check your inbox and spam folder.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Enter verification code from email:
                                </label>
                                <input v-model="emailVerificationForm.token" 
                                       type="text" 
                                       placeholder="Enter verification code"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div class="flex space-x-3">
                                <button @click="verifyEmail" :disabled="!emailVerificationForm.token || loading"
                                        class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <span v-if="loading">Verifying...</span>
                                    <span v-else>Verify Email</span>
                                </button>
                                
                                <button @click="sendEmailVerification" :disabled="loading"
                                        class="px-4 py-3 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">
                                    Resend
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Verified -->
                <div v-if="emailVerified" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm font-medium text-green-800">Email verified successfully!</span>
                    </div>
                </div>

                <!-- Phone Verification -->
                <div v-if="!phoneVerified" class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">📱 Phone Verification</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            We need to verify your phone number: <strong>{{ enrollment.phone }}</strong>
                        </p>
                        
                        <div v-if="!phoneSent">
                            <button @click="sendPhoneVerification" :disabled="loading"
                                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span v-if="loading">Sending...</span>
                                <span v-else>Send Verification Code</span>
                            </button>
                        </div>
                        
                        <div v-else class="space-y-4">
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">📱 Verification code sent to your phone (and email for testing)!</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Enter 6-digit verification code:
                                </label>
                                <input v-model="phoneVerificationForm.code" 
                                       type="text" 
                                       placeholder="123456"
                                       maxlength="6"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center text-2xl tracking-wider">
                            </div>
                            
                            <div class="flex space-x-3">
                                <button @click="verifyPhone" :disabled="phoneVerificationForm.code.length !== 6 || loading"
                                        class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <span v-if="loading">Verifying...</span>
                                    <span v-else>Verify Phone</span>
                                </button>
                                
                                <button @click="sendPhoneVerification" :disabled="loading"
                                        class="px-4 py-3 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">
                                    Resend
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phone Verified -->
                <div v-if="phoneVerified" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm font-medium text-green-800">Phone verified successfully!</span>
                    </div>
                </div>

                <!-- All Verified -->
                <div v-if="emailVerified && phoneVerified" class="text-center space-y-4">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Verification Complete!</h3>
                    <p class="text-gray-600">
                        {{ enrollment.is_trial ? 'Your 7-day free trial is now active!' : 'Your enrollment is being processed. You\'ll receive login details once approved.' }}
                    </p>
                    
                    <a href="/" class="inline-block bg-indigo-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                        Continue to StudySeco
                    </a>
                </div>
            </div>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Having trouble? Contact us at 
                    <a href="mailto:support@studyseco.com" class="text-indigo-600 hover:text-indigo-500">support@studyseco.com</a>
                </p>
            </div>
        </div>
    </div>
</template>