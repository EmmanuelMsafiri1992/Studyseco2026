<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    subject: Object,
    relatedSubjects: Object,
    canEnroll: Boolean
});

const showEnrollmentModal = ref(false);
const currentTab = ref('overview');

// Enrollment form
const enrollmentForm = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    selected_subjects: [props.subject.id],
    payment_method: '',
    payment_reference: '',
    payment_proof: null
});

const handlePaymentProofUpload = (event) => {
    enrollmentForm.payment_proof = event.target.files[0];
};

const submitEnrollment = () => {
    enrollmentForm.post('/enroll', {
        onSuccess: () => {
            showEnrollmentModal.value = false;
        }
    });
};

onMounted(() => {
    // Scroll reveal animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-up').forEach(el => {
        observer.observe(el);
    });

    // Floating elements animation
    const floatingElements = document.querySelectorAll('.floating');
    floatingElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.5}s`;
    });

    // Stats counter animation
    const statsElements = document.querySelectorAll('.stat-number');
    statsElements.forEach(el => {
        const target = parseInt(el.textContent);
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.floor(current);
        }, 20);
    });

    // Parallax effect
    window.addEventListener('scroll', () => {
        const scrollY = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax');
        parallaxElements.forEach(el => {
            const speed = el.dataset.speed || 0.5;
            el.style.transform = `translateY(${scrollY * speed}px)`;
        });
    });

    // Interactive resource cards
    const resourceCards = document.querySelectorAll('.resource-card');
    resourceCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px) scale(1.02)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>

<template>
    <Head :title="`${subject.name} - StudySeco`" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-lg border-b border-gray-200/50 shadow-sm">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex items-center justify-between h-16">
                    <Link href="/" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-emerald-500 to-blue-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">StudySeco</span>
                    </Link>

                    <div class="flex items-center space-x-4">
                        <Link href="/" class="px-4 py-2 text-gray-700 hover:text-emerald-600 transition-colors">
                            ← Back to Home
                        </Link>
                        <button
                            @click="showEnrollmentModal = true"
                            class="px-6 py-2 bg-gradient-to-r from-emerald-500 to-blue-500 text-white rounded-full hover:shadow-lg transition-all duration-300 font-medium"
                        >
                            Enroll Now
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-20 pb-16 relative overflow-hidden">
            <!-- Floating decorative elements -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
                <div class="floating absolute top-20 left-10 w-16 h-16 bg-emerald-200/30 rounded-full"></div>
                <div class="floating absolute top-32 right-20 w-24 h-24 bg-blue-200/30 rounded-full"></div>
                <div class="floating absolute bottom-20 left-1/4 w-12 h-12 bg-purple-200/30 rounded-full"></div>
                <div class="floating absolute bottom-32 right-1/3 w-20 h-20 bg-cyan-200/30 rounded-full"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="fade-up">
                        <!-- Subject badge -->
                        <div class="inline-flex items-center px-4 py-2 bg-white/80 backdrop-blur border border-emerald-200 rounded-full text-sm font-medium mb-6">
                            <span class="text-2xl mr-2">{{ subject.icon }}</span>
                            <span class="text-emerald-600 font-semibold">{{ subject.type === 'core' ? 'Core Subject' : 'Optional Subject' }}</span>
                        </div>

                        <h1 class="text-5xl md:text-6xl font-black mb-6 leading-tight">
                            <span class="text-gray-900">{{ subject.name }}</span>
                        </h1>

                        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                            {{ subject.detailed_description }}
                        </p>

                        <!-- Key stats -->
                        <div class="grid grid-cols-3 gap-6 mb-8">
                            <div class="text-center">
                                <div class="stat-number text-2xl font-bold text-emerald-600">{{ subject.students_enrolled }}</div>
                                <div class="text-sm text-gray-500">Students Enrolled</div>
                            </div>
                            <div class="text-center">
                                <div class="stat-number text-2xl font-bold text-blue-600">{{ subject.completion_rate }}</div>
                                <div class="text-sm text-gray-500">% Success Rate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ subject.average_grade }}</div>
                                <div class="text-sm text-gray-500">Average Grade</div>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button
                                @click="showEnrollmentModal = true"
                                class="group px-8 py-4 bg-gradient-to-r from-emerald-500 to-blue-500 text-white rounded-full font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                            >
                                <span class="flex items-center justify-center">
                                    Enroll for MKW {{ subject.price.toLocaleString() }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 group-hover:translate-x-1 transition-transform">
                                        <path d="M5 12h14"/>
                                        <path d="M12 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </button>
                            <button 
                                @click="currentTab = 'curriculum'"
                                class="px-8 py-4 border-2 border-gray-300 text-gray-700 rounded-full font-semibold text-lg hover:border-emerald-500 hover:text-emerald-600 transition-all duration-300"
                            >
                                View Curriculum
                            </button>
                        </div>
                    </div>

                    <!-- Interactive visual element -->
                    <div class="fade-up relative">
                        <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-all duration-500">
                            <!-- Subject icon with animation -->
                            <div class="text-center mb-8">
                                <div class="w-24 h-24 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-3xl flex items-center justify-center mx-auto mb-4 animate-bounce">
                                    <span class="text-4xl">{{ subject.icon }}</span>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Start Learning Today</h3>
                                <p class="text-gray-600">Join {{ subject.students_enrolled }}+ students already enrolled</p>
                            </div>

                            <!-- Progress simulation -->
                            <div class="space-y-4 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Subject Progress</span>
                                    <span class="text-emerald-600 font-semibold">{{ subject.completion_rate }}% Complete</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div 
                                        class="bg-gradient-to-r from-emerald-500 to-blue-500 h-3 rounded-full progress-bar" 
                                        :style="`width: ${subject.completion_rate}%`"
                                    ></div>
                                </div>
                            </div>

                            <!-- Features list -->
                            <div class="space-y-3">
                                <div class="flex items-center text-sm">
                                    <div class="w-4 h-4 bg-emerald-500 rounded-full mr-3 flex-shrink-0"></div>
                                    <span class="text-gray-700">{{ subject.duration }} comprehensive curriculum</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full mr-3 flex-shrink-0"></div>
                                    <span class="text-gray-700">Interactive learning resources</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <div class="w-4 h-4 bg-purple-500 rounded-full mr-3 flex-shrink-0"></div>
                                    <span class="text-gray-700">Expert teacher support</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <div class="w-4 h-4 bg-cyan-500 rounded-full mr-3 flex-shrink-0"></div>
                                    <span class="text-gray-700">MSCE examination preparation</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tabs Navigation -->
        <section class="sticky top-16 z-40 bg-white/95 backdrop-blur border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex space-x-8 overflow-x-auto">
                    <button 
                        @click="currentTab = 'overview'"
                        :class="currentTab === 'overview' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
                        class="py-4 px-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
                    >
                        Overview
                    </button>
                    <button 
                        @click="currentTab = 'curriculum'"
                        :class="currentTab === 'curriculum' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
                        class="py-4 px-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
                    >
                        Curriculum
                    </button>
                    <button 
                        @click="currentTab = 'resources'"
                        :class="currentTab === 'resources' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
                        class="py-4 px-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
                    >
                        Resources
                    </button>
                    <button 
                        @click="currentTab = 'community'"
                        :class="currentTab === 'community' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
                        class="py-4 px-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
                    >
                        Community
                    </button>
                    <button 
                        @click="currentTab = 'teacher'"
                        :class="currentTab === 'teacher' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-600 hover:text-gray-900'"
                        class="py-4 px-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
                    >
                        Your Teacher
                    </button>
                </div>
            </div>
        </section>

        <!-- Tab Content -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Overview Tab -->
                <div v-show="currentTab === 'overview'" class="space-y-16">
                    <!-- Why Choose This Subject -->
                    <div class="fade-up">
                        <h2 class="text-4xl font-bold text-center mb-12">
                            <span class="text-gray-900">Why Choose </span>
                            <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">{{ subject.name }}?</span>
                        </h2>
                        <div class="grid md:grid-cols-2 gap-8">
                            <div v-for="(reason, index) in subject.why_choose" :key="index" class="flex items-start space-x-4 fade-up" :style="`animation-delay: ${index * 0.1}s`">
                                <div class="w-8 h-8 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                        <polyline points="20,6 9,17 4,12"/>
                                    </svg>
                                </div>
                                <p class="text-gray-700 text-lg leading-relaxed">{{ reason }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Career Opportunities -->
                    <div class="fade-up bg-gradient-to-r from-emerald-50 to-blue-50 rounded-3xl p-12">
                        <h3 class="text-3xl font-bold text-center mb-8 text-gray-900">Career Opportunities</h3>
                        <p class="text-center text-gray-600 mb-8 text-lg">Excel in {{ subject.name }} opens doors to exciting career paths:</p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <div v-for="(career, index) in subject.career_paths" :key="index" class="px-6 py-3 bg-white rounded-full shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                                <span class="text-gray-800 font-medium">{{ career }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Success Stories -->
                    <div class="fade-up">
                        <h3 class="text-3xl font-bold text-center mb-12 text-gray-900">Student Success Stories</h3>
                        <div class="grid md:grid-cols-2 gap-8">
                            <div v-for="(story, index) in subject.success_stories" :key="index" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                        {{ story.name.split(' ').map(n => n[0]).join('') }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ story.name }}</h4>
                                        <p class="text-emerald-600 text-sm font-medium">{{ story.achievement }}</p>
                                    </div>
                                </div>
                                <blockquote class="text-gray-700 italic leading-relaxed">
                                    "{{ story.quote }}"
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Curriculum Tab -->
                <div v-show="currentTab === 'curriculum'" class="space-y-12">
                    <div class="fade-up text-center">
                        <h2 class="text-4xl font-bold mb-6">
                            <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">Comprehensive Curriculum</span>
                        </h2>
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                            Our {{ subject.name }} curriculum is designed to build strong foundations and prepare you for MSCE success.
                        </p>
                    </div>

                    <div class="grid gap-8">
                        <div v-for="(highlight, index) in subject.curriculum_highlights" :key="index" class="fade-up bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                            <div class="flex items-start space-x-4">
                                <div class="w-8 h-8 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 mt-1">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ highlight.split(':')[0] }}</h3>
                                    <p class="text-gray-700 leading-relaxed">{{ highlight.split(':')[1] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resources Tab -->
                <div v-show="currentTab === 'resources'" class="space-y-12">
                    <div class="fade-up text-center">
                        <h2 class="text-4xl font-bold mb-6">
                            <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">Learning Resources</span>
                        </h2>
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                            Access a comprehensive suite of interactive tools and materials designed to enhance your learning experience.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div v-for="(resource, index) in subject.resources" :key="index" class="resource-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center hover:shadow-xl transition-all duration-300">
                            <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <span class="text-3xl">{{ resource.icon }}</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ resource.title }}</h3>
                            <p class="text-gray-600 leading-relaxed">{{ resource.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Community Tab -->
                <div v-show="currentTab === 'community'" class="space-y-12">
                    <div class="fade-up text-center">
                        <h2 class="text-4xl font-bold mb-6">
                            <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">Student Community</span>
                        </h2>
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                            Connect, collaborate, and learn together with fellow {{ subject.name }} students from around the world.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div v-for="(interaction, index) in subject.student_interactions" :key="index" class="fade-up bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full flex items-center justify-center mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Community Activity</h3>
                            </div>
                            <p class="text-gray-700 leading-relaxed">{{ interaction }}</p>
                        </div>
                    </div>
                </div>

                <!-- Teacher Tab -->
                <div v-show="currentTab === 'teacher'" class="space-y-12">
                    <div class="fade-up max-w-4xl mx-auto">
                        <div class="bg-white rounded-3xl p-12 shadow-xl border border-gray-100">
                            <div class="text-center mb-8">
                                <div class="w-32 h-32 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-full mx-auto mb-6 flex items-center justify-center">
                                    <span class="text-5xl text-white font-bold">{{ subject.teacher_profile.name.split(' ').map(n => n[0]).join('') }}</span>
                                </div>
                                <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ subject.teacher_profile.name }}</h2>
                                <p class="text-xl text-emerald-600 font-semibold mb-4">Your {{ subject.name }} Teacher</p>
                            </div>

                            <div class="grid md:grid-cols-3 gap-8 text-center mb-8">
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-2">Qualification</h3>
                                    <p class="text-gray-600">{{ subject.teacher_profile.qualification }}</p>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-2">Experience</h3>
                                    <p class="text-gray-600">{{ subject.teacher_profile.experience }}</p>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-2">Specialty</h3>
                                    <p class="text-gray-600">{{ subject.teacher_profile.specialty }}</p>
                                </div>
                            </div>

                            <div class="text-center">
                                <button
                                    @click="showEnrollmentModal = true"
                                    class="px-8 py-4 bg-gradient-to-r from-emerald-500 to-blue-500 text-white rounded-full font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                                >
                                    Start Learning with {{ subject.teacher_profile.name.split(' ')[1] }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Subjects -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="fade-up text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Explore Other Subjects</h2>
                    <p class="text-gray-600">Build a comprehensive education with our other offerings</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <Link 
                        v-for="relatedSubject in Object.values(relatedSubjects)" 
                        :key="relatedSubject.id"
                        :href="route('subject.detail', relatedSubject.id)"
                        class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-2 group"
                    >
                        <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-blue-500 rounded-2xl flex items-center justify-center mb-4">
                            <span class="text-2xl">{{ relatedSubject.icon }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-emerald-600 transition-colors">{{ relatedSubject.name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ relatedSubject.description }}</p>
                        <div class="text-emerald-600 font-semibold">MKW {{ relatedSubject.price.toLocaleString() }}</div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-24 bg-gradient-to-r from-emerald-600 to-blue-600">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <div class="fade-up">
                    <h2 class="text-4xl font-bold text-white mb-6">Ready to Master {{ subject.name }}?</h2>
                    <p class="text-xl text-emerald-100 mb-8">
                        Join {{ subject.students_enrolled }}+ students already learning {{ subject.name }} with StudySeco
                    </p>
                    <button
                        @click="showEnrollmentModal = true"
                        class="px-12 py-4 bg-white text-emerald-600 rounded-full font-bold text-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1"
                    >
                        Enroll for MKW {{ subject.price.toLocaleString() }}
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- Enrollment Modal -->
    <div v-show="showEnrollmentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Enroll in {{ subject.name }}</h2>
                <button @click="showEnrollmentModal = false" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-all">
                    ✕
                </button>
            </div>

            <form @submit.prevent="submitEnrollment" class="space-y-6">
                <!-- Subject Selection (Pre-selected) -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ subject.icon }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ subject.name }}</h3>
                                <p class="text-sm text-gray-600">{{ subject.duration }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-bold text-emerald-600">MKW {{ subject.price.toLocaleString() }}</div>
                            <div class="text-sm text-gray-600">One-time payment</div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                        <input v-model="enrollmentForm.name" type="text" required class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                        <input v-model="enrollmentForm.email" type="email" required class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                        <input v-model="enrollmentForm.phone" type="tel" required class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Address *</label>
                        <input v-model="enrollmentForm.address" type="text" required class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div @click="enrollmentForm.payment_method = 'tnm_mpamba'"
                             class="p-4 rounded-xl border-2 cursor-pointer transition-all text-center"
                             :class="enrollmentForm.payment_method === 'tnm_mpamba' 
                                 ? 'border-emerald-500 bg-emerald-50' 
                                 : 'border-gray-300 hover:border-gray-400'">
                            <div class="text-2xl mb-2">📱</div>
                            <h4 class="font-semibold text-gray-900 text-sm">TNM Mpamba</h4>
                        </div>
                        <div @click="enrollmentForm.payment_method = 'airtel_money'"
                             class="p-4 rounded-xl border-2 cursor-pointer transition-all text-center"
                             :class="enrollmentForm.payment_method === 'airtel_money' 
                                 ? 'border-emerald-500 bg-emerald-50' 
                                 : 'border-gray-300 hover:border-gray-400'">
                            <div class="text-2xl mb-2">💸</div>
                            <h4 class="font-semibold text-gray-900 text-sm">Airtel Money</h4>
                        </div>
                        <div @click="enrollmentForm.payment_method = 'bank_transfer'"
                             class="p-4 rounded-xl border-2 cursor-pointer transition-all text-center"
                             :class="enrollmentForm.payment_method === 'bank_transfer' 
                                 ? 'border-emerald-500 bg-emerald-50' 
                                 : 'border-gray-300 hover:border-gray-400'">
                            <div class="text-2xl mb-2">🏦</div>
                            <h4 class="font-semibold text-gray-900 text-sm">Bank Transfer</h4>
                        </div>
                    </div>
                </div>

                <!-- Payment Details (simplified for individual subject) -->
                <div v-if="enrollmentForm.payment_method">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 text-center">
                        <h4 class="font-semibold text-gray-900 mb-2">Payment Instructions</h4>
                        <p class="text-gray-700 mb-2">Send MKW {{ subject.price.toLocaleString() }} to complete enrollment</p>
                        <p class="text-sm text-gray-600">Reference: {{ subject.name.toUpperCase().replace(/\s+/g, '') }}-{{ Date.now() }}</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Payment Reference *</label>
                            <input v-model="enrollmentForm.payment_reference" type="text" required class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Upload Payment Proof *</label>
                            <input @change="handlePaymentProofUpload" type="file" accept="image/*,.pdf" required class="w-full bg-gray-50 border border-gray-300 rounded-lg py-3 px-4 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div v-if="enrollmentForm.payment_method" class="pt-4">
                    <button type="submit" :disabled="enrollmentForm.processing" class="w-full px-6 py-4 bg-gradient-to-r from-emerald-500 to-blue-500 text-white rounded-full font-semibold text-lg hover:shadow-xl transition-all duration-300 disabled:opacity-50">
                        <span v-if="enrollmentForm.processing">Processing...</span>
                        <span v-else>Complete Enrollment - MKW {{ subject.price.toLocaleString() }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* Animation keyframes */
@keyframes floating {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    33% { transform: translateY(-10px) rotate(2deg); }
    66% { transform: translateY(5px) rotate(-1deg); }
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes progressBar {
    from { width: 0; }
    to { width: var(--progress); }
}

/* Animation classes */
.floating {
    animation: floating 6s ease-in-out infinite;
}

.fade-up {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s cubic-bezier(0.1, 0.2, 0.3, 1), transform 0.8s cubic-bezier(0.1, 0.2, 0.3, 1);
}

.fade-up.animate-in {
    opacity: 1;
    transform: translateY(0);
}

.progress-bar {
    animation: progressBar 2s ease-out forwards;
}

.resource-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.resource-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Parallax elements */
.parallax {
    will-change: transform;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #10B981, #3B82F6);
    border-radius: 3px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .fade-up {
        transform: translateY(20px);
    }
}
</style>