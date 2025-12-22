<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    auth: Object,
    subject: Object,
    relatedSubjects: Array,
    enrollment: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const subject = props.subject || {};
const relatedSubjects = props.relatedSubjects || [];
const enrollment = props.enrollment || {};

// State for expandable topics
const expandedTopics = ref(new Set());

const toggleTopic = (topicId) => {
    if (expandedTopics.value.has(topicId)) {
        expandedTopics.value.delete(topicId);
    } else {
        expandedTopics.value.add(topicId);
    }
};

const formatDuration = (minutes) => {
    if (minutes < 60) {
        return `${minutes} min`;
    } else {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`;
    }
};

const openVideoLesson = (lesson) => {
    if (lesson.video_path) {
        window.open(lesson.video_path, '_blank');
    }
};
</script>

<template>
    <Head :title="subject.name" />
    
    <DashboardLayout :title="subject.name" :subtitle="subject.description">
        <div class="space-y-8">
            <!-- Subject Header -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div :class="['p-8 text-white bg-gradient-to-br', 
                    subject.department === 'Sciences' ? 'from-blue-500 to-indigo-600' :
                    subject.department === 'Languages' ? 'from-emerald-500 to-teal-600' :
                    subject.department === 'Humanities' ? 'from-amber-500 to-orange-600' :
                    subject.department === 'Technical' ? 'from-purple-500 to-violet-600' :
                    subject.department === 'Commerce' ? 'from-green-500 to-emerald-600' :
                    subject.department === 'Technology' ? 'from-slate-500 to-slate-600' :
                    'from-indigo-500 to-purple-600'
                ]">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4 mb-4 md:mb-0">
                            <div class="text-5xl">{{ subject.icon }}</div>
                            <div>
                                <h1 class="text-3xl font-bold">{{ subject.name }}</h1>
                                <p class="text-white/80">{{ subject.department }} • {{ subject.grade_level }}</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 text-center">
                                <div class="text-2xl font-bold">{{ subject.topics_count || 0 }}</div>
                                <div class="text-sm text-white/80">Topics</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 text-center">
                                <div class="text-2xl font-bold">{{ subject.lessons_count || 0 }}</div>
                                <div class="text-sm text-white/80">Lessons</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 text-center">
                                <div class="text-2xl font-bold">{{ formatDuration(subject.total_duration || 0) }}</div>
                                <div class="text-sm text-white/80">Duration</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-8">
                    <p class="text-slate-600 text-lg leading-relaxed">{{ subject.detailed_description }}</p>
                </div>
            </div>

            <!-- Topics and Lessons -->
            <div v-if="subject.topics && subject.topics.length > 0" class="space-y-6">
                <h2 class="text-2xl font-bold text-slate-800">Subject Content</h2>
                
                <div class="space-y-4">
                    <div v-for="topic in subject.topics" :key="topic.id" 
                         class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                        
                        <!-- Topic Header -->
                        <button @click="toggleTopic(topic.id)" 
                                class="w-full p-6 text-left hover:bg-slate-50/50 transition-colors duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800 mb-2">{{ topic.name }}</h3>
                                    <p class="text-slate-600">{{ topic.description }}</p>
                                    <div class="flex items-center mt-2 space-x-4 text-sm text-slate-500">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ topic.lessons_count }} lesson{{ topic.lessons_count !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <div class="text-sm text-slate-500">
                                        Topic {{ topic.order_index }}
                                    </div>
                                    <svg :class="['w-5 h-5 text-slate-400 transition-transform duration-200', 
                                          expandedTopics.has(topic.id) ? 'rotate-180' : '']" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </button>
                        
                        <!-- Topic Lessons -->
                        <div v-if="expandedTopics.has(topic.id)" class="border-t border-slate-200/50">
                            <div class="p-6 space-y-4">
                                <div v-for="lesson in topic.lessons" :key="lesson.id" 
                                     class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl hover:bg-slate-100/50 transition-colors duration-200">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                            {{ lesson.order_index }}
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-800">{{ lesson.title }}</h4>
                                            <p class="text-sm text-slate-600">{{ lesson.description }}</p>
                                            <div class="flex items-center mt-1 text-xs text-slate-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ lesson.duration_minutes }} minutes
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <button v-if="lesson.video_path" 
                                                @click="openVideoLesson(lesson)"
                                                class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-xl transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m2 10V6a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                                            </svg>
                                            Watch Video
                                        </button>
                                        <div v-else class="px-4 py-2 bg-slate-200 text-slate-500 text-sm rounded-xl">
                                            Coming Soon
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Content State -->
            <div v-else class="bg-white/80 backdrop-blur-sm rounded-3xl p-12 shadow-xl border border-slate-200/50 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-4">No Content Available</h3>
                <p class="text-slate-600 mb-8">This subject doesn't have any topics or lessons yet. Content is being prepared.</p>
            </div>

            <!-- Related Subjects -->
            <div v-if="relatedSubjects.length > 0" class="space-y-6">
                <h2 class="text-2xl font-bold text-slate-800">Related Subjects</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link v-for="relatedSubject in relatedSubjects" :key="relatedSubject.id" 
                          :href="`/subjects/${relatedSubject.id}`"
                          class="block group">
                        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="flex items-center space-x-4">
                                <div class="text-3xl">{{ relatedSubject.icon }}</div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ relatedSubject.name }}</h3>
                                    <p class="text-sm text-slate-600 line-clamp-2">{{ relatedSubject.description }}</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Back Navigation -->
            <div class="flex justify-center">
                <Link href="/student/subjects" 
                      class="inline-flex items-center px-6 py-3 bg-slate-500 hover:bg-slate-600 text-white font-medium rounded-2xl transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to My Subjects
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>