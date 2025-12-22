<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    post: Object,
    subjects: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

const form = useForm({
    type: props.post.type,
    title: props.post.title || '',
    content: props.post.content,
    subject_id: props.post.subject_id || '',
    priority: props.post.priority || 'medium',
    is_anonymous: props.post.is_anonymous || false,
    tags: props.post.tags || [],
    poll_options: props.post.poll_options || [],
    poll_expires_at: props.post.poll_expires_at || '',
});

const addPollOption = () => {
    form.poll_options.push('');
};

const removePollOption = (index) => {
    form.poll_options.splice(index, 1);
};

const updatePost = () => {
    form.patch(route('community.update', props.post.id), {
        onSuccess: () => {
            // Will redirect to show page automatically
        },
        onError: (errors) => {
            console.error('Update post errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Edit Post" />
    
    <DashboardLayout title="Edit Post" subtitle="Update your community post">
        <div class="space-y-6">
            <!-- Back Button -->
            <div class="flex items-center space-x-4">
                <Link :href="route('community.show', post.id)" 
                      class="flex items-center space-x-2 px-4 py-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-2xl transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Post</span>
                </Link>
            </div>

            <!-- Edit Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-8">
                <form @submit.prevent="updatePost">
                    <div class="space-y-6">
                        <!-- Post Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Post Type</label>
                            <select v-model="form.type" 
                                    class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="post">General Post</option>
                                <option value="question">Question</option>
                                <option value="resource">Resource Share</option>
                                <option value="announcement">Announcement</option>
                            </select>
                            <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Title (Optional)</label>
                            <input v-model="form.title" type="text" 
                                   class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200" 
                                   placeholder="Give your post a title...">
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Content*</label>
                            <textarea v-model="form.content" rows="8" required
                                      class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 resize-none transition-all duration-200" 
                                      placeholder="What's on your mind? Share your thoughts, ask a question, or help others..."></textarea>
                            <div v-if="form.errors.content" class="text-red-500 text-sm mt-1">{{ form.errors.content }}</div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Related Subject (Optional)</label>
                            <select v-model="form.subject_id" 
                                    class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="">Select a subject...</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.subject_id" class="text-red-500 text-sm mt-1">{{ form.errors.subject_id }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Priority</label>
                                <select v-model="form.priority" 
                                        class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <div v-if="form.errors.priority" class="text-red-500 text-sm mt-1">{{ form.errors.priority }}</div>
                            </div>

                            <!-- Anonymous -->
                            <div class="flex items-center pt-6">
                                <input v-model="form.is_anonymous" type="checkbox" id="anonymous" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="anonymous" class="ml-3 block text-sm text-gray-700">
                                    Post anonymously
                                </label>
                            </div>
                        </div>

                        <!-- Poll Options (for questions) -->
                        <div v-if="form.type === 'question'">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Poll Options (Optional)</label>
                            <div class="space-y-3">
                                <div v-for="(option, index) in form.poll_options" :key="index" class="flex items-center space-x-3">
                                    <input v-model="form.poll_options[index]" type="text" 
                                           class="flex-1 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200" 
                                           :placeholder="`Option ${index + 1}`">
                                    <button type="button" @click="removePollOption(index)" 
                                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <button type="button" @click="addPollOption" v-if="form.poll_options.length < 10"
                                        class="inline-flex items-center space-x-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Add Poll Option</span>
                                </button>
                            </div>
                            <div v-if="form.errors.poll_options" class="text-red-500 text-sm mt-1">{{ form.errors.poll_options }}</div>
                        </div>

                        <!-- Poll Expiry (for questions with poll options) -->
                        <div v-if="form.type === 'question' && form.poll_options.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Poll Expires At (Optional)</label>
                            <input v-model="form.poll_expires_at" type="datetime-local" 
                                   class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                            <div v-if="form.errors.poll_expires_at" class="text-red-500 text-sm mt-1">{{ form.errors.poll_expires_at }}</div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                        <Link :href="route('community.show', post.id)" 
                              class="px-6 py-3 border border-gray-300 rounded-2xl text-gray-700 hover:bg-gray-50 font-medium transition-all duration-200">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing" 
                                class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span v-if="form.processing" class="flex items-center space-x-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span>Updating...</span>
                            </span>
                            <span v-else>Update Post</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>