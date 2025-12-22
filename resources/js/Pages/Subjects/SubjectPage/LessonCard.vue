<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    lesson: Object,
    canManage: Boolean,
});

const showMenu = ref(false);

const togglePublish = async () => {
    try {
        const endpoint = props.lesson.is_published ? 'lessons.unpublish' : 'lessons.publish';
        const response = await axios.patch(route(endpoint, props.lesson.id));
        
        if (response.data.success) {
            // Update lesson status in place
            props.lesson.is_published = response.data.lesson.is_published;
        }
    } catch (error) {
        console.error('Error toggling publish status:', error);
        alert('Error updating lesson status. Please try again.');
    }
};

const deleteLesson = async () => {
    if (!confirm('Are you sure you want to delete this lesson? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await axios.delete(route('lessons.destroy', props.lesson.id));
        
        if (response.data.success) {
            // Remove lesson from the DOM or emit an event
            // In a more robust implementation, you'd emit an event to the parent
            location.reload(); // Simple solution for now
        }
    } catch (error) {
        console.error('Error deleting lesson:', error);
        alert('Error deleting lesson. Please try again.');
    }
};
</script>

<template>
    <div class="bg-white rounded-3xl shadow-lg border border-slate-200/50 overflow-hidden transition-all duration-300 hover:shadow-xl hover:scale-105 group">
        <!-- Video Thumbnail or Placeholder -->
        <div class="relative h-48 bg-gradient-to-br from-slate-100 to-slate-200">
            <div v-if="lesson.video_path" class="relative w-full h-full">
                <!-- Video thumbnail - you might want to generate thumbnails from videos -->
                <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                    <svg class="w-16 h-16 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.646.647a1 1 0 00.707.293h.354a1 1 0 01.707.293l.646.647a1 1 0 00.707.293H16.586a1 1 0 00.707-.293L18 11.586A1 1 0 0018 10H17"></path>
                    </svg>
                </div>
                <!-- Play Button Overlay -->
                <div class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="w-16 h-16 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-indigo-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div v-else class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-xs text-slate-500">No video uploaded</p>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="absolute top-4 left-4">
                <span v-if="lesson.is_published" class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                    Published
                </span>
                <span v-else class="px-2 py-1 bg-amber-500 text-white text-xs font-semibold rounded-full">
                    Draft
                </span>
            </div>
            
            <!-- Duration Badge -->
            <div v-if="lesson.duration_minutes" class="absolute top-4 right-4 bg-black/60 backdrop-blur-sm text-white text-xs font-semibold px-2 py-1 rounded-full">
                {{ lesson.formatted_duration || lesson.duration_minutes + 'm' }}
            </div>
            
            <!-- Management Menu -->
            <div v-if="canManage" class="absolute bottom-4 right-4">
                <div class="relative">
                    <button @click="showMenu = !showMenu" class="p-2 bg-white/90 backdrop-blur-sm rounded-full shadow-lg hover:bg-white transition-all duration-200">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div v-if="showMenu" @click.stop class="absolute bottom-full right-0 mb-2 w-48 bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200/50 py-2 z-10">
                        <button @click="togglePublish(); showMenu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-100/70 transition-colors duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" :class="lesson.is_published ? 'text-amber-600' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="lesson.is_published ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'"></path>
                                </svg>
                                <span>{{ lesson.is_published ? 'Unpublish' : 'Publish' }}</span>
                            </div>
                        </button>
                        <button class="w-full text-left px-4 py-2 text-sm hover:bg-slate-100/70 transition-colors duration-150 text-blue-600">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                <span>Edit Lesson</span>
                            </div>
                        </button>
                        <button @click="deleteLesson(); showMenu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 transition-colors duration-150 text-red-600">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Delete</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lesson Details -->
        <div class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-800 mb-2 line-clamp-2">{{ lesson.title }}</h3>
                <p v-if="lesson.description" class="text-sm text-slate-600 line-clamp-3">{{ lesson.description }}</p>
            </div>

            <!-- Lesson Meta -->
            <div class="flex items-center justify-between text-xs text-slate-500 mb-4">
                <span v-if="lesson.video_filename">{{ lesson.video_filename }}</span>
                <span v-else>No video file</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-200/50">
                <Link v-if="lesson.video_path && lesson.is_published" 
                      :href="route('lessons.show', lesson.id)" 
                      class="flex items-center space-x-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.646.647a1 1 0 00.707.293h.354a1 1 0 01.707.293l.646.647a1 1 0 00.707.293H16.586a1 1 0 00.707-.293L18 11.586A1 1 0 0018 10H17"></path>
                    </svg>
                    <span>Watch Now</span>
                </Link>
                <span v-else-if="!lesson.video_path" class="text-sm text-slate-400">No video available</span>
                <span v-else class="text-sm text-slate-400">Unpublished</span>

                <div class="flex items-center space-x-2">
                    <div v-if="lesson.notes" class="group relative">
                        <svg class="w-4 h-4 text-slate-400 hover:text-slate-600 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full right-0 mb-2 w-64 bg-slate-800 text-white text-xs rounded-lg py-2 px-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-20">
                            {{ lesson.notes }}
                            <div class="absolute top-full right-6 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-slate-800"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Click away listener for menu -->
    <div v-if="showMenu" @click="showMenu = false" class="fixed inset-0 z-5"></div>
</template>