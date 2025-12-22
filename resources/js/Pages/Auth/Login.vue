<script setup>
import Checkbox from '@/components/Checkbox.vue';
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div>
        <Head title="Login - StudySeco" />
        
        <!-- Professional Login Layout -->
        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-cyan-50">
            <div class="flex min-h-screen">
                
                <!-- Left Side - Branding & Information -->
                <div class="hidden lg:flex lg:flex-1 lg:flex-col lg:justify-center lg:px-20 xl:px-24 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-32 -translate-y-32"></div>
                        <div class="absolute top-1/3 right-0 w-48 h-48 bg-white rounded-full translate-x-24"></div>
                        <div class="absolute bottom-0 left-1/4 w-72 h-72 bg-white rounded-full translate-y-36"></div>
                    </div>
                    
                    <div class="relative z-10 max-w-md">
                        <!-- Logo with Brand Text -->
                        <div class="mb-10">
                            <ApplicationLogo :showText="true" class="h-16 w-16" />
                        </div>
                        
                        <!-- Welcome Message -->
                        <h1 class="text-4xl font-bold mb-4 leading-tight">
                            Welcome to<br />
                            <span class="bg-gradient-to-r from-yellow-200 to-yellow-400 bg-clip-text text-transparent">StudySeco</span>
                        </h1>
                        
                        <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                            Your gateway to excellence in secondary education. Learn online, excel everywhere.
                        </p>
                        
                        <!-- Key Features -->
                        <div class="space-y-4">
                            <div class="flex items-center text-blue-100">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mr-3 flex-shrink-0"></div>
                                <span>100% Online Learning Platform</span>
                            </div>
                            <div class="flex items-center text-blue-100">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mr-3 flex-shrink-0"></div>
                                <span>Malawi Curriculum Aligned</span>
                            </div>
                            <div class="flex items-center text-blue-100">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mr-3 flex-shrink-0"></div>
                                <span>Flexible Exam Center Selection</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Login Form -->
                <div class="flex-1 flex flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
                    <div class="mx-auto w-full max-w-sm lg:w-96">
                        
                        <!-- Mobile Logo -->
                        <div class="lg:hidden text-center mb-8">
                            <ApplicationLogo :showText="true" class="h-12 w-12 mx-auto" />
                        </div>
                        
                        <!-- Login Header -->
                        <div class="text-center lg:text-left">
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Sign in to your account</h2>
                            <p class="text-gray-600 mb-8">
                                Don't have an account? 
                                <Link :href="route('register')" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                    Register here
                                </Link>
                            </p>
                        </div>

                        <!-- Status Message -->
                        <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm font-medium text-green-600">{{ status }}</p>
                        </div>

                        <!-- Login Form -->
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Email Field -->
                            <div>
                                <InputLabel for="email" value="Email Address" class="text-sm font-semibold text-gray-700 mb-2" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Enter your email"
                                />
                                <InputError class="mt-2 text-sm text-red-600" :message="form.errors.email" />
                            </div>

                            <!-- Password Field -->
                            <div>
                                <InputLabel for="password" value="Password" class="text-sm font-semibold text-gray-700 mb-2" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                />
                                <InputError class="mt-2 text-sm text-red-600" :message="form.errors.password" />
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center">
                                    <Checkbox name="remember" v-model:checked="form.remember" class="text-blue-600 focus:ring-blue-500" />
                                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <PrimaryButton
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    {{ form.processing ? 'Signing in...' : 'Sign In' }}
                                </PrimaryButton>
                            </div>
                            
                        </form>

                        <!-- Additional Links -->
                        <div class="mt-8 text-center text-sm text-gray-500">
                            <p>
                                By signing in, you agree to our 
                                <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> 
                                and 
                                <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                            </p>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>
