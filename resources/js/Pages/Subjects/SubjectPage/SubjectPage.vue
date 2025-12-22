<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';

const props = defineProps({
    auth: Object,
    subject: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const showSidebar = ref(true);
const isMobile = ref(false);
const expandedModules = ref(new Set());
const selectedLesson = ref(null);

// AI Tutor Chat functionality
const showAIChat = ref(true);
const chatMessages = ref([
    {
        id: 1,
        sender: 'ai',
        message: `Hi! I'm your AI ${props.subject.name} tutor. Ask me anything about the lesson or ${props.subject.name.toLowerCase()} topics!`,
        timestamp: new Date()
    },
    {
        id: 2,
        sender: 'ai', 
        message: `Welcome to the ${props.subject.name} lesson! I'm here to help you understand any concepts. What would you like to know about ${props.subject.name.toLowerCase()} techniques or practices?`,
        timestamp: new Date()
    }
]);
const newMessage = ref('');
const chatContainer = ref(null);

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

const toggleModule = (topicId) => {
    if (expandedModules.value.has(topicId)) {
        expandedModules.value.delete(topicId);
    } else {
        expandedModules.value.add(topicId);
    }
};

const getLessonTypeIcon = (lesson) => {
    if (lesson.video_path) return 'video';
    if (lesson.type === 'quiz') return 'quiz';
    if (lesson.type === 'text') return 'text';
    return 'document';
};

const getLessonProgress = (lesson) => {
    // This would come from actual progress tracking
    return Math.random() > 0.5 ? 100 : 0;
};

const toggleAIChat = () => {
    showAIChat.value = !showAIChat.value;
};

const sendMessage = () => {
    if (newMessage.value.trim()) {
        // Add user message
        chatMessages.value.push({
            id: Date.now(),
            sender: 'user',
            message: newMessage.value.trim(),
            timestamp: new Date()
        });
        
        const userMsg = newMessage.value.trim();
        newMessage.value = '';
        
        // Simulate AI response
        setTimeout(() => {
            chatMessages.value.push({
                id: Date.now(),
                sender: 'ai',
                message: generateAIResponse(userMsg),
                timestamp: new Date()
            });
            
            // Scroll to bottom
            if (chatContainer.value) {
                chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
            }
        }, 1000);
    }
};

const generateAIResponse = (userMessage) => {
    const subjectLower = props.subject.name.toLowerCase();
    const responses = [
        `That's a great question about ${subjectLower}! Let me help you with that concept.`,
        `In ${subjectLower} practices, this relates to fundamental principles and techniques.`,
        `This topic is fundamental to understanding ${subjectLower} concepts.`,
        `Let me explain how this applies to ${subjectLower} techniques and practices.`,
        `This concept is important for understanding ${subjectLower} fundamentals.`
    ];
    return responses[Math.floor(Math.random() * responses.length)];
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Expand all modules by default
    if (props.subject?.topics) {
        props.subject.topics.forEach(topic => {
            expandedModules.value.add(topic.id);
        });
    }
    
    // Select first lesson if available
    if (props.subject?.topics?.[0]?.lessons?.[0]) {
        selectedLesson.value = props.subject.topics[0].lessons[0];
    }
    
    // Scroll chat to bottom on mount
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
});
</script>

<template>
    <Head :title="subject.name" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 font-sans text-slate-800">
        <!-- Header -->
        <header class="h-16 bg-white/90 backdrop-blur-xl border-b border-slate-200/50 px-4 md:px-6 flex items-center justify-between relative z-50">
            <div class="flex items-center space-x-4 flex-1 min-w-0">
                <Link :href="route('subjects.index')" class="p-2 hover:bg-slate-100 rounded-xl transition-colors duration-200 flex-shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </Link>
                <button @click="toggleSidebar" class="p-2 hover:bg-slate-100 rounded-xl transition-colors duration-200 lg:hidden">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Mobile AI Chat Toggle -->
                <button @click="toggleAIChat" 
                        :class="[
                            'p-2 rounded-xl transition-all duration-200 lg:hidden',
                            showAIChat ? 'bg-green-100 text-green-600' : 'hover:bg-slate-100 text-slate-600'
                        ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm md:text-lg font-bold text-slate-800 truncate">{{ subject?.name }}</h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 flex-shrink-0">
                <!-- AI Chat Toggle -->
                <button @click="toggleAIChat" 
                        :class="[
                            'p-2 rounded-xl transition-all duration-200 hidden lg:block',
                            showAIChat ? 'bg-green-100 text-green-600' : 'hover:bg-slate-100 text-slate-600'
                        ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                    </svg>
                </button>
                
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-slate-800">{{ user.name }}</p>
                    <p class="text-xs text-slate-500">{{ user.role }}</p>
                </div>
                <img :src="user.profile_photo_url || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face&facepad=2&bg=white'" :alt="user.name" class="h-8 w-8 md:h-10 md:w-10 rounded-xl ring-2 ring-white shadow-md">
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex h-[calc(100vh-64px)]">
            <!-- Left Sidebar - Subject Structure -->
            <div :class="[
                'bg-white/90 backdrop-blur-xl border-r border-slate-200/50 transition-all duration-300 overflow-hidden flex flex-col',
                showSidebar ? 'w-80' : 'w-0',
                isMobile ? 'fixed inset-y-16 left-0 z-40 shadow-xl' : ''
            ]">
                <!-- Sidebar Header -->
                <div class="p-4 border-b border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-800">Subject Content</h2>
                        <button @click="toggleSidebar" class="p-1 hover:bg-slate-100 rounded-lg lg:hidden">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-sm text-slate-600 mt-1">
                        {{ subject.topics?.length || 0 }}/{{ subject.topics?.length || 0 }}
                    </div>
                </div>

                <!-- Subject Progress Overview -->
                <div class="p-4 border-b border-slate-200/50">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Progress</span>
                        <span class="text-slate-800 font-medium">0/{{ subject.topics?.reduce((acc, topic) => acc + (topic.lessons?.length || 0), 0) || 0 }} lessons</span>
                    </div>
                    <div class="mt-2 bg-slate-200 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Subject Modules/Topics -->
                <div class="flex-1 overflow-y-auto">
                    <template v-for="topic in (subject?.topics || [])" :key="topic.id">
                        <div class="border-b border-slate-200/50">
                            <!-- Module Header -->
                            <button 
                                @click="toggleModule(topic.id)"
                                class="w-full p-4 text-left hover:bg-slate-100 transition-colors duration-200"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 border-2 border-slate-300 rounded-full flex items-center justify-center">
                                            <div v-if="getLessonProgress(topic) === 100" class="w-3 h-3 bg-green-500 rounded-full"></div>
                                        </div>
                                        <div>
                                            <h3 class="text-slate-800 font-medium">Topic {{ topic.order_index + 1 || 1 }}</h3>
                                            <p class="text-xs text-slate-500">0/{{ topic.lessons?.length || 0 }}</p>
                                        </div>
                                    </div>
                                    <svg 
                                        :class="[
                                            'w-5 h-5 text-slate-400 transform transition-transform duration-200',
                                            expandedModules.has(topic.id) ? 'rotate-90' : ''
                                        ]" 
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </button>

                            <!-- Module Lessons -->
                            <div 
                                v-if="expandedModules.has(topic.id)" 
                                class="bg-slate-50"
                            >
                                <template v-for="lesson in (topic.lessons || [])" :key="lesson.id">
                                    <Link 
                                        :href="route('lessons.show', lesson.id)"
                                        :class="[
                                            'block py-3 px-6 pl-12 border-l-4 transition-all duration-200 hover:bg-slate-100',
                                            selectedLesson?.id === lesson.id 
                                                ? 'border-indigo-500 bg-indigo-50' 
                                                : 'border-transparent'
                                        ]"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <!-- Lesson Icon -->
                                            <div class="flex-shrink-0">
                                                <div v-if="getLessonTypeIcon(lesson) === 'video'" class="w-5 h-5 text-indigo-500">
                                                    <svg fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </div>
                                                <div v-else-if="getLessonTypeIcon(lesson) === 'quiz'" class="w-5 h-5 text-green-500">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div v-else class="w-5 h-5 text-slate-400">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Lesson Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <div class="min-w-0 flex-1">
                                                        <p :class="[
                                                            'text-sm font-medium truncate',
                                                            selectedLesson?.id === lesson.id ? 'text-indigo-600' : 'text-slate-800'
                                                        ]">
                                                            {{ (topic.order_index + 1 || 1) }}.{{ (lesson.order_index + 1 || 1) }} {{ lesson.title }}
                                                        </p>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <span v-if="getLessonTypeIcon(lesson) === 'video'" class="text-xs text-slate-500">
                                                                MULTIMEDIA
                                                            </span>
                                                            <span v-else-if="getLessonTypeIcon(lesson) === 'quiz'" class="text-xs text-slate-500">
                                                                QUIZ - 18 QUESTIONS
                                                            </span>
                                                            <span v-else class="text-xs text-slate-500">
                                                                TEXT
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Completion Status -->
                                                    <div class="flex-shrink-0 ml-2">
                                                        <div v-if="getLessonProgress(lesson) === 100" class="w-5 h-5 text-green-500">
                                                            <svg fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </div>
                                                        <div v-else class="w-5 h-5 border-2 border-slate-300 rounded-full"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex">
                <!-- Video and Lesson Content -->
                <div class="flex-1 flex flex-col bg-gradient-to-br from-slate-50 to-blue-50">
                    <!-- Lesson Header -->
                    <div class="bg-white/80 backdrop-blur-xl border-b border-slate-200/50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-xl font-bold text-slate-800">
                                    {{ selectedLesson?.title || '1.1 Lesson Introduction' }}
                                </h1>
                                <p class="text-sm text-slate-600 mt-1">
                                    {{ selectedLesson?.topic?.name || 'Topic 1' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="text-right">
                                    <p class="text-sm text-slate-600">Lesson {{ selectedLesson?.order_index + 1 || 1 }}</p>
                                </div>
                                <button class="p-2 hover:bg-slate-100 rounded-xl transition-colors duration-200">
                                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Video Player -->
                    <div class="flex-1 bg-slate-800 relative">
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="text-center text-white px-4">
                                <div class="text-6xl text-white/20 mb-4">{{ subject.name.charAt(0) }}</div>
                                <h3 class="text-2xl font-medium mb-2 text-white/80">Topic 1</h3>
                                <h2 class="text-4xl font-bold mb-8">Lesson Introduction</h2>
                                
                                <!-- Video Controls Mockup -->
                                <div class="mt-8 flex items-center justify-center space-x-4">
                                    <button class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mt-6 flex items-center justify-between max-w-lg mx-auto">
                                    <span class="text-sm text-white/80">36:08</span>
                                    <div class="flex-1 mx-4 h-1 bg-white/20 rounded-full">
                                        <div class="h-full bg-white/70 rounded-full" style="width: 35%"></div>
                                    </div>
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-white/60 rounded-sm"></div>
                                        <div class="w-2 h-2 bg-white/60 rounded-sm"></div>
                                        <div class="w-2 h-2 bg-white/60 rounded-sm"></div>
                                        <div class="w-2 h-2 bg-white/60 rounded-sm"></div>
                                        <div class="w-2 h-2 bg-white/60 rounded-sm"></div>
                                        <div class="w-2 h-2 bg-white/60 rounded-sm"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Tutor Chat Sidebar -->
                <div :class="[
                    'bg-white border-l border-slate-200/50 transition-all duration-300 overflow-hidden flex flex-col',
                    showAIChat ? 'w-96' : 'w-0',
                    isMobile ? 'fixed inset-y-16 right-0 z-40 shadow-xl' : ''
                ]">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-slate-200/50 bg-gradient-to-r from-green-50 to-emerald-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800">AI {{ subject?.name }} Tutor</h3>
                                    <div class="flex items-center space-x-1">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <span class="text-sm text-green-600 font-medium">Online</span>
                                    </div>
                                </div>
                            </div>
                            <button @click="toggleAIChat" class="p-1 hover:bg-white/50 rounded-lg">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                        <div v-for="message in chatMessages" :key="message.id" 
                             :class="[
                                 'flex',
                                 message.sender === 'user' ? 'justify-end' : 'justify-start'
                             ]">
                            <div :class="[
                                'max-w-xs lg:max-w-md px-4 py-3 rounded-2xl',
                                message.sender === 'user' 
                                    ? 'bg-indigo-500 text-white rounded-br-md' 
                                    : 'bg-green-500 text-white rounded-bl-md'
                            ]">
                                <p class="text-sm">{{ message.message }}</p>
                                <p class="text-xs opacity-75 mt-1">
                                    {{ message.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="p-4 border-t border-slate-200/50 bg-slate-50">
                        <p class="text-xs text-slate-600 mb-2 italic">
                            Hi! I'm your AI {{ subject.name }} tutor. Ask me anything about the lesson or {{ subject.name.toLowerCase() }} topics!
                        </p>
                        <div class="flex space-x-2">
                            <input 
                                v-model="newMessage"
                                @keyup.enter="sendMessage"
                                type="text" 
                                :placeholder="`Ask me anything about ${subject.name.toLowerCase()}...`"
                                class="flex-1 px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                            />
                            <button 
                                @click="sendMessage"
                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Overlays -->
        <div 
            v-if="isMobile && showSidebar" 
            @click="toggleSidebar" 
            class="fixed inset-0 bg-black/50 z-30 lg:hidden"
        ></div>
        
        <div 
            v-if="isMobile && showAIChat" 
            @click="toggleAIChat" 
            class="fixed inset-0 bg-black/50 z-30 lg:hidden"
        ></div>
    </div>
</template>