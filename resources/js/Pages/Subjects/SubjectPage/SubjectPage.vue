<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import ChunkedVideoUpload from '@/components/ChunkedVideoUpload.vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    subject: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

// Check if user can manage content (admin or teacher)
const canManageContent = computed(() => {
    return user.role === 'admin' || user.role === 'teacher';
});

const showSidebar = ref(true);
const isMobile = ref(false);
const expandedModules = ref(new Set());
const selectedLesson = ref(null);

// Admin functionality - Topic and Lesson management
const selectedTopicId = ref(props.subject?.topics?.[0]?.id || null);
const showAddTopicForm = ref(false);
const showAddLessonForm = ref(false);

const newTopic = ref({
    name: '',
    description: '',
});

const newLesson = ref({
    title: '',
    description: '',
    notes: '',
    videoData: null,
});

const isUploading = ref(false);
const uploadProgress = ref(0);
const showChunkedUpload = ref(false);

const selectedTopic = computed(() => {
    return props.subject?.topics?.find(topic => topic.id === selectedTopicId.value);
});

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

// Admin Functions - Topic Management
const addTopic = async () => {
    try {
        const response = await axios.post(route('topics.store'), {
            subject_id: props.subject.id,
            name: newTopic.value.name,
            description: newTopic.value.description,
        });

        if (response.data.success) {
            // Add the new topic to the existing topics
            props.subject.topics.push(response.data.topic);

            // Reset form
            newTopic.value = { name: '', description: '' };
            showAddTopicForm.value = false;

            // Select the new topic and expand it
            selectedTopicId.value = response.data.topic.id;
            expandedModules.value.add(response.data.topic.id);
        }
    } catch (error) {
        console.error('Error adding topic:', error);
        alert('Error adding topic. Please try again.');
    }
};

const cancelAddTopic = () => {
    newTopic.value = { name: '', description: '' };
    showAddTopicForm.value = false;
};

// Admin Functions - Lesson Management
const addLesson = async () => {
    if (isUploading.value) return;

    try {
        isUploading.value = true;

        const formData = new FormData();
        formData.append('topic_id', selectedTopicId.value);
        formData.append('title', newLesson.value.title);
        formData.append('description', newLesson.value.description);
        formData.append('notes', newLesson.value.notes);

        // If we have chunked video data, use that
        if (newLesson.value.videoData) {
            formData.append('video_path', newLesson.value.videoData.filePath);
            formData.append('video_filename', newLesson.value.videoData.fileName || 'uploaded_video.mp4');
            if (newLesson.value.videoData.duration) {
                formData.append('duration_minutes', newLesson.value.videoData.duration);
            }
            if (newLesson.value.videoData.storageDisk) {
                formData.append('storage_disk', newLesson.value.videoData.storageDisk);
            }
        }

        const response = await axios.post(route('lessons.store'), formData);

        if (response.data.success) {
            // Add the new lesson to the selected topic
            const topic = props.subject.topics.find(t => t.id === selectedTopicId.value);
            if (topic) {
                if (!topic.lessons) topic.lessons = [];
                topic.lessons.push(response.data.lesson);
            }

            // Reset form
            newLesson.value = { title: '', description: '', notes: '', videoData: null };
            showAddLessonForm.value = false;
            showChunkedUpload.value = false;
        }
    } catch (error) {
        console.error('Error adding lesson:', error);
        alert('Error adding lesson. Please try again.');
    } finally {
        isUploading.value = false;
        uploadProgress.value = 0;
    }
};

const handleVideoUploadSuccess = (uploadResult) => {
    newLesson.value.videoData = uploadResult;
    showChunkedUpload.value = false;
};

const handleVideoUploadError = (error) => {
    console.error('Video upload error:', error);
    alert('Video upload failed. Please try again.');
};

const removeVideo = () => {
    newLesson.value.videoData = null;
    showChunkedUpload.value = false;
};

const cancelAddLesson = () => {
    newLesson.value = { title: '', description: '', notes: '', videoData: null };
    showAddLessonForm.value = false;
    showChunkedUpload.value = false;
};

const selectTopicForLesson = (topicId) => {
    selectedTopicId.value = topicId;
    showAddLessonForm.value = true;
};

// Publish/Unpublish lesson
const toggleLessonPublish = async (lesson, event) => {
    event.preventDefault();
    event.stopPropagation();

    try {
        const endpoint = lesson.is_published
            ? route('lessons.unpublish', lesson.id)
            : route('lessons.publish', lesson.id);

        const response = await axios.patch(endpoint);

        if (response.data.success) {
            // Update the lesson in the local state
            lesson.is_published = response.data.lesson.is_published;
        }
    } catch (error) {
        console.error('Error toggling publish status:', error);
        alert('Failed to update lesson status. Please try again.');
    }
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
                <!-- Admin Controls - Add Topic and Lesson -->
                <template v-if="canManageContent">
                    <button @click="showAddTopicForm = true"
                            class="hidden md:flex items-center space-x-2 px-3 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Add Topic</span>
                    </button>

                    <button @click="showAddLessonForm = true" :disabled="!selectedTopic && !(subject?.topics?.length > 0)"
                            class="hidden md:flex items-center space-x-2 px-3 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <span>Add Lesson</span>
                    </button>
                </template>

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
                                                        <div class="flex items-center space-x-2">
                                                            <p :class="[
                                                                'text-sm font-medium truncate',
                                                                selectedLesson?.id === lesson.id ? 'text-indigo-600' : 'text-slate-800',
                                                                !lesson.is_published ? 'opacity-70' : ''
                                                            ]">
                                                                {{ (topic.order_index + 1 || 1) }}.{{ (lesson.order_index + 1 || 1) }} {{ lesson.title }}
                                                            </p>
                                                            <!-- Draft Badge + Publish Button for unpublished lessons -->
                                                            <template v-if="canManageContent">
                                                                <span v-if="!lesson.is_published" class="px-1.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">
                                                                    Draft
                                                                </span>
                                                                <button
                                                                    @click="toggleLessonPublish(lesson, $event)"
                                                                    :class="[
                                                                        'px-1.5 py-0.5 text-xs font-medium rounded transition-colors',
                                                                        lesson.is_published
                                                                            ? 'bg-green-100 text-green-700 hover:bg-red-100 hover:text-red-700'
                                                                            : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'
                                                                    ]"
                                                                    :title="lesson.is_published ? 'Click to unpublish' : 'Click to publish'"
                                                                >
                                                                    {{ lesson.is_published ? 'Published' : 'Publish' }}
                                                                </button>
                                                            </template>
                                                        </div>
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
                    <div class="flex-1 bg-slate-800 relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center p-8">
                            <div class="text-center text-white w-full max-w-2xl">
                                <!-- Subject Initial -->
                                <div class="text-6xl md:text-8xl text-white/20 mb-6">{{ subject.name.charAt(0) }}</div>

                                <!-- Topic & Lesson Title -->
                                <h3 class="text-xl md:text-2xl font-medium mb-2 text-white/80">
                                    {{ selectedLesson?.topic?.name || 'Topic 1' }}
                                </h3>
                                <h2 class="text-2xl md:text-4xl font-bold mb-8">
                                    {{ selectedLesson?.title || 'Select a Lesson' }}
                                </h2>

                                <!-- Play Button -->
                                <div class="flex items-center justify-center mb-8">
                                    <button class="w-16 h-16 md:w-20 md:h-20 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-all duration-200 hover:scale-110">
                                        <svg class="w-8 h-8 md:w-10 md:h-10 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Video Progress Bar -->
                                <div class="flex items-center justify-center space-x-4 max-w-md mx-auto px-4">
                                    <span class="text-sm text-white/80 whitespace-nowrap">0:00</span>
                                    <div class="flex-1 h-2 bg-white/20 rounded-full cursor-pointer hover:h-3 transition-all duration-200">
                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-200" style="width: 0%"></div>
                                    </div>
                                    <span class="text-sm text-white/80 whitespace-nowrap">
                                        {{ selectedLesson?.formatted_duration || '--:--' }}
                                    </span>
                                </div>

                                <!-- Volume & Settings Controls -->
                                <div class="flex items-center justify-center space-x-4 mt-6">
                                    <button class="p-2 text-white/60 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-white/60 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-white/60 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- No video selected message -->
                                <p v-if="!selectedLesson" class="text-white/50 text-sm mt-6">
                                    Click on a lesson from the sidebar to start watching
                                </p>
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

        <!-- Add Topic Modal -->
        <div v-if="showAddTopicForm" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-200/50 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Add New Topic</h3>
                <form @submit.prevent="addTopic">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Topic Name*</label>
                            <input v-model="newTopic.name" type="text" required
                                   class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white transition-all duration-200"
                                   placeholder="e.g. Introduction, Basic Concepts">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                            <textarea v-model="newTopic.description" rows="3"
                                      class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white transition-all duration-200"
                                      placeholder="Brief description of this topic..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" @click="cancelAddTopic"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            Add Topic
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Lesson Modal -->
        <div v-if="showAddLessonForm" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-slate-200/50 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-slate-800 mb-6">Add New Lesson</h3>
                <form @submit.prevent="addLesson">
                    <div class="space-y-6">
                        <!-- Topic Selection -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Select Topic*</label>
                            <select v-model="selectedTopicId" required
                                    class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option v-for="topic in subject?.topics" :key="topic.id" :value="topic.id">
                                    {{ topic.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Lesson Title*</label>
                            <input v-model="newLesson.title" type="text" required
                                   class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                   placeholder="e.g. Introduction to Algebra">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                            <textarea v-model="newLesson.description" rows="3"
                                      class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                      placeholder="What will students learn in this lesson?"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Video File</label>

                            <!-- Video Upload Interface -->
                            <div v-if="!newLesson.videoData && !showChunkedUpload" class="space-y-3">
                                <button @click="showChunkedUpload = true" type="button"
                                        class="w-full bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-dashed border-blue-300 rounded-2xl p-6 hover:border-blue-400 hover:bg-gradient-to-r hover:from-blue-100 hover:to-indigo-100 transition-all duration-200">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-blue-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-2 text-sm font-medium text-slate-700">Upload Video File</p>
                                        <p class="text-xs text-slate-500">Fast chunked upload with compression</p>
                                        <p class="text-xs text-slate-400 mt-1">MP4, MOV, AVI, WMV, MKV up to 1GB</p>
                                    </div>
                                </button>
                            </div>

                            <!-- Chunked Upload Component -->
                            <ChunkedVideoUpload
                                v-if="showChunkedUpload"
                                @upload-success="handleVideoUploadSuccess"
                                @upload-error="handleVideoUploadError"
                                @cancel="showChunkedUpload = false"
                                class="mt-3"
                            />

                            <!-- Video Selected State -->
                            <div v-if="newLesson.videoData" class="mt-3 p-4 bg-green-50 border border-green-200 rounded-xl">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-green-800">Video uploaded successfully!</p>
                                            <p class="text-xs text-green-600">
                                                {{ newLesson.videoData.fileName }}
                                                <span v-if="newLesson.videoData.duration" class="ml-2">{{ newLesson.videoData.duration }} min</span>
                                            </p>
                                        </div>
                                    </div>
                                    <button @click="removeVideo" type="button" class="text-green-600 hover:text-green-800">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Lesson Notes</label>
                            <textarea v-model="newLesson.notes" rows="4"
                                      class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                      placeholder="Additional notes, resources, or homework for this lesson..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-8">
                        <button type="button" @click="cancelAddLesson"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isUploading"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="!isUploading">Add Lesson</span>
                            <span v-else class="flex items-center space-x-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                                    <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Saving...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>