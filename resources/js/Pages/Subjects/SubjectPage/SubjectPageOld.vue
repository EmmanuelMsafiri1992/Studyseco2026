<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import TopicSidebar from './TopicSidebar.vue';
import LessonCard from './LessonCard.vue';
import ChunkedVideoUpload from '@/components/ChunkedVideoUpload.vue';

const props = defineProps({
    auth: Object,
    subject: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const selectedTopicId = ref(props.subject.topics[0]?.id || null);
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
    videoData: null, // Will contain the chunked upload result
});

const isUploading = ref(false);
const uploadProgress = ref(0);
const showChunkedUpload = ref(false);

const selectedTopic = computed(() => {
    return props.subject.topics.find(topic => topic.id === selectedTopicId.value);
});

const selectTopic = (topicId) => {
    selectedTopicId.value = topicId;
};

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
            
            // Select the new topic
            selectedTopicId.value = response.data.topic.id;
        }
    } catch (error) {
        console.error('Error adding topic:', error);
        alert('Error adding topic. Please try again.');
    }
};

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

const cancelAddTopic = () => {
    newTopic.value = { name: '', description: '' };
    showAddTopicForm.value = false;
};

const cancelAddLesson = () => {
    newLesson.value = { title: '', description: '', notes: '', videoData: null };
    showAddLessonForm.value = false;
    showChunkedUpload.value = false;
};
</script>

<template>
    <Head :title="subject.name" />
    
    <div class="flex h-screen bg-gradient-to-br from-slate-50 to-blue-50 font-sans text-slate-800">
        <!-- Sidebar with Topics -->
        <TopicSidebar 
            :subject="subject" 
            :selectedTopicId="selectedTopicId"
            @selectTopic="selectTopic"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="h-20 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 px-8 flex items-center justify-between relative z-50">
                <div>
                    <div class="flex items-center space-x-4">
                        <Link :href="route('subjects.index')" class="p-2 hover:bg-slate-100 rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">{{ subject.name }}</h1>
                            <p class="text-slate-500 text-sm">{{ subject.department }} - {{ subject.grade_level }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4" v-if="user?.role === 'admin' || user?.role === 'teacher'">
                    <button @click="showAddTopicForm = true"
                            class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Topic</span>
                        </div>
                    </button>
                    
                    <button @click="showAddLessonForm = true" :disabled="!selectedTopic"
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span>Add Lesson</span>
                        </div>
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-8 space-y-6">
                <!-- Selected Topic Content -->
                <div v-if="selectedTopic" class="space-y-6">
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800">{{ selectedTopic.name }}</h2>
                                <p class="text-slate-600 mt-1">{{ selectedTopic.description || 'No description provided.' }}</p>
                            </div>
                            <div class="flex items-center space-x-4 text-sm text-slate-500">
                                <span>{{ selectedTopic.lessons?.length || 0 }} lessons</span>
                                <span v-if="selectedTopic.total_duration">{{ Math.floor(selectedTopic.total_duration / 60) }}h {{ selectedTopic.total_duration % 60 }}m</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lessons Grid -->
                    <div v-if="selectedTopic.lessons && selectedTopic.lessons.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <LessonCard 
                            v-for="lesson in selectedTopic.lessons" 
                            :key="lesson.id" 
                            :lesson="lesson"
                            :canManage="user?.role === 'admin' || user?.role === 'teacher'"
                        />
                    </div>

                    <!-- No Lessons State -->
                    <div v-else class="text-center py-16">
                        <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 rounded-3xl flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-800 mb-2">No lessons yet</h3>
                        <p class="text-slate-500 mb-6">Start building your subject by adding the first lesson.</p>
                        <button v-if="user?.role === 'admin' || user?.role === 'teacher'" @click="showAddLessonForm = true"
                                class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Add First Lesson
                        </button>
                    </div>
                </div>

                <!-- No Topic Selected -->
                <div v-else-if="subject.topics.length === 0" class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-800 mb-2">No topics yet</h3>
                    <p class="text-slate-500 mb-6">Create your first topic to start organizing lessons.</p>
                    <button v-if="user?.role === 'admin' || user?.role === 'teacher'" @click="showAddTopicForm = true"
                            class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Create First Topic
                    </button>
                </div>
            </main>
        </div>

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
                                                <span v-if="newLesson.videoData.duration" class="ml-2">• {{ newLesson.videoData.duration }} min</span>
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
                                <span>Uploading... {{ uploadProgress }}%</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>