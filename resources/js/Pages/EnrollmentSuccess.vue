<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    enrollment: Object,
    subjectNames: Array
});

onMounted(() => {
    // Confetti animation
    const createConfetti = () => {
        const colors = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6'];
        const confettiContainer = document.getElementById('confetti-container');
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti-piece';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.animationDelay = Math.random() * 3 + 's';
            confetti.style.animationDuration = Math.random() * 3 + 2 + 's';
            confettiContainer.appendChild(confetti);
        }

        // Remove confetti after animation
        setTimeout(() => {
            if (confettiContainer) {
                confettiContainer.innerHTML = '';
            }
        }, 5000);
    };

    createConfetti();
});
</script>

<template>
    <Head title="Enrollment Successful - StudySeco" />
    
    <GuestLayout>
        <div id="confetti-container" class="fixed inset-0 pointer-events-none z-50"></div>

        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50 flex items-center justify-center p-4">
        <!-- Background particles -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-20 h-20 bg-emerald-500/20 rounded-full animate-pulse"></div>
            <div class="absolute top-40 right-20 w-32 h-32 bg-blue-500/20 rounded-full animate-pulse" style="animation-delay: -2s;"></div>
            <div class="absolute bottom-20 left-20 w-16 h-16 bg-purple-500/20 rounded-full animate-pulse" style="animation-delay: -4s;"></div>
        </div>

        <div class="relative z-10 w-full max-w-4xl">
            <!-- Success Card -->
            <div class="bg-white rounded-3xl p-8 md:p-12 text-center shadow-2xl border border-gray-100">
                <!-- Success Icon -->
                <div class="w-24 h-24 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22,4 12,14.01 9,11.01"/>
                    </svg>
                </div>

                <!-- Success Message -->
                <h1 class="text-4xl md:text-6xl font-black mb-6">
                    <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                        Enrollment Successful! 🎉
                    </span>
                </h1>

                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto leading-relaxed">
                    Thank you for enrolling with StudySeco! Your application has been submitted and is being processed.
                </p>

                <!-- Enrollment Details -->
                <div class="grid md:grid-cols-2 gap-8 mb-12">
                    <!-- Personal Details -->
                    <div class="bg-gray-50 rounded-2xl p-6 text-left">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Your Details
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <label class="text-gray-400 text-sm">Name:</label>
                                <p class="text-gray-900 font-semibold">{{ enrollment.name }}</p>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm">Email:</label>
                                <p class="text-gray-900 font-semibold">{{ enrollment.email }}</p>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm">Phone:</label>
                                <p class="text-gray-900 font-semibold">{{ enrollment.phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Enrollment Details -->
                    <div class="bg-gray-50 rounded-2xl p-6 text-left">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                            Enrollment Details
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <label class="text-gray-400 text-sm">Subjects Selected:</label>
                                <div class="mt-1">
                                    <span v-for="(subject, index) in subjectNames" :key="index" class="inline-block bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded text-xs mr-1 mb-1">
                                        {{ subject }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm">Total Amount:</label>
                                <p class="text-gray-900 font-bold text-lg">{{ enrollment.currency }} {{ parseFloat(enrollment.total_amount).toLocaleString() }}</p>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm">Payment Method:</label>
                                <p class="text-gray-900 font-semibold capitalize">{{ enrollment.payment_method.replace('_', ' ') }}</p>
                            </div>
                            <div>
                                <label class="text-gray-400 text-sm">Reference:</label>
                                <p class="text-gray-900 font-semibold">{{ enrollment.payment_reference }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Information -->
                <div v-if="enrollment.status === 'approved'" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-green-500">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                        Status: Approved ✅
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Congratulations! Your enrollment has been approved and your account is ready for access.
                    </p>
                    <div class="space-y-2 text-left max-w-2xl mx-auto">
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">✅ Application submitted successfully</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">✅ Payment verification completed</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">✅ Account created successfully</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">✅ Welcome email sent with login credentials</span>
                        </div>
                    </div>
                </div>
                
                <div v-else-if="enrollment.status === 'pending'" class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-yellow-500">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                        Status: Processing
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Your enrollment is currently being reviewed by our admissions team. This typically takes 24-48 hours.
                    </p>
                    <div class="space-y-2 text-left max-w-2xl mx-auto">
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-600">✅ Application submitted successfully</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3 animate-pulse"></div>
                            <span class="text-gray-600">⏳ Payment verification in progress</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-gray-400">⏳ Account creation (pending approval)</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <div class="w-3 h-3 bg-gray-400 rounded-full mr-3"></div>
                            <span class="text-gray-400">⏳ Welcome email & login credentials</span>
                        </div>
                    </div>
                </div>
                
                <div v-else-if="enrollment.status === 'rejected'" class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-2xl p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-red-500">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        Status: Rejected
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Unfortunately, your enrollment application was not approved. Please contact support for more information.
                    </p>
                </div>

                <!-- What happens next -->
                <div class="bg-white/5 rounded-2xl p-6 mb-8 text-left">
                    <h3 class="text-xl font-bold text-white mb-4 text-center">What happens next?</h3>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">1</span>
                            </div>
                            <h4 class="font-semibold text-white mb-2">Payment Verification</h4>
                            <p class="text-gray-300 text-sm">Our team verifies your payment proof and reference number.</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">2</span>
                            </div>
                            <h4 class="font-semibold text-white mb-2">Account Creation</h4>
                            <p class="text-gray-300 text-sm">We create your student account and enroll you in selected subjects.</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-white font-bold text-xl">3</span>
                            </div>
                            <h4 class="font-semibold text-white mb-2">Welcome Email</h4>
                            <p class="text-gray-300 text-sm">You'll receive login credentials and access to your student dashboard.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-gradient-to-r from-emerald-500/20 to-blue-500/20 border border-emerald-500/30 rounded-2xl p-6 mb-8">
                    <h3 class="text-xl font-bold text-white mb-4">Need Help?</h3>
                    <p class="text-gray-300 mb-4">If you have any questions about your enrollment, please contact us:</p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="flex items-center justify-center bg-white/10 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400 mr-2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span class="text-white font-semibold">+265 99 123 4567</span>
                        </div>
                        <div class="flex items-center justify-center bg-white/10 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400 mr-2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span class="text-white font-semibold">info@studyseco.com</span>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="space-y-4">
                    <Link href="/" class="inline-block px-8 py-4 bg-gradient-to-r from-emerald-500 to-blue-500 text-white rounded-full font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        Return to Home
                    </Link>
                    
                    <p class="text-gray-400 text-sm">
                        Bookmark this page or save your enrollment ID: <strong class="text-white">#{{ enrollment.id }}</strong>
                    </p>
                </div>
            </div>
        </div>
        </div>
    </GuestLayout>
</template>

<style>
.glass {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.confetti-piece {
    width: 8px;
    height: 8px;
    position: absolute;
    animation: fall linear infinite;
    top: -10px;
}

@keyframes fall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

body {
    background: #0c0c0c;
    color: #fff;
    font-family: 'Inter', sans-serif;
    overflow-x: hidden;
}
</style>