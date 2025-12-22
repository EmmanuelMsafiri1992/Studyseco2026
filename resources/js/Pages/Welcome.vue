<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    subjects: { type: Array, default: () => [] },
    studentCount: { type: Number, default: 500 },
    testimonials: { type: Array, default: () => [] },
    accessDurations: { type: Array, default: () => [] },
    studentStories: { type: Array, default: () => [] },
    paymentMethods: { type: Object, default: () => ({}) }
});

// State management
const showEnrollmentModal = ref(false);
const showSubjectModal = ref(false);
const selectedSubject = ref(null);
const showChatModal = ref(false);
const showLibraryModal = ref(false);
const showChatWidget = ref(false);
const currentSlide = ref(0);
const isNavbarScrolled = ref(false);

// Chat functionality
const chatMessages = ref([
    { id: 'initial-bot', type: 'bot', message: 'Hello! 👋 Welcome to StudySeco. How can I help you today?', timestamp: new Date() }
]);
const newMessage = ref('');
const isTyping = ref(false);

// Live chat support variables  
const supportChatId = ref(localStorage.getItem('studyseco_chat_session') || null);
const chatStatus = ref('disconnected'); // disconnected, waiting, active
const queuePosition = ref(null);
const agentName = ref(null);
const chatPollInterval = ref(null);

// Enrollment form
const enrollmentForm = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    country: '',
    selected_subjects: [],
    enrollment_type: 'trial', // trial or paid
    payment_method_id: '',
    payment_reference: '',
    payment_proof: null,
    terms_accepted: false
});

// Enrollment state management
const enrollmentStep = ref(1); // 1: subjects, 2: info, 3: payment
const selectedSubjects = ref([]);
const showTrialOption = ref(true);
const detectedCountry = ref('');
const detectedCurrency = ref('');

// Get payment methods for the selected country/region
const availablePaymentMethods = computed(() => {
    const countryToRegion = {
        'Malawi': 'malawi',
        'South Africa': 'south_africa', 
        'United States': 'international',
        'Canada': 'international',
        'Australia': 'international',
        'Other': 'international'
    };
    
    const region = countryToRegion[enrollmentForm.country] || 'international';
    return props.paymentMethods?.[region] || [];
});

// Payment state
const showPaymentStep = ref(false);
const selectedRegion = computed(() => {
    if (!enrollmentForm.country) return 'international';
    const country = enrollmentForm.country.toLowerCase();
    if (country === 'malawi') return 'malawi';
    if (country === 'south africa') return 'south_africa';
    return 'international';
});

// Currency and pricing
const getCurrencyInfo = (region) => {
    switch(region) {
        case 'malawi':
            return { currency: 'MWK', price: 50000, symbol: 'MK' };
        case 'south_africa':
            return { currency: 'ZAR', price: 350, symbol: 'R' };
        default:
            return { currency: 'USD', price: 25, symbol: '$' };
    }
};

// Default data with fallbacks
const defaultAccessDurations = [
    { id: 1, name: '6 Months Access', days: 180, price: 50000, description: 'Perfect for semester study' },
    { id: 2, name: '1 Year Access', days: 365, price: 85000, description: 'Full academic year access' },
    { id: 3, name: '2 Years Access', days: 730, price: 150000, description: 'Complete secondary education' }
];

const defaultSubjects = [
    { id: 1, name: 'Mathematics', description: 'Advanced mathematics curriculum' },
    { id: 2, name: 'English', description: 'Language and literature' },
    { id: 3, name: 'Science', description: 'Physics, Chemistry, Biology' },
    { id: 4, name: 'History', description: 'World and African history' }
];

const defaultStories = [
    {
        id: 1,
        name: 'Temwa Mwale',
        location: 'London, UK',
        country_flag: '🇬🇧',
        current_status: 'University of Edinburgh - Engineering',
        story: 'StudySeco allowed me to complete my Malawian secondary education while living in London. The teachers were incredible!',
        msce_credits: 6,
        avatar_color_from: 'emerald-500',
        avatar_color_to: 'blue-500'
    },
    {
        id: 2,
        name: 'Grace Kachingwe',
        location: 'Toronto, Canada',
        country_flag: '🇨🇦',
        current_status: 'University of Toronto - Medicine',
        story: 'The flexibility of online learning while maintaining the Malawian curriculum was perfect for my goals.',
        msce_credits: 8,
        avatar_color_from: 'purple-500',
        avatar_color_to: 'pink-500'
    }
];

const accessDurations = computed(() => props.accessDurations.length ? props.accessDurations : defaultAccessDurations);
const subjects = computed(() => props.subjects.length ? props.subjects : defaultSubjects);
const studentStories = computed(() => props.studentStories.length ? props.studentStories : defaultStories);

// Methods
const toggleEnrollmentModal = () => {
    showEnrollmentModal.value = !showEnrollmentModal.value;
    if (showEnrollmentModal.value) {
        // Reset form state when opening modal
        enrollmentStep.value = 1;
        selectedSubjects.value = [];
        enrollmentForm.selected_subjects = [];
        enrollmentForm.country = detectedCountry.value || '';
        updateCurrencyBasedOnCountry();
    }
};

const showSubjectEnrollment = (subject) => {
    selectedSubject.value = subject;
    showSubjectModal.value = true;
};

// Location detection
const detectUserLocation = async () => {
    try {
        // Try browser geolocation first
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async (position) => {
                // Use reverse geocoding to get country (simplified)
                detectedCountry.value = 'Malawi'; // Default for now
                updateCurrencyBasedOnCountry();
            });
        } else {
            // Fallback to IP-based detection
            try {
                const response = await fetch('https://ipapi.co/json/');
                const data = await response.json();
                detectedCountry.value = data.country_name || 'Malawi';
                updateCurrencyBasedOnCountry();
            } catch (error) {
                detectedCountry.value = 'Malawi';
                updateCurrencyBasedOnCountry();
            }
        }
    } catch (error) {
        detectedCountry.value = 'Malawi';
        updateCurrencyBasedOnCountry();
    }
};

const updateCurrencyBasedOnCountry = () => {
    const country = (enrollmentForm.country || detectedCountry.value).toLowerCase();
    if (country === 'malawi') {
        detectedCurrency.value = 'MWK';
    } else if (country === 'south africa') {
        detectedCurrency.value = 'ZAR';
    } else {
        detectedCurrency.value = 'USD';
    }
};

const toggleSubjectSelection = (subject) => {
    const index = selectedSubjects.value.findIndex(s => s.id === subject.id);
    if (index > -1) {
        selectedSubjects.value.splice(index, 1);
    } else {
        selectedSubjects.value.push(subject);
    }
    enrollmentForm.selected_subjects = selectedSubjects.value.map(s => s.id);
};

const getTotalAmount = () => {
    if (enrollmentForm.enrollment_type === 'trial') return 0;
    
    const pricePerSubject = getCurrencyInfo(selectedRegion.value).price;
    return selectedSubjects.value.length * pricePerSubject;
};

const nextEnrollmentStep = () => {
    if (enrollmentStep.value === 1 && selectedSubjects.value.length === 0) {
        alert('Please select at least one subject');
        return;
    }
    if (enrollmentStep.value < 3) {
        enrollmentStep.value++;
    }
};

const prevEnrollmentStep = () => {
    if (enrollmentStep.value > 1) {
        enrollmentStep.value--;
    }
};

const submitEnrollment = () => {
    // For paid enrollment, ensure we're on step 3 before submission
    if (enrollmentForm.enrollment_type === 'paid' && enrollmentStep.value < 3) {
        nextEnrollmentStep();
        return;
    }
    
    // Validate terms and conditions for all enrollment types
    if (!enrollmentForm.terms_accepted) {
        alert('Please accept the terms and conditions to continue');
        return;
    }
    
    // Validate required fields for paid enrollment on step 3
    if (enrollmentForm.enrollment_type === 'paid' && enrollmentStep.value === 3) {
        if (!enrollmentForm.payment_method_id) {
            alert('Please select a payment method');
            return;
        }
        if (!enrollmentForm.payment_reference) {
            alert('Please enter your payment reference number');
            return;
        }
        if (!enrollmentForm.payment_proof) {
            alert('Please upload your payment proof');
            return;
        }
    }
    
    // Ensure selected subjects are set
    enrollmentForm.selected_subjects = selectedSubjects.value.map(s => s.id);
    
    // For trial enrollments, clear payment fields to avoid validation issues
    if (enrollmentForm.enrollment_type === 'trial') {
        enrollmentForm.payment_method_id = null;
        enrollmentForm.payment_reference = null;
        enrollmentForm.payment_proof = null;
    }
    
    console.log('About to submit enrollment:');
    console.log('- Form data:', enrollmentForm.data());
    console.log('- Selected subjects:', selectedSubjects.value);
    console.log('- Subject IDs:', enrollmentForm.selected_subjects);
    console.log('- Enrollment type:', enrollmentForm.enrollment_type);
    
    enrollmentForm.post(route('enrollment.store'), {
        onSuccess: (response) => {
            console.log('Enrollment successful:', response);
            showEnrollmentModal.value = false;
            enrollmentForm.reset();
            selectedSubjects.value = [];
            enrollmentStep.value = 1;
        },
        onError: (errors) => {
            console.error('Enrollment errors:', errors);
            console.log('Full error object:', errors);
        },
        onFinish: () => {
            console.log('Enrollment request finished');
        }
    });
};

const sendMessage = async () => {
    if (newMessage.value.trim() === '') return;
    if (chatStatus.value === 'closed') {
        chatMessages.value.push({
            id: `error-closed-${Date.now()}`,
            type: 'system',
            message: 'This chat session is closed. You cannot send new messages.',
            timestamp: new Date()
        });
        return;
    }
    
    const userMessage = newMessage.value.trim();
    
    // Add user message to chat immediately
    chatMessages.value.push({
        id: `user-${Date.now()}`,
        type: 'user',
        message: userMessage,
        timestamp: new Date()
    });
    
    newMessage.value = '';
    isTyping.value = true;
    
    try {
        if (!supportChatId.value) {
            // Start new chat session
            const response = await fetch(`http://127.0.0.1:8000/api/chatbot/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    initial_message: userMessage,
                    name: 'Guest User',
                    email: null,
                    priority: 'normal'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                supportChatId.value = data.session_id;
                localStorage.setItem('studyseco_chat_session', data.session_id);
                chatStatus.value = 'waiting';
                queuePosition.value = data.queue_position;
                
                // Add system message
                chatMessages.value.push({
                    id: `local-system-${Date.now()}`,
                    type: 'system',
                    message: `Thanks for your message! You're #${data.queue_position} in queue. A StudySeco representative will respond soon!`,
                    timestamp: new Date()
                });
                
                // Start polling for messages
                startChatPolling();
            } else {
                throw new Error(data.message || 'Failed to start chat');
            }
        } else {
            // Send message in existing session
            const response = await fetch(`http://127.0.0.1:8000/api/chatbot/chat/${supportChatId.value}/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    message: userMessage
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                // Handle specific error cases
                if (data.error && data.error.includes('closed')) {
                    chatStatus.value = 'closed';
                    stopChatPolling();
                    chatMessages.value.push({
                        id: `system-closed-${Date.now()}`,
                        type: 'system',
                        message: 'This chat session has been closed by the support agent.',
                        timestamp: new Date()
                    });
                    return; // Don't throw error, just inform user
                }
                throw new Error(data.error || 'Failed to send message');
            }
        }
    } catch (error) {
        console.error('Error sending message:', error);
        chatMessages.value.push({
            id: `error-${Date.now()}`,
            type: 'system',
            message: 'Sorry, there was an error sending your message. Please try again.',
            timestamp: new Date()
        });
    } finally {
        isTyping.value = false;
    }
};

// Start polling for new messages from agents
const startChatPolling = () => {
    if (chatPollInterval.value) {
        clearInterval(chatPollInterval.value);
    }
    
    chatPollInterval.value = setInterval(async () => {
        if (!supportChatId.value) return;
        
        try {
            const response = await fetch(`http://127.0.0.1:8000/api/chatbot/chat/${supportChatId.value}/messages`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update chat status
                chatStatus.value = data.chat.status;
                queuePosition.value = data.chat.queue_position;
                agentName.value = data.chat.agent_name;
                
                // Stop polling if chat is closed
                if (data.chat.status === 'closed') {
                    stopChatPolling();
                    chatMessages.value.push({
                        id: `system-closed-polling-${Date.now()}`,
                        type: 'system',
                        message: 'This chat session has been closed.',
                        timestamp: new Date()
                    });
                    return;
                }
                
                // Add new messages from agents/system
                data.messages.forEach(message => {
                    if (message.sender_type === 'agent' || message.sender_type === 'system') {
                        // Check if message is already in our chat by ID
                        const exists = chatMessages.value.some(m => 
                            m.id === message.id || 
                            (m.message === message.message && m.type === (message.sender_type === 'agent' ? 'agent' : 'system'))
                        );
                        
                        if (!exists) {
                            chatMessages.value.push({
                                id: message.id,
                                type: message.sender_type === 'agent' ? 'agent' : 'system',
                                message: message.message,
                                senderName: message.sender_name,
                                timestamp: new Date(message.created_at)
                            });
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
    }, 3000); // Poll every 3 seconds
};

// Stop polling when component unmounts
const stopChatPolling = () => {
    if (chatPollInterval.value) {
        clearInterval(chatPollInterval.value);
        chatPollInterval.value = null;
    }
};

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % studentStories.value.length;
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + studentStories.value.length) % studentStories.value.length;
};

const scrollToContact = (e) => {
    e.preventDefault();
    const contactElement = document.getElementById('contact');
    if (contactElement) {
        contactElement.scrollIntoView({ behavior: 'smooth' });
    }
};

// Initialize existing chat session
const initializeChat = async () => {
    if (supportChatId.value) {
        try {
            const response = await fetch(`http://127.0.0.1:8000/api/chatbot/chat/${supportChatId.value}/messages`, {
                headers: { 'Accept': 'application/json' }
            });
            
            const data = await response.json();
            
            if (data.success) {
                chatStatus.value = data.chat.status;
                queuePosition.value = data.chat.queue_position;
                agentName.value = data.chat.agent_name;
                
                // Restore chat history (only non-user messages)
                const nonUserMessages = data.messages.filter(msg => msg.sender_type !== 'user');
                nonUserMessages.forEach(message => {
                    chatMessages.value.push({
                        id: message.id,
                        type: message.sender_type === 'agent' ? 'agent' : 'system',
                        message: message.message,
                        senderName: message.sender_name,
                        timestamp: new Date(message.created_at)
                    });
                });
                
                // Start polling if chat is still active
                if (data.chat.status !== 'closed') {
                    startChatPolling();
                }
            } else {
                // Invalid session, clear it
                supportChatId.value = null;
                localStorage.removeItem('studyseco_chat_session');
            }
        } catch (error) {
            console.error('Error initializing chat:', error);
            supportChatId.value = null;
            localStorage.removeItem('studyseco_chat_session');
        }
    }
};

// Lifecycle
onMounted(() => {
    const handleScroll = () => {
        isNavbarScrolled.value = window.scrollY > 20;
    };
    
    window.addEventListener('scroll', handleScroll);
    
    // Auto-advance testimonial slider
    setInterval(nextSlide, 6000);
    
    // Show chat widget after 3 seconds
    setTimeout(() => {
        showChatWidget.value = true;
    }, 3000);
    
    // Detect user location on mount
    detectUserLocation();
    
    // Initialize chat session if exists
    initializeChat();
});

// Cleanup on unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
    stopChatPolling();
});
</script>

<template>
    <Head title="StudySeco - Complete Malawi Secondary Education Online" />
    
    <!-- Navigation -->
    <nav :class="[
        'fixed top-0 w-full z-50 transition-all duration-500',
        isNavbarScrolled ? 'glass shadow-medium' : 'bg-transparent'
    ]">
        <div class="container-custom">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center shadow-soft">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-secondary-900">StudySeco</span>
                        <span class="text-xs text-secondary-500 font-medium -mt-1">International Education</span>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="#home" class="nav-link text-secondary-700">Home</a>
                    <a href="#subjects" class="nav-link text-secondary-700">Subjects</a>
                    <a href="#about" class="nav-link text-secondary-700">About</a>
                    <button @click="showLibraryModal = true" class="nav-link text-secondary-700">Resources</button>
                    <a href="#contact" @click="scrollToContact" class="nav-link text-secondary-700">Contact</a>
                </div>

                <!-- CTA Buttons -->
                <div class="flex items-center space-x-4" v-if="canLogin">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn-secondary">
                            Dashboard
                        </Link>
                        <button @click="toggleEnrollmentModal" class="btn-primary">
                            Extend/Enroll
                        </button>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn-secondary">
                            Sign In
                        </Link>
                        <button @click="toggleEnrollmentModal" class="btn-primary">
                            Enroll Now
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient hero-pattern min-h-screen flex items-center section-padding">
        <div class="container-custom">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Hero Content -->
                <div class="fade-in-up animate">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-50 border border-primary-200 text-primary-700 text-sm font-medium mb-8">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                        </span>
                        Now accepting international students
                    </div>
                    
                    <h1 class="heading-xl text-secondary-900 mb-6 text-balance">
                        Complete Your 
                        <span class="text-gradient">Malawi Secondary</span>
                        Education Online
                    </h1>
                    
                    <p class="text-xl text-secondary-600 mb-8 leading-relaxed">
                        Join {{ studentCount }}+ students worldwide studying the official Malawi curriculum with certified teachers, flexible schedules, and international accessibility.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <button @click="toggleEnrollmentModal" class="btn-primary btn-lg text-base sm:text-lg py-4 px-6">
                            Start Learning Today
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                                <path d="M5 12h14m-7-7 7 7-7 7"/>
                            </svg>
                        </button>
                        <button @click="showLibraryModal = true" class="btn-secondary btn-lg text-base sm:text-lg py-4 px-6">
                            Explore Resources
                        </button>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="flex items-center space-x-6 text-sm text-secondary-600">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success-500 mr-2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Official Curriculum
                        </div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success-500 mr-2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            Certified Teachers
                        </div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success-500 mr-2">
                                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                            </svg>
                            Global Access
                        </div>
                    </div>
                </div>
                
                <!-- Hero Visual -->
                <div class="relative fade-in-up animate">
                    <div class="relative z-10">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Students learning online" 
                             class="rounded-3xl shadow-hard w-full">
                        
                        <!-- Floating Cards -->
                        <div class="absolute -top-4 -left-4 glass-card p-4 animate-bounce-gentle">
                            <div class="text-2xl font-bold text-primary-600">{{ studentCount }}+</div>
                            <div class="text-sm text-secondary-600">Active Students</div>
                        </div>
                        
                        <div class="absolute -bottom-4 -right-4 glass-card p-4 animate-bounce-gentle" style="animation-delay: 1s;">
                            <div class="text-2xl font-bold text-accent-600">98%</div>
                            <div class="text-sm text-secondary-600">Success Rate</div>
                        </div>
                    </div>
                    
                    <!-- Background Elements -->
                    <div class="absolute -z-10 top-8 left-8 w-72 h-72 bg-primary-200 rounded-full opacity-20 blur-3xl"></div>
                    <div class="absolute -z-10 bottom-8 right-8 w-96 h-96 bg-accent-200 rounded-full opacity-20 blur-3xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Subjects Section -->
    <section id="subjects" class="section-padding bg-white">
        <div class="container-custom">
            <div class="text-center mb-16">
                <h2 class="heading-lg text-secondary-900 mb-4">Our Academic Subjects</h2>
                <p class="text-xl text-secondary-600 max-w-3xl mx-auto">
                    Comprehensive secondary education following the official Malawi curriculum with modern teaching methods
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="subject in subjects" :key="subject.id" class="card-hover p-6 text-center group cursor-pointer" @click="showSubjectEnrollment(subject)">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-100 to-accent-100 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-3xl">{{ subject.icon }}</span>
                    </div>
                    <h3 class="text-lg font-semibold text-secondary-900 mb-2">{{ subject.name }}</h3>
                    <p class="text-sm text-secondary-600 mb-3">{{ subject.description }}</p>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                        {{ subject.department || (subject.type === 'core' ? 'Core Subject' : 'Optional Subject') }}
                    </div>
                    <div class="mt-3 text-xs text-secondary-500">
                        {{ subject.grade_level || 'Form 1-4' }} • Click to learn more
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comprehensive How It Works Guide -->
    <section id="how-it-works" class="section-padding bg-gradient-to-br from-blue-50 via-white to-cyan-50">
        <div class="container-custom">
            <div class="text-center mb-16">
                <h2 class="heading-lg text-secondary-900 mb-4">How StudySeco Works</h2>
                <p class="text-xl text-secondary-600 max-w-3xl mx-auto">
                    Your complete guide to excelling in Malawian secondary education through our innovative online platform
                </p>
            </div>

            <!-- Step-by-Step Process -->
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
                <div>
                    <h3 class="text-3xl font-bold text-secondary-900 mb-6">Your Learning Journey</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0 mt-1">1</div>
                            <div>
                                <h4 class="font-semibold text-secondary-900 mb-2">Enroll & Choose Subjects</h4>
                                <p class="text-secondary-600">Select from our comprehensive Malawi curriculum-aligned subjects including Mathematics, English, Sciences, and more.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0 mt-1">2</div>
                            <div>
                                <h4 class="font-semibold text-secondary-900 mb-2">Learn 100% Online</h4>
                                <p class="text-secondary-600">Access interactive lessons, videos, assignments, and practice tests from anywhere with internet connection.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0 mt-1">3</div>
                            <div>
                                <h4 class="font-semibold text-secondary-900 mb-2">Track Your Progress</h4>
                                <p class="text-secondary-600">Monitor your learning journey with detailed analytics, achievements, and personalized feedback from certified teachers.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0 mt-1">4</div>
                            <div>
                                <h4 class="font-semibold text-secondary-900 mb-2">Choose Exam Centers</h4>
                                <p class="text-secondary-600">Select up to 5 preferred examination centers from our approved Malawian institutions for your physical exams.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl p-6 text-white mb-6">
                        <h4 class="text-xl font-bold mb-3">🎓 Student Success Features</h4>
                        <ul class="space-y-2 text-blue-100">
                            <li class="flex items-center"><span class="mr-2">✓</span> Interactive video lessons</li>
                            <li class="flex items-center"><span class="mr-2">✓</span> Live teacher support</li>
                            <li class="flex items-center"><span class="mr-2">✓</span> Practice examinations</li>
                            <li class="flex items-center"><span class="mr-2">✓</span> Progress tracking</li>
                            <li class="flex items-center"><span class="mr-2">✓</span> Mobile learning app</li>
                            <li class="flex items-center"><span class="mr-2">✓</span> Certificate upon completion</li>
                        </ul>
                    </div>
                    <div class="text-center">
                        <button @click="toggleEnrollmentModal" class="btn bg-gradient-to-r from-blue-600 to-blue-700 text-white btn-lg w-full">
                            Start Your Journey Today
                        </button>
                    </div>
                </div>
            </div>

            <!-- Exam Centers Information -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-16">
                <div class="grid lg:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold text-secondary-900 mb-6">📍 Flexible Exam Center Selection</h3>
                        <div class="space-y-4 mb-6">
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-secondary-700">Choose from <strong>50+ approved examination centers</strong> across Malawi</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-secondary-700">Select up to <strong>5 preferred centers</strong> during registration</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-secondary-700">Receive confirmation <strong>30 days before exam dates</strong></p>
                            </div>
                        </div>
                        
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                            <h4 class="font-semibold text-amber-800 mb-2">🌍 International Students</h4>
                            <p class="text-amber-700 text-sm">
                                Students abroad must select an exam center and <strong>arrive at least 2 weeks before exam dates</strong> 
                                for registration verification and orientation.
                            </p>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-secondary-900 mb-4">Approved Exam Centers Include:</h4>
                        <div class="grid grid-cols-1 gap-2 text-sm">
                            <div class="bg-secondary-50 rounded-lg p-3">
                                <h5 class="font-medium text-secondary-800">Community Day Secondary Schools</h5>
                                <p class="text-secondary-600">Open to external candidates for examinations</p>
                            </div>
                            <div class="bg-secondary-50 rounded-lg p-3">
                                <h5 class="font-medium text-secondary-800">Government Secondary Schools</h5>
                                <p class="text-secondary-600">Full government institutions accepting open students</p>
                            </div>
                            <div class="bg-secondary-50 rounded-lg p-3">
                                <h5 class="font-medium text-secondary-800">Registered Night Schools</h5>
                                <p class="text-secondary-600">Specialized centers for adult and open education</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Malawian Secondary Schools List -->
            <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl p-8">
                <div class="text-center mb-8">
                    <h3 class="text-3xl font-bold text-secondary-900 mb-4">🏫 Approved Examination Centers</h3>
                    <p class="text-secondary-700 max-w-2xl mx-auto">
                        StudySeco partners with these accredited Malawian institutions to provide examination services for open students
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Northern Region -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-bold text-blue-800 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-blue-600 rounded-full mr-2"></span>
                            Northern Region
                        </h4>
                        <ul class="space-y-2 text-sm text-secondary-700">
                            <li>• Mzuzu Secondary School</li>
                            <li>• Ekwendeni Secondary School</li>
                            <li>• Embangweni Secondary School</li>
                            <li>• Rumphi Secondary School</li>
                            <li>• Karonga Secondary School</li>
                            <li>• Chitipa Secondary School</li>
                        </ul>
                    </div>

                    <!-- Central Region -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-bold text-green-800 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-green-600 rounded-full mr-2"></span>
                            Central Region
                        </h4>
                        <ul class="space-y-2 text-sm text-secondary-700">
                            <li>• Lilongwe Secondary School</li>
                            <li>• Kasungu Secondary School</li>
                            <li>• Dowa Secondary School</li>
                            <li>• Salima Secondary School</li>
                            <li>• Nkhotakota Secondary School</li>
                            <li>• Dedza Secondary School</li>
                            <li>• Ntcheu Secondary School</li>
                            <li>• Mchinji Secondary School</li>
                        </ul>
                    </div>

                    <!-- Southern Region -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-bold text-purple-800 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-purple-600 rounded-full mr-2"></span>
                            Southern Region
                        </h4>
                        <ul class="space-y-2 text-sm text-secondary-700">
                            <li>• Blantyre Secondary School</li>
                            <li>• Zomba Secondary School</li>
                            <li>• Chiradzulu Secondary School</li>
                            <li>• Nsanje Secondary School</li>
                            <li>• Chikwawa Secondary School</li>
                            <li>• Thyolo Secondary School</li>
                            <li>• Machinga Secondary School</li>
                            <li>• Mangochi Secondary School</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <div class="bg-white rounded-lg p-6 max-w-2xl mx-auto">
                        <h5 class="font-semibold text-secondary-900 mb-3">📋 Exam Center Selection Process</h5>
                        <div class="grid md:grid-cols-3 gap-4 text-sm">
                            <div class="text-center">
                                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-2 text-xs font-bold">1</div>
                                <p class="text-secondary-700">Choose 5 preferred centers during enrollment</p>
                            </div>
                            <div class="text-center">
                                <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center mx-auto mb-2 text-xs font-bold">2</div>
                                <p class="text-secondary-700">Receive confirmation 30 days before exams</p>
                            </div>
                            <div class="text-center">
                                <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center mx-auto mb-2 text-xs font-bold">3</div>
                                <p class="text-secondary-700">Attend physical exams at assigned center</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Innovation Section -->
            <div class="mt-16 text-center">
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-secondary-900 mb-3">Interactive Learning</h4>
                        <p class="text-secondary-600">Engaging multimedia content, simulations, and interactive exercises make learning enjoyable and effective.</p>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-secondary-900 mb-3">Student-Focused</h4>
                        <p class="text-secondary-600">Personalized learning paths, achievement tracking, and 24/7 support ensure every student succeeds.</p>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-secondary-900 mb-3">Global Community</h4>
                        <p class="text-secondary-600">Connect with students worldwide while maintaining focus on Malawian curriculum and cultural context.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Student Stories Section -->
    <section class="section-padding bg-secondary-50">
        <div class="container-custom">
            <div class="text-center mb-16">
                <h2 class="heading-lg text-secondary-900 mb-4">Student Success Stories</h2>
                <p class="text-xl text-secondary-600">Hear from our graduates studying at top universities worldwide</p>
            </div>
            
            <div class="relative">
                <div class="overflow-hidden rounded-3xl">
                    <div 
                        class="flex transition-transform duration-700 ease-out"
                        :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
                    >
                        <div 
                            v-for="story in studentStories" 
                            :key="story.id"
                            class="w-full flex-shrink-0"
                        >
                            <div class="card-gradient p-12 text-center max-w-4xl mx-auto">
                                <div :class="[
                                    'w-24 h-24 rounded-full mx-auto mb-8 flex items-center justify-center text-white text-2xl font-bold bg-gradient-to-br',
                                    `from-${story.avatar_color_from}`,
                                    `to-${story.avatar_color_to}`
                                ]">
                                    {{ story.name.split(' ').map(n => n[0]).join('') }}
                                </div>
                                
                                <blockquote class="text-2xl font-medium text-secondary-800 mb-8 italic">
                                    "{{ story.story }}"
                                </blockquote>
                                
                                <div class="space-y-2">
                                    <div class="font-semibold text-secondary-900">{{ story.name }}</div>
                                    <div class="text-secondary-600">{{ story.location }} {{ story.country_flag }}</div>
                                    <div class="text-primary-600 font-medium">{{ story.current_status }}</div>
                                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-success-100 text-success-800 text-sm font-medium">
                                        🏆 {{ story.msce_credits }} MSCE Credits
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex justify-center items-center mt-8 space-x-4">
                    <button @click="prevSlide" class="p-3 rounded-full bg-white shadow-soft hover:shadow-medium transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15,18 9,12 15,6"/>
                        </svg>
                    </button>
                    
                    <div class="flex space-x-2">
                        <button 
                            v-for="(story, index) in studentStories" 
                            :key="index"
                            @click="currentSlide = index"
                            :class="[
                                'w-3 h-3 rounded-full transition-all duration-200',
                                currentSlide === index ? 'bg-primary-500' : 'bg-secondary-300'
                            ]"
                        ></button>
                    </div>
                    
                    <button @click="nextSlide" class="p-3 rounded-full bg-white shadow-soft hover:shadow-medium transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Malawian Secondary Schools Section -->
    <section class="section-padding bg-white">
        <div class="container-custom">
            <div class="text-center max-w-4xl mx-auto mb-16">
                <h2 class="heading-lg text-secondary-900 mb-6">
                    Approved Examination Centers in 
                    <span class="text-gradient">Malawi</span>
                </h2>
                <p class="text-xl text-secondary-600 mb-8 leading-relaxed">
                    After completing your online studies, choose from our network of approved secondary schools across Malawi where you can sit for your final examinations. We work with community day secondary schools, full government schools, and night schools that welcome external candidates.
                </p>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-amber-800">Important Information</h3>
                    </div>
                    <div class="text-amber-700 space-y-2">
                        <p>🏫 <strong>Study Online:</strong> Complete 100% of your subject work through our digital platform</p>
                        <p>📝 <strong>Choose 5 Centers:</strong> Select at least 5 preferred examination centers to ensure placement</p>
                        <p>✈️ <strong>International Students:</strong> Arrive at least 2 weeks before examination dates</p>
                        <p>🎓 <strong>Final Exams:</strong> Physical attendance required only for final examinations</p>
                    </div>
                </div>
            </div>

            <!-- Schools Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Community Day Secondary Schools -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-blue-800">Community Day Secondary Schools</h3>
                    </div>
                    <div class="space-y-2 text-blue-700">
                        <p>• Chitipa Community Day Secondary School</p>
                        <p>• Karonga Community Day Secondary School</p>
                        <p>• Mzuzu Community Day Secondary School</p>
                        <p>• Kasungu Community Day Secondary School</p>
                        <p>• Lilongwe Community Day Secondary School</p>
                        <p>• Dedza Community Day Secondary School</p>
                        <p>• Balaka Community Day Secondary School</p>
                        <p>• Zomba Community Day Secondary School</p>
                        <p>• Chiradzulu Community Day Secondary School</p>
                        <p>• Blantyre Community Day Secondary School</p>
                        <p>• Chiromo Community Day Secondary School</p>
                        <p>• Nsanje Community Day Secondary School</p>
                    </div>
                </div>

                <!-- Government Secondary Schools -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M9 7l3-3 3 3M4 10h16v11H4V10z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-green-800">Government Secondary Schools</h3>
                    </div>
                    <div class="space-y-2 text-green-700">
                        <p>• Mzuzu Government Secondary School</p>
                        <p>• Kasungu Government Secondary School</p>
                        <p>• Lilongwe Government Secondary School</p>
                        <p>• Dowa Government Secondary School</p>
                        <p>• Salima Government Secondary School</p>
                        <p>• Dedza Government Secondary School</p>
                        <p>• Ntcheu Government Secondary School</p>
                        <p>• Balaka Government Secondary School</p>
                        <p>• Machinga Government Secondary School</p>
                        <p>• Zomba Government Secondary School</p>
                        <p>• Blantyre Government Secondary School</p>
                        <p>• Chikwawa Government Secondary School</p>
                    </div>
                </div>

                <!-- Night Schools -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-purple-800">Night Schools</h3>
                    </div>
                    <div class="space-y-2 text-purple-700">
                        <p>• Lilongwe Night School</p>
                        <p>• Blantyre Night School</p>
                        <p>• Mzuzu Night School</p>
                        <p>• Zomba Night School</p>
                        <p>• Kasungu Night School</p>
                        <p>• Mangochi Night School</p>
                        <p>• Karonga Night School</p>
                        <p>• Dedza Night School</p>
                        <p>• Salima Night School</p>
                        <p>• Balaka Night School</p>
                        <p>• Nsanje Night School</p>
                        <p>• Chitipa Night School</p>
                    </div>
                </div>
            </div>

            <!-- Selection Process -->
            <div class="mt-16 bg-gradient-to-r from-gray-50 to-white rounded-2xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">How Center Selection Works</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-lg">1</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Complete Studies</h4>
                        <p class="text-gray-600 text-sm">Finish all subject work online through our platform</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-lg">2</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Choose 5 Centers</h4>
                        <p class="text-gray-600 text-sm">Select your preferred examination centers</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-lg">3</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Get Confirmation</h4>
                        <p class="text-gray-600 text-sm">We secure your spot at available centers</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-lg">4</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Sit Exams</h4>
                        <p class="text-gray-600 text-sm">Take your final exams at the assigned center</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section-padding bg-gradient-to-br from-primary-600 to-accent-600 text-white">
        <div class="container-custom text-center">
            <h2 class="heading-lg mb-6">Ready to Start Your Journey?</h2>
            <p class="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
                Join thousands of students who have successfully completed their Malawi secondary education with StudySeco.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button @click="toggleEnrollmentModal" class="btn bg-white text-primary-600 hover:bg-secondary-50 btn-lg">
                    Enroll Today
                </button>
                <button @click="showChatModal = true" class="btn border-2 border-white text-white hover:bg-white hover:text-primary-600 btn-lg">
                    Talk to Advisor
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-secondary-900 text-white py-16">
        <div class="container-custom">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">StudySeco</span>
                    </div>
                    <p class="text-secondary-300 mb-6 max-w-md">
                        Empowering students worldwide with quality Malawi secondary education through innovative online learning.
                    </p>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-secondary-300">
                        <li><a href="#subjects" class="hover:text-white transition-colors">Subjects</a></li>
                        <li><a href="#about" class="hover:text-white transition-colors">About</a></li>
                        <li><button @click="showLibraryModal = true" class="hover:text-white transition-colors">Resources</button></li>
                    </ul>
                </div>
                
                <div id="contact">
                    <h3 class="font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-secondary-300">
                        <li>support@studyseco.com</li>
                        <li>+265 123 456 789</li>
                        <li>Lilongwe, Malawi</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-secondary-700 mt-12 pt-8 text-center text-secondary-400">
                <p>&copy; 2024 StudySeco. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Chat Widget -->
    <div 
        v-if="showChatWidget"
        class="fixed bottom-6 right-6 z-40"
        :class="{ 'animate-bounce-gentle': !showChatModal }"
    >
        <button 
            @click="showChatModal = true"
            class="w-16 h-16 rounded-full bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-hard hover:shadow-glow transition-all duration-300 flex items-center justify-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </button>
    </div>

    <!-- Comprehensive Enrollment Modal -->
    <div v-if="showEnrollmentModal" class="modal-overlay flex items-center justify-center p-4" @click.self="showEnrollmentModal = false">
        <div class="modal-content p-8 max-w-6xl w-full max-h-[95vh] overflow-y-auto">
            <!-- Header with Progress -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-secondary-900">Join StudySeco</h2>
                    <p class="text-secondary-600 mt-1">Excellence in Malawian Secondary Education</p>
                </div>
                <button @click="showEnrollmentModal = false" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Steps -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center space-x-4">
                    <div :class="['flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold transition-colors', enrollmentStep >= 1 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-600']">1</div>
                    <div :class="['w-16 h-1 transition-colors', enrollmentStep >= 2 ? 'bg-primary-600' : 'bg-gray-200']"></div>
                    <div :class="['flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold transition-colors', enrollmentStep >= 2 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-600']">2</div>
                    <div v-if="enrollmentForm.enrollment_type === 'paid'" :class="['w-16 h-1 transition-colors', enrollmentStep >= 3 ? 'bg-primary-600' : 'bg-gray-200']"></div>
                    <div v-if="enrollmentForm.enrollment_type === 'paid'" :class="['flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold transition-colors', enrollmentStep >= 3 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-600']">3</div>
                </div>
            </div>
            
            <form @submit.prevent="submitEnrollment" class="space-y-8">
                <!-- Step 1: Subject Selection & Enrollment Type -->
                <div v-show="enrollmentStep === 1" class="space-y-8">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-semibold text-secondary-900 mb-3">Choose Your Learning Path</h3>
                        <p class="text-secondary-600">Select subjects and enrollment type for your Malawi secondary education journey</p>
                    </div>
                    
                    <!-- Trial vs Paid Options -->
                    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-8 rounded-2xl mb-8">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div :class="['border-2 rounded-xl p-6 cursor-pointer transition-all transform hover:scale-105', enrollmentForm.enrollment_type === 'trial' ? 'border-green-500 bg-green-50 shadow-lg' : 'border-gray-200 hover:border-green-300']" @click="enrollmentForm.enrollment_type = 'trial'">
                                <div class="flex items-center mb-4">
                                    <input type="radio" v-model="enrollmentForm.enrollment_type" value="trial" class="mr-3 text-green-500">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl">🎯</span>
                                        <h5 class="font-bold text-green-700 text-lg">Free 7-Day Trial</h5>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg mb-4">
                                    <p class="text-green-600 font-semibold text-center text-xl">FREE for 7 days</p>
                                </div>
                                <ul class="text-sm text-green-600 space-y-2">
                                    <li class="flex items-center"><span class="mr-2">✓</span>Full access to all features</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>All selected subjects included</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>Email & phone verification required</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>Perfect for exploring StudySeco</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>No payment needed to start</li>
                                </ul>
                            </div>
                            <div :class="['border-2 rounded-xl p-6 cursor-pointer transition-all transform hover:scale-105', enrollmentForm.enrollment_type === 'paid' ? 'border-blue-500 bg-blue-50 shadow-lg' : 'border-gray-200 hover:border-blue-300']" @click="enrollmentForm.enrollment_type = 'paid'">
                                <div class="flex items-center mb-4">
                                    <input type="radio" v-model="enrollmentForm.enrollment_type" value="paid" class="mr-3 text-blue-500">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl">📚</span>
                                        <h5 class="font-bold text-blue-700 text-lg">Full Access (4 Months)</h5>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg mb-4">
                                    <div class="text-center">
                                        <p class="text-blue-600 font-semibold">{{ getCurrencyInfo(selectedRegion).symbol }}{{ getCurrencyInfo(selectedRegion).price }} per subject</p>
                                        <p class="text-xs text-blue-500">4 months access guaranteed</p>
                                    </div>
                                </div>
                                <ul class="text-sm text-blue-600 space-y-2">
                                    <li class="flex items-center"><span class="mr-2">✓</span>4 months unlimited access</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>Pay per subject selected</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>Extension options available</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>Full MANEB curriculum support</li>
                                    <li class="flex items-center"><span class="mr-2">✓</span>Certificate eligible</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subject Selection Grid -->
                    <div>
                        <h4 class="text-xl font-semibold text-secondary-900 mb-6 text-center">Choose Your Subjects (Select at least 1, maximum 12)</h4>
                        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                            <div v-for="subject in subjects" :key="subject.id" 
                                 :class="['card p-4 cursor-pointer transition-all border-2 transform hover:scale-105', selectedSubjects.some(s => s.id === subject.id) ? 'border-primary-500 bg-primary-50 shadow-lg' : 'border-gray-200 hover:border-primary-300']"
                                 @click="toggleSubjectSelection(subject)">
                                <div class="text-center">
                                    <div class="text-3xl mb-3">{{ subject.icon || '📚' }}</div>
                                    <h5 class="font-semibold text-sm text-secondary-900 mb-2">{{ subject.name }}</h5>
                                    <p class="text-xs text-secondary-600 mb-2">{{ subject.grade_level || 'Forms 1-4' }}</p>
                                    <p class="text-xs text-secondary-500 mb-3">{{ subject.department }}</p>
                                    <div v-if="selectedSubjects.some(s => s.id === subject.id)" class="mt-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                            ✓ Selected
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Selection Summary -->
                        <div v-if="selectedSubjects.length > 0" class="bg-secondary-50 p-6 rounded-xl border">
                            <h5 class="font-semibold text-secondary-900 mb-4">Selected Subjects ({{ selectedSubjects.length }})</h5>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-for="subject in selectedSubjects" :key="subject.id" 
                                      class="inline-flex items-center px-3 py-2 rounded-full text-sm bg-primary-100 text-primary-800">
                                    {{ subject.icon || '📚' }} {{ subject.name }}
                                    <button type="button" @click="toggleSubjectSelection(subject)" class="ml-2 hover:text-primary-600 font-bold">×</button>
                                </span>
                            </div>
                            
                            <!-- Cost Summary -->
                            <div class="bg-white p-4 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-secondary-700">Selected Subjects:</span>
                                    <span class="font-semibold">{{ selectedSubjects.length }}</span>
                                </div>
                                <div v-if="enrollmentForm.enrollment_type === 'paid'" class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-secondary-700">Price per Subject:</span>
                                    <span class="font-semibold">{{ getCurrencyInfo(selectedRegion).symbol }}{{ getCurrencyInfo(selectedRegion).price.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-secondary-700">Access Duration:</span>
                                    <span class="font-semibold">{{ enrollmentForm.enrollment_type === 'trial' ? '7 days' : '4 months' }}</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between items-center text-lg font-bold">
                                    <span class="text-secondary-700">Total Amount:</span>
                                    <span :class="enrollmentForm.enrollment_type === 'trial' ? 'text-green-600' : 'text-primary-600'">
                                        {{ enrollmentForm.enrollment_type === 'trial' ? 'FREE' : `${getCurrencyInfo(selectedRegion).symbol}${getTotalAmount().toLocaleString()} ${getCurrencyInfo(selectedRegion).currency}` }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Personal Information & Verification -->
                <div v-show="enrollmentStep === 2" class="space-y-8">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-semibold text-secondary-900 mb-3">Your Information</h3>
                        <p class="text-secondary-600">Provide your details for account creation and verification</p>
                    </div>
                    
                    <!-- Verification Notice -->
                    <div class="bg-amber-50 border border-amber-200 p-6 rounded-xl">
                        <div class="flex items-start space-x-3">
                            <div class="text-amber-500 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-semibold text-amber-800 mb-2">Email & Phone Verification Required</h5>
                                <p class="text-sm text-amber-700">To prevent fake registrations and ensure security, we will verify both your email address and phone number during enrollment. You'll receive verification codes after submitting this form.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Information Form -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Full Name *</label>
                            <input v-model="enrollmentForm.name" type="text" class="form-input" required placeholder="Enter your full legal name">
                            <p class="text-xs text-secondary-600 mt-1">As it appears on your identification</p>
                        </div>
                        <div>
                            <label class="form-label">Email Address *</label>
                            <input v-model="enrollmentForm.email" type="email" class="form-input" required placeholder="your@email.com">
                            <p class="text-xs text-secondary-600 mt-1">Verification email will be sent here</p>
                        </div>
                        <div>
                            <label class="form-label">Phone Number *</label>
                            <input v-model="enrollmentForm.phone" type="tel" class="form-input" required placeholder="+265 123 456 789">
                            <p class="text-xs text-secondary-600 mt-1">SMS verification code will be sent</p>
                        </div>
                        <div>
                            <label class="form-label">Country/Location *</label>
                            <select v-model="enrollmentForm.country" @change="updateCurrencyBasedOnCountry" class="form-input" required>
                                <option value="">Select your country</option>
                                <option value="Malawi">🇲🇼 Malawi</option>
                                <option value="South Africa">🇿🇦 South Africa</option>
                                <option value="United Kingdom">🇬🇧 United Kingdom</option>
                                <option value="United States">🇺🇸 United States</option>
                                <option value="Canada">🇨🇦 Canada</option>
                                <option value="Australia">🇦🇺 Australia</option>
                                <option value="Other">🌍 Other</option>
                            </select>
                            <p class="text-xs text-secondary-600 mt-1">Determines your currency and payment methods</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Address (Optional)</label>
                            <textarea v-model="enrollmentForm.address" class="form-input" rows="2" placeholder="Your current address"></textarea>
                        </div>
                    </div>
                    
                    <!-- Current Selection Summary -->
                    <div class="bg-blue-50 p-6 rounded-xl border border-blue-200">
                        <h5 class="font-semibold text-blue-900 mb-3">Enrollment Summary</h5>
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-blue-700 mb-1"><strong>Enrollment Type:</strong></p>
                                <p class="text-blue-600">{{ enrollmentForm.enrollment_type === 'trial' ? '🎯 7-Day Free Trial' : '📚 4-Month Full Access' }}</p>
                            </div>
                            <div>
                                <p class="text-blue-700 mb-1"><strong>Subjects Selected:</strong></p>
                                <p class="text-blue-600">{{ selectedSubjects.length }} subjects</p>
                            </div>
                            <div>
                                <p class="text-blue-700 mb-1"><strong>Total Cost:</strong></p>
                                <p class="text-blue-600 font-semibold">{{ enrollmentForm.enrollment_type === 'trial' ? 'FREE' : `${getCurrencyInfo(selectedRegion).symbol}${getTotalAmount().toLocaleString()}` }}</p>
                            </div>
                            <div>
                                <p class="text-blue-700 mb-1"><strong>Access Duration:</strong></p>
                                <p class="text-blue-600">{{ enrollmentForm.enrollment_type === 'trial' ? '7 days' : '4 months' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Payment (Only for paid enrollment) -->
                <div v-show="enrollmentStep === 3 && enrollmentForm.enrollment_type === 'paid'" class="space-y-8">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-semibold text-secondary-900 mb-3">Payment & Verification</h3>
                        <p class="text-secondary-600">Complete your enrollment with payment verification</p>
                    </div>
                    
                    <!-- Payment Summary -->
                    <div class="bg-gradient-to-r from-primary-50 to-accent-50 p-8 rounded-2xl border">
                        <h4 class="font-bold text-secondary-900 mb-6 text-center text-xl">Payment Summary</h4>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-secondary-700">Selected Subjects:</span>
                                    <span class="font-semibold">{{ selectedSubjects.length }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-secondary-700">Price per Subject:</span>
                                    <span class="font-semibold">{{ getCurrencyInfo(selectedRegion).symbol }}{{ getCurrencyInfo(selectedRegion).price.toLocaleString() }} {{ getCurrencyInfo(selectedRegion).currency }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-secondary-700">Access Duration:</span>
                                    <span class="font-semibold">4 months (120 days)</span>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-xl border">
                                <div class="text-center">
                                    <p class="text-secondary-600 mb-2">Total Amount Due</p>
                                    <p class="text-3xl font-bold text-primary-600">{{ getCurrencyInfo(selectedRegion).symbol }}{{ getTotalAmount().toLocaleString() }}</p>
                                    <p class="text-sm text-secondary-500">{{ getCurrencyInfo(selectedRegion).currency }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method Selection -->
                    <div>
                        <label class="form-label">Payment Method *</label>
                        <div class="mt-3">
                            <div v-if="availablePaymentMethods.length > 0" class="space-y-3">
                                <div 
                                    v-for="method in availablePaymentMethods" 
                                    :key="method.id"
                                    :class="[
                                        'border-2 rounded-xl p-3 sm:p-4 cursor-pointer transition-all duration-200 hover:shadow-md active:scale-98',
                                        enrollmentForm.payment_method_id == method.id 
                                            ? 'border-primary-500 bg-primary-50 ring-2 ring-primary-200' 
                                            : 'border-gray-200 hover:border-primary-300 hover:bg-gray-50'
                                    ]" 
                                    @click="enrollmentForm.payment_method_id = method.id.toString()"
                                >
                                    <div class="flex items-center">
                                        <input 
                                            type="radio" 
                                            v-model="enrollmentForm.payment_method_id" 
                                            :value="method.id.toString()" 
                                            class="mr-3 flex-shrink-0 w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500" 
                                            @click.stop
                                        >
                                        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                            <span class="text-xl sm:text-2xl flex-shrink-0">{{ method.icon }}</span>
                                            <div class="min-w-0 flex-1">
                                                <h5 class="font-semibold text-sm sm:text-base text-gray-800 leading-tight">{{ method.name }}</h5>
                                                <p class="text-xs sm:text-sm text-gray-600 mt-0.5">{{ method.type }} - {{ method.currency }}</p>
                                                <div v-if="method.instructions" class="text-xs text-gray-500 mt-1 break-words leading-relaxed">
                                                    {{ method.instructions }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="enrollmentForm.payment_method_id == method.id" class="flex-shrink-0 ml-2">
                                            <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center p-8">
                                <p class="text-secondary-600 mb-4">No payment methods available for your selected country.</p>
                                <p class="text-sm text-secondary-500">Please select your country in the previous step or contact support.</p>
                                <!-- Temporary debug info -->
                                <div class="mt-4 p-4 bg-gray-100 rounded text-xs text-left">
                                    <p><strong>Debug Info:</strong></p>
                                    <p>Selected Country: "{{ enrollmentForm.country }}"</p>
                                    <p>Available Regions: {{ Object.keys(paymentMethods || {}).join(', ') }}</p>
                                    <p>Total Payment Methods: {{ Object.values(paymentMethods || {}).flat().length }}</p>
                                    <p>PaymentMethods Object: {{ JSON.stringify(paymentMethods) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Instructions -->
                    <div class="bg-blue-50 p-6 rounded-xl border border-blue-200">
                        <h5 class="font-semibold text-blue-800 mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M16 12l-4-4-4 4"/>
                                <path d="M12 16V8"/>
                            </svg>
                            Payment Instructions
                        </h5>
                        <ol class="text-sm text-blue-700 space-y-2 list-decimal list-inside">
                            <li>Make payment using your selected payment method above</li>
                            <li>Enter the exact transaction reference number you receive</li>
                            <li>Upload clear proof of payment (screenshot or receipt)</li>
                            <li>Submit for verification - processing takes up to 24 hours</li>
                            <li>You'll receive email and SMS confirmation once approved</li>
                        </ol>
                    </div>
                    
                    <!-- Payment Verification Form -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Payment Reference Number *</label>
                            <input v-model="enrollmentForm.payment_reference" 
                                   name="payment_reference"
                                   type="text" 
                                   class="form-input" 
                                   :required="enrollmentForm.enrollment_type === 'paid'" 
                                   placeholder="e.g., TXN123456789">
                            <p class="text-xs text-secondary-600 mt-1">Exactly as shown in your payment confirmation</p>
                        </div>
                        <div>
                            <label class="form-label">Upload Payment Proof *</label>
                            <input type="file" 
                                   name="payment_proof"
                                   @input="enrollmentForm.payment_proof = $event.target.files[0]" 
                                   accept=".jpg,.jpeg,.png,.pdf" 
                                   class="form-input" 
                                   :required="enrollmentForm.enrollment_type === 'paid'">
                            <p class="text-xs text-secondary-600 mt-1">Screenshot or receipt (JPG, PNG, PDF - max 5MB)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <input v-model="enrollmentForm.terms_accepted" type="checkbox" class="mt-1 rounded border-secondary-300 text-primary-600 focus:ring-primary-500" required>
                        <div class="text-sm text-secondary-700 leading-relaxed">
                            <p class="mb-2">
                                I agree to StudySeco's 
                                <a href="#" class="text-primary-600 hover:text-primary-700 underline">Terms of Service</a> 
                                and 
                                <a href="#" class="text-primary-600 hover:text-primary-700 underline">Privacy Policy</a>.
                            </p>
                            <div class="space-y-2 text-xs">
                                <p v-if="enrollmentForm.enrollment_type === 'trial'" class="text-green-600">
                                    ✓ I understand my <strong>7-day free trial</strong> begins immediately upon email/phone verification.
                                </p>
                                <p v-else class="text-blue-600">
                                    ✓ I understand my <strong>4-month access</strong> begins after payment verification and account creation.
                                </p>
                                <div class="bg-amber-50 p-3 rounded border border-amber-200">
                                    <p class="font-medium text-amber-800 mb-2">📋 Cancellation Policy:</p>
                                    <ul class="text-amber-700 space-y-1">
                                        <li>• <strong>14-day cooling period:</strong> Cancel within 14 days for full refund via same payment method</li>
                                        <li>• <strong>Refund processing:</strong> 24-48 hours after cancellation approval</li>
                                        <li>• <strong>No pausing:</strong> Subject access cannot be paused once started</li>
                                        <li>• <strong>Material policy:</strong> Resources are for personal use only, not downloadable or shareable outside the platform</li>
                                    </ul>
                                </div>
                                <p class="text-gray-600">
                                    <strong>Data Usage:</strong> Approximately 2-3GB monthly for optimal learning experience
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="button" @click="enrollmentStep > 1 ? prevEnrollmentStep() : (showEnrollmentModal = false)" 
                            class="btn-secondary flex-1 py-3 text-base sm:text-lg font-medium">
                        {{ enrollmentStep > 1 ? '← Previous' : 'Cancel' }}
                    </button>
                    
                    <button v-if="enrollmentStep < 2 || (enrollmentStep < 3 && enrollmentForm.enrollment_type === 'paid')"
                            type="button" @click="nextEnrollmentStep" class="btn-primary flex-1 py-3 text-base sm:text-lg font-semibold">
                        Continue →
                    </button>
                    
                    <button v-else type="submit" :disabled="enrollmentForm.processing" class="btn-primary flex-1 py-3 text-base sm:text-lg font-semibold">
                        <span v-if="enrollmentForm.processing">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else-if="enrollmentForm.enrollment_type === 'trial'">🎯 Start Free Trial</span>
                        <span v-else>💳 Submit for Payment Verification</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chat Modal -->
    <div v-if="showChatModal" class="fixed inset-0 z-50 pointer-events-none">
        <div class="absolute bottom-20 right-6 w-96 h-96 pointer-events-auto">
            <div class="modal-content w-full h-full flex flex-col">
                <div class="flex justify-between items-center p-6 border-b border-secondary-200">
                    <h3 class="font-semibold text-secondary-900">Chat with StudySeco</h3>
                    <button @click="showChatModal = false" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                
                <div class="flex-1 p-6 overflow-y-auto space-y-4">
                    <!-- Chat Status Indicator -->
                    <div v-if="chatStatus === 'waiting'" class="text-center">
                        <div class="inline-flex items-center px-3 py-2 bg-amber-100 text-amber-700 rounded-full text-sm">
                            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Queue Position: #{{ queuePosition }} 
                        </div>
                    </div>
                    
                    <div v-if="chatStatus === 'active' && agentName" class="text-center">
                        <div class="inline-flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-full text-sm">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            Connected to {{ agentName }}
                        </div>
                    </div>
                    
                    <div v-if="chatStatus === 'closed'" class="text-center">
                        <div class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-full text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Chat Closed
                        </div>
                    </div>
                    
                    <!-- Chat Messages -->
                    <div v-for="message in chatMessages" :key="message.timestamp" :class="[
                        'flex',
                        message.type === 'user' ? 'justify-end' : 'justify-start'
                    ]">
                        <div :class="[
                            'max-w-xs px-4 py-2 rounded-2xl text-sm',
                            message.type === 'user' 
                                ? 'bg-primary-500 text-white rounded-br-md' 
                                : message.type === 'agent'
                                    ? 'bg-green-100 text-green-800 rounded-bl-md border border-green-200'
                                    : message.type === 'system'
                                        ? 'bg-amber-50 text-amber-700 rounded-lg border border-amber-200 italic'
                                        : 'bg-secondary-100 text-secondary-800 rounded-bl-md'
                        ]">
                            <div v-if="message.type === 'agent' && message.senderName" class="text-xs font-semibold mb-1 text-green-600">
                                {{ message.senderName }}
                            </div>
                            <div>{{ message.message }}</div>
                        </div>
                    </div>
                    
                    <div v-if="isTyping" class="flex justify-start">
                        <div class="bg-secondary-100 text-secondary-800 px-4 py-2 rounded-2xl rounded-bl-md text-sm">
                            <span class="animate-pulse">Typing...</span>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 border-t border-secondary-200">
                    <form @submit.prevent="sendMessage" class="flex gap-3">
                        <input 
                            v-model="newMessage" 
                            type="text" 
                            :placeholder="chatStatus === 'closed' ? 'Chat session closed' : 'Type your message...'"
                            :disabled="chatStatus === 'closed'"
                            class="form-input flex-1"
                            :class="{ 'opacity-50 cursor-not-allowed': chatStatus === 'closed' }"
                        >
                        <button type="submit" class="btn-primary" :disabled="chatStatus === 'closed'"
                                :class="{ 'opacity-50 cursor-not-allowed': chatStatus === 'closed' }">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22,2 15,22 11,13 2,9 22,2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Modal -->
    <div v-if="showLibraryModal" class="modal-overlay flex items-center justify-center p-4" @click.self="showLibraryModal = false">
        <div class="modal-content p-8 max-w-4xl w-full">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-secondary-900">Learning Resources</h2>
                <button @click="showLibraryModal = false" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600">
                        <path d="M12 9v4l2 2"/>
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-secondary-900 mb-2">Enrollment Required</h3>
                <p class="text-secondary-600 mb-6">Please enroll in our program to access learning resources including digital library, video lessons, practice tests, and study groups.</p>
                <button @click="showLibraryModal = false; toggleEnrollmentModal()" class="btn-primary px-8 py-3">
                    Enroll Now to Access Resources
                </button>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6 opacity-50 pointer-events-none">
                <div class="card p-6">
                    <h3 class="font-semibold text-secondary-900 mb-4">📚 Digital Library</h3>
                    <p class="text-secondary-600 mb-4">Access textbooks, references, and study materials for all subjects.</p>
                    <button class="btn-secondary w-full">Browse Library</button>
                </div>
                
                <div class="card p-6">
                    <h3 class="font-semibold text-secondary-900 mb-4">🎥 Video Lessons</h3>
                    <p class="text-secondary-600 mb-4">Watch recorded lessons from our certified teachers anytime.</p>
                    <button class="btn-secondary w-full">Watch Lessons</button>
                </div>
                
                <div class="card p-6">
                    <h3 class="font-semibold text-secondary-900 mb-4">📝 Practice Tests</h3>
                    <p class="text-secondary-600 mb-4">Prepare for MSCE with past papers and practice questions.</p>
                    <button class="btn-secondary w-full">Start Practice</button>
                </div>
                
                <div class="card p-6">
                    <h3 class="font-semibold text-secondary-900 mb-4">👥 Study Groups</h3>
                    <p class="text-secondary-600 mb-4">Connect with other students in virtual study sessions.</p>
                    <button class="btn-secondary w-full">Join Groups</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Enrollment Modal -->
    <div v-if="showSubjectModal" class="modal-overlay flex items-center justify-center p-4" @click.self="showSubjectModal = false">
        <div class="modal-content p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-secondary-900">{{ selectedSubject?.name }}</h2>
                <button @click="showSubjectModal = false" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            
            <div class="text-center mb-8">
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-100 to-accent-100 flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">{{ selectedSubject?.icon }}</span>
                </div>
                <h3 class="text-xl font-semibold text-secondary-900 mb-2">{{ selectedSubject?.name }}</h3>
                <p class="text-secondary-600 mb-4">{{ selectedSubject?.description }}</p>
                
                <div class="bg-secondary-50 p-6 rounded-xl mb-6">
                    <h4 class="font-semibold text-secondary-900 mb-3">What you'll learn:</h4>
                    <ul class="text-sm text-secondary-700 space-y-2">
                        <li v-if="selectedSubject?.name === 'Mathematics'">• Algebra, geometry, trigonometry, and calculus</li>
                        <li v-if="selectedSubject?.name === 'Mathematics'">• Problem-solving and analytical thinking</li>
                        <li v-if="selectedSubject?.name === 'Mathematics'">• Statistical analysis and data interpretation</li>
                        
                        <li v-if="selectedSubject?.name === 'English Language'">• Advanced grammar and vocabulary</li>
                        <li v-if="selectedSubject?.name === 'English Language'">• Literature analysis and composition</li>
                        <li v-if="selectedSubject?.name === 'English Language'">• Communication and presentation skills</li>
                        
                        <li v-if="selectedSubject?.name === 'Physics'">• Mechanics, electricity, and magnetism</li>
                        <li v-if="selectedSubject?.name === 'Physics'">• Waves, optics, and modern physics</li>
                        <li v-if="selectedSubject?.name === 'Physics'">• Laboratory experiments and practical applications</li>
                        
                        <li v-if="!['Mathematics', 'English Language', 'Physics'].includes(selectedSubject?.name)">• Comprehensive curriculum covering all key topics</li>
                        <li v-if="!['Mathematics', 'English Language', 'Physics'].includes(selectedSubject?.name)">• Interactive lessons with certified teachers</li>
                        <li v-if="!['Mathematics', 'English Language', 'Physics'].includes(selectedSubject?.name)">• Practice exercises and assessments</li>
                    </ul>
                </div>
                
                <div class="text-center">
                    <div class="flex gap-4">
                        <button @click="showSubjectModal = false" class="btn-secondary flex-1">
                            Browse More Subjects
                        </button>
                        <button @click="showSubjectModal = false; toggleEnrollmentModal()" class="btn-primary flex-1">
                            Enroll Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Mobile-friendly touch interactions */
.active\:scale-98:active {
    transform: scale(0.98);
}

/* Better mobile button sizing */
@media (max-width: 640px) {
    .payment-method-card {
        min-height: 60px;
        touch-action: manipulation;
    }
    
    /* Ensure buttons are touch-friendly on mobile */
    .btn-primary, .btn-secondary {
        min-height: 44px;
        padding: 12px 24px;
    }
    
    /* Better spacing on mobile */
    .space-y-3 > * + * {
        margin-top: 12px;
    }
}

/* Better focus states for accessibility */
input:focus-visible, button:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>