<script setup>
const props = defineProps({
    subject: Object,
    selectedTopicId: [Number, String],
});

const emit = defineEmits(['selectTopic']);

const selectTopic = (topicId) => {
    emit('selectTopic', topicId);
};
</script>

<template>
    <div class="w-80 bg-white/80 backdrop-blur-xl border-r border-slate-200/50 flex-shrink-0 shadow-xl">
        <!-- Subject Header -->
        <div class="p-6 border-b border-slate-200/50">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 line-clamp-1">{{ subject.name }}</h2>
                    <p class="text-sm text-slate-500">{{ subject.code }}</p>
                </div>
            </div>
            <div class="text-xs text-slate-500 space-y-1">
                <p>{{ subject.topics?.length || 0 }} Topics</p>
                <p>{{ subject.teacher_name || 'No teacher assigned' }}</p>
            </div>
        </div>

        <!-- Topics List -->
        <nav class="flex-1 overflow-y-auto p-4">
            <div class="space-y-2">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Subject Content</h3>
                
                <div v-if="subject.topics && subject.topics.length > 0" class="space-y-2">
                    <button 
                        v-for="(topic, index) in subject.topics" 
                        :key="topic.id"
                        @click="selectTopic(topic.id)"
                        :class="[
                            'w-full text-left p-4 rounded-2xl transition-all duration-200 group',
                            selectedTopicId === topic.id 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' 
                                : 'bg-slate-50 hover:bg-slate-100 text-slate-700 hover:shadow-md'
                        ]"
                    >
                        <div class="flex items-start space-x-3">
                            <div :class="[
                                'w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0',
                                selectedTopicId === topic.id 
                                    ? 'bg-white/20 text-white' 
                                    : 'bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200'
                            ]">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 :class="[
                                    'font-semibold text-sm line-clamp-2 mb-1',
                                    selectedTopicId === topic.id ? 'text-white' : 'text-slate-800'
                                ]">
                                    {{ topic.name }}
                                </h4>
                                <p v-if="topic.description" :class="[
                                    'text-xs line-clamp-2 mb-2',
                                    selectedTopicId === topic.id ? 'text-white/80' : 'text-slate-500'
                                ]">
                                    {{ topic.description }}
                                </p>
                                <div :class="[
                                    'flex items-center space-x-4 text-xs',
                                    selectedTopicId === topic.id ? 'text-white/70' : 'text-slate-400'
                                ]">
                                    <span class="flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ topic.lessons?.length || 0 }} lessons</span>
                                    </span>
                                    <span v-if="topic.total_duration" class="flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ Math.floor(topic.total_duration / 60) }}h {{ topic.total_duration % 60 }}m</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- No Topics State -->
                <div v-else class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-slate-500">No topics yet</p>
                </div>
            </div>
        </nav>

        <!-- Progress Bar -->
        <div v-if="subject.topics && subject.topics.length > 0" class="p-4 border-t border-slate-200/50">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span>Subject Progress</span>
                <span>{{ Math.round((subject.topics.filter(t => t.lessons?.some(l => l.is_published)).length / subject.topics.length) * 100) }}%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                     :style="{ width: Math.round((subject.topics.filter(t => t.lessons?.some(l => l.is_published)).length / subject.topics.length) * 100) + '%' }">
                </div>
            </div>
        </div>
    </div>
</template>