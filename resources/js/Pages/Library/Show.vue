<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    resource: Object,
    related: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Content protection
const showProtectionWarning = ref(false);
const downloadAttempts = ref(0);

// Prevent right-click context menu
const preventRightClick = (e) => {
    e.preventDefault();
    showProtectionWarning.value = true;
    setTimeout(() => {
        showProtectionWarning.value = false;
    }, 3000);
};

// Prevent keyboard shortcuts
const preventKeyboard = (e) => {
    // Prevent Ctrl+S, Ctrl+A, Ctrl+P, F12, etc.
    const forbiddenKeys = [
        { key: 's', ctrl: true }, // Save
        { key: 'a', ctrl: true }, // Select all
        { key: 'p', ctrl: true }, // Print
        { key: 'c', ctrl: true }, // Copy
        { key: 'u', ctrl: true }, // View source
        { key: 'i', ctrl: true, shift: true }, // Developer tools
        { key: 'j', ctrl: true, shift: true }, // Console
        { key: 'k', ctrl: true, shift: true }, // Console
        { key: 'F12' }, // Developer tools
    ];

    const pressed = {
        key: e.key.toLowerCase(),
        ctrl: e.ctrlKey,
        shift: e.shiftKey,
        alt: e.altKey
    };

    const isForbidden = forbiddenKeys.some(forbidden => {
        return pressed.key === forbidden.key &&
               pressed.ctrl === (forbidden.ctrl || false) &&
               pressed.shift === (forbidden.shift || false) &&
               pressed.alt === (forbidden.alt || false);
    });

    if (isForbidden) {
        e.preventDefault();
        showProtectionWarning.value = true;
        setTimeout(() => {
            showProtectionWarning.value = false;
        }, 3000);
    }
};

// Fake download attempt handler
const handleDownloadAttempt = () => {
    downloadAttempts.value++;
    showProtectionWarning.value = true;
    setTimeout(() => {
        showProtectionWarning.value = false;
    }, 3000);
    
    // Log attempt to backend
    fetch(route('library.stream', { resource: props.resource.id, download: 1 }))
        .catch(() => {});
};

// Prevent text selection
const preventSelection = () => {
    if (window.getSelection) {
        window.getSelection().removeAllRanges();
    }
};

// Utility functions
const getTypeIcon = (type) => {
    const icons = {
        book: 'M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197',
        past_paper: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        video: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        audio: 'M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 17a1 1 0 01-1-1V8a1 1 0 011-1h1.5a1 1 0 01.707.293L13 9h3a1 1 0 011 1v6a1 1 0 01-1 1h-3l-1.793 1.793A1 1 0 019 17z'
    };
    return icons[type] || icons.document;
};

const getTypeColor = (type) => {
    const colors = {
        book: 'from-blue-500 to-indigo-600',
        past_paper: 'from-green-500 to-emerald-600',
        document: 'from-purple-500 to-violet-600',
        video: 'from-red-500 to-rose-600',
        audio: 'from-yellow-500 to-orange-600'
    };
    return colors[type] || colors.document;
};

const formatFileSize = (bytes) => {
    if (!bytes) return 'Unknown';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

// Setup protection on mount
onMounted(() => {
    if (props.resource.is_protected) {
        document.addEventListener('contextmenu', preventRightClick);
        document.addEventListener('keydown', preventKeyboard);
        document.addEventListener('selectstart', preventSelection);
        document.addEventListener('dragstart', preventSelection);
        
        // Prevent dragging
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        document.body.style.mozUserSelect = 'none';
        document.body.style.msUserSelect = 'none';
        
        // Add CSS to prevent text selection
        const style = document.createElement('style');
        style.textContent = `
            .protected-content * {
                -webkit-touch-callout: none;
                -webkit-user-select: none;
                -khtml-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                -webkit-tap-highlight-color: transparent;
            }
        `;
        document.head.appendChild(style);
    }
});

// Cleanup on unmount
onUnmounted(() => {
    if (props.resource.is_protected) {
        document.removeEventListener('contextmenu', preventRightClick);
        document.removeEventListener('keydown', preventKeyboard);
        document.removeEventListener('selectstart', preventSelection);
        document.removeEventListener('dragstart', preventSelection);
        
        document.body.style.userSelect = '';
        document.body.style.webkitUserSelect = '';
        document.body.style.mozUserSelect = '';
        document.body.style.msUserSelect = '';
    }
});
</script>

<template>
    <Head :title="resource.title" />
    
    <DashboardLayout :title="resource.title" :subtitle="resource.subject?.name || 'Digital Resource'">
        <div class="max-w-6xl mx-auto space-y-6" :class="resource.is_protected ? 'protected-content' : ''">
            <!-- Protection Warning -->
            <div 
                v-if="showProtectionWarning" 
                class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-xl animate-bounce"
            >
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-semibold">Content Protected!</span>
                </div>
                <p class="text-sm mt-1">Downloads and copying are not allowed</p>
            </div>

            <!-- Resource Header -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div :class="['h-48 bg-gradient-to-br', getTypeColor(resource.type), 'flex items-center justify-center relative']">
                    <svg class="w-24 h-24 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getTypeIcon(resource.type)"></path>
                    </svg>
                    <div class="absolute top-4 left-4">
                        <Link :href="route('library.index')" class="p-2 bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </Link>
                    </div>
                    <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                        <span class="text-sm font-medium text-white capitalize">{{ resource.type.replace('_', ' ') }}</span>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-slate-900 mb-4">{{ resource.title }}</h1>
                            
                            <div class="flex flex-wrap gap-4 mb-6">
                                <div v-if="resource.subject" class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                        {{ resource.subject.name }}
                                    </span>
                                </div>
                                
                                <div v-if="resource.grade_level" class="flex items-center text-slate-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                                    </svg>
                                    <span>Grade {{ resource.grade_level }}</span>
                                </div>
                                
                                <div v-if="resource.year" class="flex items-center text-slate-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h.5a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h.5z"></path>
                                    </svg>
                                    <span>{{ resource.year }}</span>
                                </div>
                                
                                <div v-if="resource.exam_board" class="flex items-center text-slate-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                    </svg>
                                    <span>{{ resource.exam_board }}</span>
                                </div>
                            </div>

                            <div v-if="resource.description" class="prose prose-slate max-w-none mb-6">
                                <p class="text-slate-600 leading-relaxed">{{ resource.description }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-6 lg:w-80">
                            <h3 class="font-semibold text-slate-800 mb-4">Resource Information</h3>
                            
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">File Size:</span>
                                    <span class="font-medium">{{ formatFileSize(resource.file_size) }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Views:</span>
                                    <span class="font-medium">{{ resource.view_count.toLocaleString() }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-slate-600">File Type:</span>
                                    <span class="font-medium uppercase">{{ resource.file_type }}</span>
                                </div>
                                
                                <div v-if="resource.is_protected" class="flex justify-between">
                                    <span class="text-slate-600">Protection:</span>
                                    <span class="flex items-center text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Enabled
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6 space-y-3">
                                <!-- View Button -->
                                <a 
                                    :href="route('library.stream', resource.id)" 
                                    target="_blank"
                                    class="w-full px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Resource
                                </a>

                                <!-- Fake Download Button (for protection demo) -->
                                <button 
                                    @click="handleDownloadAttempt"
                                    class="w-full px-6 py-3 bg-slate-300 text-slate-500 rounded-2xl font-semibold cursor-not-allowed flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-4-6h0a2 2 0 002 2v0a2 2 0 002-2v0a2 2 0 00-2-2h-4a2 2 0 00-2 2v0a2 2 0 002 2z"></path>
                                    </svg>
                                    Download (Protected)
                                </button>
                                
                                <p class="text-xs text-center text-slate-500">
                                    Downloads are disabled for content protection
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Resources -->
            <div v-if="related.length > 0" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Related Resources</h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link 
                            v-for="item in related" 
                            :key="item.id"
                            :href="route('library.show', item.id)"
                            class="flex items-center p-4 bg-slate-50 rounded-2xl hover:bg-slate-100 transition-colors duration-200 group"
                        >
                            <div :class="['w-12 h-12 rounded-xl bg-gradient-to-br', getTypeColor(item.type), 'flex items-center justify-center mr-4 flex-shrink-0']">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(item.type)"></path>
                                </svg>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-slate-800 group-hover:text-indigo-600 transition-colors duration-200 line-clamp-1">
                                    {{ item.title }}
                                </h3>
                                <p class="text-sm text-slate-500 capitalize">{{ item.type.replace('_', ' ') }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.protected-content {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Disable text selection for protected content */
.protected-content *::selection {
    background: transparent;
}

.protected-content *::-moz-selection {
    background: transparent;
}
</style>