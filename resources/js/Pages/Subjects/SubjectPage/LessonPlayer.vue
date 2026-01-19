<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import Hls from 'hls.js';

const props = defineProps({
    auth: Object,
    lesson: Object,
    subject: Object,
});

// HLS instance
let hls = null;

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };
const videoPlayer = ref(null);
const currentTime = ref(0);
const duration = ref(0);
const isPlaying = ref(false);
const volume = ref(1);
const showControls = ref(true);
const isFullscreen = ref(false);
const isLoading = ref(true);
const playbackRate = ref(1);
const showSidebar = ref(true);
const isMobile = ref(false);
const buffered = ref(0);
const expandedModules = ref(new Set());

// HLS and Quality Selector State
const currentQuality = ref('auto');
const showQualityMenu = ref(false);
const availableQualities = ref(props.lesson.available_qualities || []);
const isHlsSupported = ref(false);
const isTranscoding = ref(props.lesson.is_transcoding || false);
const transcodingProgress = ref(props.lesson.transcoding_progress || 0);
const transcodingStatus = ref(props.lesson.transcoding_status || 'none');
let transcodingPollInterval = null;

// AI Tutor Chat functionality
const showAIChat = ref(true);
const chatMessages = ref([
    {
        id: 1,
        sender: 'ai',
        message: "Hi! I'm your AI Agriculture tutor. Ask me anything about the lesson or agriculture topics!",
        timestamp: new Date()
    },
    {
        id: 2,
        sender: 'ai', 
        message: "Welcome to the Agriculture lesson! I'm here to help you understand any concepts. What would you like to know about farming techniques or agricultural practices?",
        timestamp: new Date()
    }
]);
const newMessage = ref('');
const chatContainer = ref(null);

const formattedCurrentTime = computed(() => formatTime(currentTime.value));
const formattedDuration = computed(() => formatTime(duration.value));
const progress = computed(() => duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0);

const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    
    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
};

const togglePlay = () => {
    if (videoPlayer.value) {
        if (isPlaying.value) {
            videoPlayer.value.pause();
        } else {
            videoPlayer.value.play();
        }
    }
};

const setCurrentTime = (event) => {
    const rect = event.target.getBoundingClientRect();
    const percent = (event.clientX - rect.left) / rect.width;
    const newTime = percent * duration.value;
    
    if (videoPlayer.value) {
        videoPlayer.value.currentTime = newTime;
    }
};

const changeVolume = (event) => {
    const newVolume = event.target.value / 100;
    volume.value = newVolume;
    if (videoPlayer.value) {
        videoPlayer.value.volume = newVolume;
    }
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        videoPlayer.value.requestFullscreen();
        isFullscreen.value = true;
    } else {
        document.exitFullscreen();
        isFullscreen.value = false;
    }
};

const skipTime = (seconds) => {
    if (videoPlayer.value) {
        videoPlayer.value.currentTime += seconds;
    }
};

const changePlaybackRate = (rate) => {
    playbackRate.value = rate;
    if (videoPlayer.value) {
        videoPlayer.value.playbackRate = rate;
    }
};

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

const updateBuffered = () => {
    if (videoPlayer.value && videoPlayer.value.buffered.length > 0) {
        const bufferedEnd = videoPlayer.value.buffered.end(videoPlayer.value.buffered.length - 1);
        buffered.value = duration.value > 0 ? (bufferedEnd / duration.value) * 100 : 0;
    }
};

const toggleModule = (topicId) => {
    if (expandedModules.value.has(topicId)) {
        expandedModules.value.delete(topicId);
    } else {
        expandedModules.value.add(topicId);
    }
};

const getLessonTypeIcon = (lessonItem) => {
    if (lessonItem.video_path) return 'video';
    if (lessonItem.type === 'quiz') return 'quiz';
    if (lessonItem.type === 'text') return 'text';
    return 'document';
};

const getLessonProgress = (lessonItem) => {
    // This would come from actual progress tracking
    return lessonItem.id === props.lesson.id ? 100 : Math.random() > 0.5 ? 100 : 0;
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
    const responses = [
        "That's a great question about agriculture! Let me help you with that concept.",
        "In agricultural practices, this relates to soil management and crop rotation techniques.",
        "This topic is fundamental to understanding sustainable farming methods.",
        "Let me explain how this applies to modern agricultural techniques and farming practices.",
        "This concept is important for crop yield optimization and soil health maintenance."
    ];
    return responses[Math.floor(Math.random() * responses.length)];
};

// HLS Functions
const setupHls = () => {
    if (!videoPlayer.value) return;

    const hlsUrl = props.lesson.hls_url;
    const videoUrl = props.lesson.video_url;

    // Check if HLS is supported
    isHlsSupported.value = Hls.isSupported();

    // If HLS URL is available and HLS.js is supported
    if (hlsUrl && isHlsSupported.value) {
        // Destroy existing HLS instance
        if (hls) {
            hls.destroy();
        }

        hls = new Hls({
            enableWorker: true,
            lowLatencyMode: false,
            backBufferLength: 90,
        });

        hls.loadSource(hlsUrl);
        hls.attachMedia(videoPlayer.value);

        hls.on(Hls.Events.MANIFEST_PARSED, (event, data) => {
            // Get available quality levels from HLS
            const levels = hls.levels;
            if (levels && levels.length > 0) {
                availableQualities.value = levels.map((level, index) => ({
                    quality: `${level.height}p`,
                    width: level.width,
                    height: level.height,
                    bitrate: Math.round(level.bitrate / 1000),
                    index: index,
                }));
            }
            isLoading.value = false;
        });

        hls.on(Hls.Events.LEVEL_SWITCHED, (event, data) => {
            const level = hls.levels[data.level];
            if (level && currentQuality.value === 'auto') {
                // Update UI to show current auto-selected quality
            }
        });

        hls.on(Hls.Events.ERROR, (event, data) => {
            if (data.fatal) {
                switch (data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        console.error('HLS network error, trying to recover');
                        hls.startLoad();
                        break;
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        console.error('HLS media error, trying to recover');
                        hls.recoverMediaError();
                        break;
                    default:
                        console.error('HLS fatal error, falling back to direct playback');
                        destroyHls();
                        // Fall back to direct video URL
                        if (videoUrl) {
                            videoPlayer.value.src = videoUrl;
                        }
                        break;
                }
            }
        });
    }
    // Native HLS support (Safari, iOS)
    else if (hlsUrl && videoPlayer.value.canPlayType('application/vnd.apple.mpegurl')) {
        videoPlayer.value.src = hlsUrl;
    }
    // Fallback to direct video URL
    else if (videoUrl) {
        videoPlayer.value.src = videoUrl;
    }
};

const destroyHls = () => {
    if (hls) {
        hls.destroy();
        hls = null;
    }
};

const setQuality = (quality) => {
    currentQuality.value = quality;
    showQualityMenu.value = false;

    if (!hls) return;

    if (quality === 'auto') {
        hls.currentLevel = -1; // Auto quality selection
    } else {
        // Find the level index for the selected quality
        const qualityItem = availableQualities.value.find(q => q.quality === quality);
        if (qualityItem && qualityItem.index !== undefined) {
            hls.currentLevel = qualityItem.index;
        }
    }
};

const getQualityLabel = (quality) => {
    if (quality === 'auto') {
        if (hls && hls.currentLevel >= 0 && hls.levels[hls.currentLevel]) {
            return `Auto (${hls.levels[hls.currentLevel].height}p)`;
        }
        return 'Auto';
    }
    return quality;
};

const toggleQualityMenu = () => {
    showQualityMenu.value = !showQualityMenu.value;
};

// Poll transcoding status
const pollTranscodingStatus = async () => {
    if (!isTranscoding.value) return;

    try {
        const response = await fetch(`/api/lessons/${props.lesson.id}/transcoding-status`);
        if (response.ok) {
            const data = await response.json();
            transcodingStatus.value = data.transcoding.status;
            transcodingProgress.value = data.transcoding.progress;
            availableQualities.value = data.available_qualities || [];

            // Stop polling if transcoding is complete or failed
            if (data.transcoding.status === 'completed' || data.transcoding.status === 'failed') {
                isTranscoding.value = false;
                clearInterval(transcodingPollInterval);
                transcodingPollInterval = null;

                // Reinitialize HLS if transcoding completed
                if (data.transcoding.status === 'completed' && data.hls_url) {
                    props.lesson.hls_url = data.hls_url;
                    setupHls();
                }
            }
        }
    } catch (error) {
        console.error('Error polling transcoding status:', error);
    }
};

const startTranscodingPolling = () => {
    if (isTranscoding.value && !transcodingPollInterval) {
        transcodingPollInterval = setInterval(pollTranscodingStatus, 5000);
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Expand the current lesson's module
    if (props.lesson.topic) {
        expandedModules.value.add(props.lesson.topic.id);
    }
    
    // Scroll chat to bottom on mount
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
    
    if (videoPlayer.value) {
        videoPlayer.value.preload = 'metadata';
        videoPlayer.value.crossOrigin = 'anonymous';
        
        videoPlayer.value.addEventListener('loadedmetadata', () => {
            duration.value = videoPlayer.value.duration;
            isLoading.value = false;
        });
        
        videoPlayer.value.addEventListener('loadstart', () => {
            isLoading.value = true;
        });
        
        videoPlayer.value.addEventListener('canplay', () => {
            isLoading.value = false;
        });
        
        videoPlayer.value.addEventListener('waiting', () => {
            isLoading.value = true;
        });
        
        videoPlayer.value.addEventListener('playing', () => {
            isLoading.value = false;
        });
        
        videoPlayer.value.addEventListener('progress', updateBuffered);
        
        videoPlayer.value.addEventListener('timeupdate', () => {
            currentTime.value = videoPlayer.value.currentTime;
        });
        
        videoPlayer.value.addEventListener('play', () => {
            isPlaying.value = true;
        });
        
        videoPlayer.value.addEventListener('pause', () => {
            isPlaying.value = false;
        });
        
        videoPlayer.value.addEventListener('ended', () => {
            isPlaying.value = false;
        });

        // Auto-hide controls
        let controlsTimeout;
        const resetControlsTimeout = () => {
            clearTimeout(controlsTimeout);
            showControls.value = true;
            controlsTimeout = setTimeout(() => {
                if (isPlaying.value) {
                    showControls.value = false;
                }
            }, 3000);
        };

        videoPlayer.value.addEventListener('mousemove', resetControlsTimeout);
        videoPlayer.value.addEventListener('click', togglePlay);

        // Initialize HLS.js for adaptive streaming
        setupHls();
    }

    // Start transcoding polling if video is being transcoded
    if (isTranscoding.value) {
        startTranscodingPolling();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'textarea') {
            return;
        }
        
        switch(e.code) {
            case 'Space':
                e.preventDefault();
                togglePlay();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                skipTime(-10);
                break;
            case 'ArrowRight':
                e.preventDefault();
                skipTime(10);
                break;
            case 'KeyF':
                e.preventDefault();
                toggleFullscreen();
                break;
        }
    });
});

// Cleanup on unmount
onUnmounted(() => {
    // Destroy HLS instance
    destroyHls();

    // Clear transcoding polling interval
    if (transcodingPollInterval) {
        clearInterval(transcodingPollInterval);
        transcodingPollInterval = null;
    }

    // Remove event listeners
    window.removeEventListener('resize', checkMobile);
});
</script>

<template>
    <Head :title="lesson.title" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 font-sans text-slate-800">
        <!-- Header -->
        <header class="h-16 bg-white/90 backdrop-blur-xl border-b border-slate-200/50 px-4 md:px-6 flex items-center justify-between relative z-50">
            <div class="flex items-center space-x-4 flex-1 min-w-0">
                <Link :href="route('subjects.show', lesson.topic.subject.id)" class="p-2 hover:bg-slate-100 rounded-xl transition-colors duration-200 flex-shrink-0">
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
                    <h1 class="text-sm md:text-lg font-bold text-slate-800 truncate">{{ subject?.name || lesson.topic.subject.name }}</h1>
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
                </div>

                <!-- Subject Progress Overview -->
                <div class="p-4 border-b border-slate-200/50">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Progress</span>
                        <span class="text-slate-800 font-medium">3/25 lessons</span>
                    </div>
                    <div class="mt-2 bg-slate-200 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: 12%"></div>
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
                                            <h3 class="text-slate-800 font-medium">{{ topic.name }}</h3>
                                            <p class="text-xs text-slate-500">0/{{ topic.lessons?.length || 0 }} lessons</p>
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
                                <template v-for="topicLesson in (topic.lessons || [])" :key="topicLesson.id">
                                    <Link 
                                        :href="route('lessons.show', topicLesson.id)"
                                        :class="[
                                            'block py-3 px-6 pl-12 border-l-4 transition-all duration-200 hover:bg-slate-100',
                                            topicLesson.id === lesson.id 
                                                ? 'border-indigo-500 bg-slate-100' 
                                                : 'border-transparent'
                                        ]"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <!-- Lesson Icon -->
                                            <div class="flex-shrink-0">
                                                <div v-if="getLessonTypeIcon(topicLesson) === 'video'" class="w-5 h-5 text-indigo-500">
                                                    <svg fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </div>
                                                <div v-else-if="getLessonTypeIcon(topicLesson) === 'quiz'" class="w-5 h-5 text-green-500">
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
                                                            topicLesson.id === lesson.id ? 'text-indigo-600' : 'text-slate-800'
                                                        ]">
                                                            {{ topicLesson.title }}
                                                        </p>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <span v-if="getLessonTypeIcon(topicLesson) === 'video'" class="text-xs text-slate-500">
                                                                MULTIMEDIA
                                                            </span>
                                                            <span v-else-if="getLessonTypeIcon(topicLesson) === 'quiz'" class="text-xs text-slate-500">
                                                                QUIZ - 10 QUESTIONS
                                                            </span>
                                                            <span v-else class="text-xs text-slate-500">
                                                                TEXT
                                                            </span>
                                                            <span class="text-xs text-slate-400">•</span>
                                                            <span v-if="topicLesson.duration_minutes" class="text-xs text-slate-500">
                                                                {{ topicLesson.formatted_duration }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Completion Status -->
                                                    <div class="flex-shrink-0 ml-2">
                                                        <div v-if="getLessonProgress(topicLesson) === 100" class="w-5 h-5 text-green-500">
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
                            <h1 class="text-xl font-bold text-slate-800">{{ lesson.title }}</h1>
                            <p class="text-sm text-slate-600 mt-1">{{ lesson.topic.name }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm text-slate-600">Lesson {{ lesson.order_index + 1 || 1 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video Player -->
                <div class="flex-1 bg-black relative">
                    <!-- Transcoding Progress Indicator -->
                    <div
                        v-if="isTranscoding"
                        class="absolute top-0 left-0 right-0 bg-gradient-to-b from-black/80 to-transparent p-4 z-20"
                    >
                        <div class="flex items-center space-x-3 text-white">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span>Processing video for adaptive streaming...</span>
                                    <span>{{ transcodingProgress }}%</span>
                                </div>
                                <div class="mt-2 bg-white/20 rounded-full h-2">
                                    <div
                                        class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                                        :style="{ width: transcodingProgress + '%' }"
                                    ></div>
                                </div>
                                <p class="text-xs text-white/60 mt-1">
                                    Multiple quality options will be available once processing completes
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-full flex items-center justify-center">
                        <video
                            v-if="lesson.video_url || lesson.hls_url"
                            ref="videoPlayer"
                            class="w-full h-full object-contain video-player-hd"
                            :poster="lesson.thumbnail_url"
                            playsinline
                            preload="auto"
                            controlslist="nodownload"
                            disablepictureinpicture="false"
                            webkit-playsinline
                            x-webkit-airplay="allow"
                        >
                            Your browser does not support the video tag.
                        </video>

                        <!-- Loading Spinner -->
                        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/50">
                            <div class="flex items-center space-x-3 text-white">
                                <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm">Loading...</span>
                            </div>
                        </div>

                        <!-- Video Controls -->
                        <div 
                            :class="[
                                'absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent p-4 md:p-6 transition-all duration-300',
                                showControls ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'
                            ]"
                            @mouseover="showControls = true"
                            @touchstart="showControls = true"
                        >
                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="relative">
                                    <div class="w-full h-1 bg-white/20 rounded-full">
                                        <div class="h-full bg-white/30 rounded-full transition-all duration-200" :style="{ width: buffered + '%' }"></div>
                                    </div>
                                    <div class="absolute top-0 w-full h-1 cursor-pointer" @click="setCurrentTime">
                                        <div class="h-full bg-blue-500 rounded-full transition-all duration-200 relative" :style="{ width: progress + '%' }">
                                            <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-blue-500 rounded-full shadow-md"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Control Buttons -->
                            <div class="flex items-center justify-between text-white">
                                <div class="flex items-center space-x-3 md:space-x-4">
                                    <!-- Play/Pause -->
                                    <button @click="togglePlay" class="p-2 hover:bg-white/20 rounded-full transition-colors duration-200">
                                        <svg v-if="!isPlaying" class="w-6 h-6 md:w-8 md:h-8" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        <svg v-else class="w-6 h-6 md:w-8 md:h-8" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 4h4v16H6zM14 4h4v16h-4z"/>
                                        </svg>
                                    </button>

                                    <!-- Skip Buttons -->
                                    <button @click="skipTime(-10)" class="hidden sm:block p-1 hover:bg-white/20 rounded-full transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path>
                                        </svg>
                                    </button>
                                    <button @click="skipTime(10)" class="hidden sm:block p-1 hover:bg-white/20 rounded-full transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z"></path>
                                        </svg>
                                    </button>

                                    <!-- Volume Control -->
                                    <div class="hidden md:flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m-2.829-2.829a2.5 2.5 0 010-3.536M6 10H4a1 1 0 00-1 1v2a1 1 0 001 1h2l4 4V6l-4 4z"></path>
                                        </svg>
                                        <input type="range" min="0" max="100" :value="volume * 100" @input="changeVolume" class="w-16 accent-blue-500">
                                    </div>

                                    <!-- Time Display -->
                                    <div class="text-xs md:text-sm font-mono">
                                        {{ formattedCurrentTime }} / {{ formattedDuration }}
                                    </div>
                                </div>

                                <!-- Right Controls -->
                                <div class="flex items-center space-x-2">
                                    <!-- Quality Selector -->
                                    <div v-if="availableQualities.length > 0" class="relative">
                                        <button
                                            @click="toggleQualityMenu"
                                            class="px-2 py-1 text-xs bg-white/20 rounded hover:bg-white/30 transition-colors duration-200 flex items-center space-x-1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>{{ getQualityLabel(currentQuality) }}</span>
                                        </button>
                                        <div
                                            v-if="showQualityMenu"
                                            class="absolute bottom-full right-0 mb-2 bg-black/90 rounded-lg p-2 min-w-[120px] z-50"
                                        >
                                            <div class="text-xs text-white/60 px-2 py-1 border-b border-white/20 mb-1">Quality</div>
                                            <div class="flex flex-col space-y-1">
                                                <button
                                                    @click="setQuality('auto')"
                                                    :class="[
                                                        'px-2 py-1 text-xs rounded text-left',
                                                        currentQuality === 'auto' ? 'bg-blue-500' : 'hover:bg-white/20'
                                                    ]"
                                                >
                                                    Auto
                                                </button>
                                                <button
                                                    v-for="quality in availableQualities"
                                                    :key="quality.quality"
                                                    @click="setQuality(quality.quality)"
                                                    :class="[
                                                        'px-2 py-1 text-xs rounded text-left flex justify-between items-center',
                                                        currentQuality === quality.quality ? 'bg-blue-500' : 'hover:bg-white/20'
                                                    ]"
                                                >
                                                    <span>{{ quality.quality }}</span>
                                                    <span class="text-white/50 ml-2">{{ quality.bitrate }}k</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Playback Speed -->
                                    <div class="relative group">
                                        <button class="px-2 py-1 text-xs bg-white/20 rounded hover:bg-white/30 transition-colors duration-200">
                                            {{ playbackRate }}x
                                        </button>
                                        <div class="absolute bottom-full right-0 mb-2 bg-black/90 rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
                                            <div class="flex flex-col space-y-1">
                                                <button @click="changePlaybackRate(0.5)" class="px-2 py-1 text-xs hover:bg-white/20 rounded">0.5x</button>
                                                <button @click="changePlaybackRate(0.75)" class="px-2 py-1 text-xs hover:bg-white/20 rounded">0.75x</button>
                                                <button @click="changePlaybackRate(1)" class="px-2 py-1 text-xs hover:bg-white/20 rounded">1x</button>
                                                <button @click="changePlaybackRate(1.25)" class="px-2 py-1 text-xs hover:bg-white/20 rounded">1.25x</button>
                                                <button @click="changePlaybackRate(1.5)" class="px-2 py-1 text-xs hover:bg-white/20 rounded">1.5x</button>
                                                <button @click="changePlaybackRate(2)" class="px-2 py-1 text-xs hover:bg-white/20 rounded">2x</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fullscreen -->
                                    <button @click="toggleFullscreen" class="p-2 hover:bg-white/20 rounded-full transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- No Video State -->
                        <div v-if="!lesson.video_url && !lesson.hls_url" class="absolute inset-0 flex items-center justify-center bg-black">
                            <div class="text-center text-white px-4">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold mb-2">Video not available</h3>
                                <p class="text-sm opacity-75">This lesson doesn't have a video file uploaded yet.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Bar -->
                <div class="bg-white/80 backdrop-blur-xl border-t border-slate-200/50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Lesson Notes -->
                            <div v-if="lesson.notes" class="text-sm text-slate-600">
                                <button class="flex items-center space-x-2 hover:text-slate-800 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Lesson Notes</span>
                                </button>
                            </div>
                        </div>

                        <!-- Complete & Continue Button -->
                        <div>
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl font-medium transition-colors duration-200 flex items-center space-x-2">
                                <span>COMPLETE & CONTINUE</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </button>
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
                                    <h3 class="text-lg font-bold text-slate-800">AI {{ subject?.name || 'Agriculture' }} Tutor</h3>
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
                            Hi! I'm your AI Agriculture tutor. Ask me anything about the lesson or agriculture topics!
                        </p>
                        <div class="flex space-x-2">
                            <input 
                                v-model="newMessage"
                                @keyup.enter="sendMessage"
                                type="text" 
                                placeholder="Ask me anything about agriculture..."
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

<style scoped>
/* Ensure video renders at maximum quality */
.video-player-hd {
    image-rendering: high-quality;
    image-rendering: -webkit-optimize-contrast;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    transform: translateZ(0);
}

/* Prevent quality degradation on fullscreen */
video:fullscreen {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Ensure crisp rendering */
video {
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
}
</style>