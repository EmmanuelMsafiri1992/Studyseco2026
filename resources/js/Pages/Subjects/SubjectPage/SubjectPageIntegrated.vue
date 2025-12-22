<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';

const props = defineProps({
    auth: Object,
    subject: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

// Video player refs and state
const videoPlayer = ref(null);
const currentTime = ref(0);
const duration = ref(0);
const isPlaying = ref(false);
const volume = ref(1);
const showControls = ref(true);
const isFullscreen = ref(false);
const isLoading = ref(true);
const playbackRate = ref(1);
const buffered = ref(0);

// Lesson and navigation state
const selectedLessonId = ref(null);
const expandedModules = ref(new Set());
const showSidebar = ref(true);
const isMobile = ref(false);

// Computed properties
const selectedLesson = computed(() => {
    if (!selectedLessonId.value) return null;
    
    for (const topic of props.subject.topics || []) {
        const lesson = topic.lessons?.find(l => l.id === selectedLessonId.value);
        if (lesson) return lesson;
    }
    return null;
});

const formattedCurrentTime = computed(() => formatTime(currentTime.value));
const formattedDuration = computed(() => formatTime(duration.value));
const progress = computed(() => duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0);

// Utility functions
const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    
    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
};

const formatDuration = (minutes) => {
    if (!minutes) return 'No duration set';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0) {
        return `${hours}h ${mins}m`;
    }
    return `${mins}m`;
};

// Video player functions
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

// Navigation functions
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

const selectLesson = (lesson) => {
    selectedLessonId.value = lesson.id;
    
    // Reset video player state
    currentTime.value = 0;
    duration.value = 0;
    isPlaying.value = false;
    isLoading.value = true;
    
    // Wait for video element to update
    nextTick(() => {
        if (videoPlayer.value && lesson.video_url) {
            setupVideoEvents();
        }
    });
};

const getLessonTypeIcon = (lesson) => {
    if (lesson.video_path) return 'video';
    if (lesson.type === 'quiz') return 'quiz';
    if (lesson.type === 'text') return 'text';
    return 'document';
};

const getLessonProgress = (lesson) => {
    // This would come from actual progress tracking
    return lesson.id === selectedLessonId.value ? 100 : Math.random() > 0.5 ? 100 : 0;
};

const setupVideoEvents = () => {
    if (!videoPlayer.value) return;
    
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
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Expand all modules by default and select first lesson
    if (props.subject.topics) {
        props.subject.topics.forEach(topic => {
            expandedModules.value.add(topic.id);
        });
        
        // Select first lesson with video
        for (const topic of props.subject.topics) {
            if (topic.lessons) {
                const firstVideoLesson = topic.lessons.find(lesson => lesson.video_path);
                if (firstVideoLesson) {
                    selectLesson(firstVideoLesson);
                    break;
                }
            }
        }
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
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm md:text-lg font-bold text-slate-800 truncate">{{ subject.name }}</h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 flex-shrink-0">
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
                        <span class="text-slate-800 font-medium">
                            {{ subject.topics?.reduce((acc, topic) => acc + (topic.lessons?.length || 0), 0) || 0 }} lessons
                        </span>
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
                                            <p class="text-xs text-slate-500">{{ topic.lessons?.length || 0 }} lessons</p>
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
                                    <button 
                                        @click="selectLesson(lesson)"
                                        :class="[
                                            'w-full text-left py-3 px-6 pl-12 border-l-4 transition-all duration-200 hover:bg-slate-100',
                                            lesson.id === selectedLessonId 
                                                ? 'border-indigo-500 bg-slate-100' 
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
                                                            lesson.id === selectedLessonId ? 'text-indigo-600' : 'text-slate-800'
                                                        ]">
                                                            {{ lesson.title }}
                                                        </p>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <span v-if="getLessonTypeIcon(lesson) === 'video'" class="text-xs text-slate-500">
                                                                MULTIMEDIA
                                                            </span>
                                                            <span v-else-if="getLessonTypeIcon(lesson) === 'quiz'" class="text-xs text-slate-500">
                                                                QUIZ - 10 QUESTIONS
                                                            </span>
                                                            <span v-else class="text-xs text-slate-500">
                                                                TEXT
                                                            </span>
                                                            <span class="text-xs text-slate-400">•</span>
                                                            <span v-if="lesson.duration_minutes" class="text-xs text-slate-500">
                                                                {{ formatDuration(lesson.duration_minutes) }}
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
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col bg-gradient-to-br from-slate-50 to-blue-50">
                <!-- Lesson Header -->
                <div v-if="selectedLesson" class="bg-white/80 backdrop-blur-xl border-b border-slate-200/50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-slate-800">{{ selectedLesson.title }}</h1>
                            <p class="text-sm text-slate-600 mt-1">{{ selectedLesson.topic?.name || 'Lesson Content' }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm text-slate-600">Lesson {{ selectedLesson.order_index + 1 || 1 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video Player -->
                <div class="flex-1 bg-black relative" v-if="selectedLesson">
                    <div class="w-full h-full flex items-center justify-center">
                        <video 
                            v-if="selectedLesson.video_url"
                            ref="videoPlayer"
                            :src="selectedLesson.video_url"
                            class="w-full h-full object-contain"
                            :poster="selectedLesson.thumbnail_url"
                            playsinline
                            preload="metadata"
                            controlslist="nodownload"
                            disablepictureinpicture="false"
                            webkit-playsinline
                            :key="selectedLesson.id"
                        >
                            <source :src="selectedLesson.video_url" type="video/mp4">
                            <source :src="selectedLesson.video_url" type="video/webm">
                            <source :src="selectedLesson.video_url" type="video/x-matroska">
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
                                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-200 relative" :style="{ width: progress + '%' }">
                                            <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 w-3 h-3 bg-indigo-500 rounded-full shadow-md"></div>
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
                                        <input type="range" min="0" max="100" :value="volume * 100" @input="changeVolume" class="w-16 accent-indigo-500">
                                    </div>

                                    <!-- Time Display -->
                                    <div class="text-xs md:text-sm font-mono">
                                        {{ formattedCurrentTime }} / {{ formattedDuration }}
                                    </div>
                                </div>

                                <!-- Right Controls -->
                                <div class="flex items-center space-x-2">
                                    <!-- Playback Speed -->
                                    <div class="relative group">
                                        <button class="px-2 py-1 text-xs bg-white/20 rounded hover:bg-white/30 transition-colors duration-200">
                                            {{ playbackRate }}x
                                        </button>
                                        <div class="absolute bottom-full right-0 mb-2 bg-black/90 rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
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
                        <div v-if="!selectedLesson.video_url" class="absolute inset-0 flex items-center justify-center bg-black">
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

                <!-- Welcome State -->
                <div v-else class="flex-1 flex items-center justify-center">
                    <div class="text-center max-w-md px-4">
                        <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 8h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome to {{ subject.name }}</h2>
                        <p class="text-slate-600 mb-6">Select a lesson from the left sidebar to start learning.</p>
                        <div class="text-sm text-slate-500">
                            <p>{{ subject.topics?.length || 0 }} modules • {{ subject.topics?.reduce((acc, topic) => acc + (topic.lessons?.length || 0), 0) || 0 }} lessons</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Bar -->
                <div v-if="selectedLesson" class="bg-white/80 backdrop-blur-xl border-t border-slate-200/50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Lesson Notes -->
                            <div v-if="selectedLesson.notes" class="text-sm text-slate-600">
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
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div 
            v-if="isMobile && showSidebar" 
            @click="toggleSidebar" 
            class="fixed inset-0 bg-black/50 z-30 lg:hidden"
        ></div>
    </div>
</template>