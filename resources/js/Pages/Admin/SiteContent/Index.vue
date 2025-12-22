<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    contents: Array,
    studentStories: Array,
    auth: Object,
});

// Content Management
const showContentModal = ref(false);
const showStoryModal = ref(false);
const editingContent = ref(null);
const editingStory = ref(null);

const contentForm = useForm({
    key: '',
    title: '',
    content: '',
    meta_data: {},
    is_active: true,
});

const storyForm = useForm({
    name: '',
    location: '',
    country_flag: '🇲🇼',
    current_status: '',
    story: '',
    achievements: [],
    msce_credits: '',
    avatar_color_from: 'emerald-500',
    avatar_color_to: 'blue-500',
    achievement_icon: '🎓',
    is_featured: true,
    is_active: true,
});

// Content methods
const openContentModal = (content = null) => {
    editingContent.value = content;
    if (content) {
        contentForm.key = content.key;
        contentForm.title = content.title;
        contentForm.content = content.content;
        contentForm.meta_data = content.meta_data || {};
        contentForm.is_active = content.is_active;
    } else {
        contentForm.reset();
        contentForm.is_active = true;
    }
    showContentModal.value = true;
};

const saveContent = () => {
    if (editingContent.value) {
        contentForm.put(route('admin.site-content.contents.update', editingContent.value.id), {
            onSuccess: () => {
                showContentModal.value = false;
                editingContent.value = null;
            }
        });
    } else {
        contentForm.post(route('admin.site-content.contents.store'), {
            onSuccess: () => {
                showContentModal.value = false;
            }
        });
    }
};

const deleteContent = (content) => {
    if (confirm('Are you sure you want to delete this content?')) {
        useForm().delete(route('admin.site-content.contents.destroy', content.id));
    }
};

// Story methods
const openStoryModal = (story = null) => {
    editingStory.value = story;
    if (story) {
        storyForm.name = story.name;
        storyForm.location = story.location;
        storyForm.country_flag = story.country_flag;
        storyForm.current_status = story.current_status;
        storyForm.story = story.story;
        storyForm.achievements = story.achievements || [];
        storyForm.msce_credits = story.msce_credits || '';
        storyForm.avatar_color_from = story.avatar_color_from;
        storyForm.avatar_color_to = story.avatar_color_to;
        storyForm.achievement_icon = story.achievement_icon;
        storyForm.is_featured = story.is_featured;
        storyForm.is_active = story.is_active;
    } else {
        storyForm.reset();
        storyForm.country_flag = '🇲🇼';
        storyForm.avatar_color_from = 'emerald-500';
        storyForm.avatar_color_to = 'blue-500';
        storyForm.achievement_icon = '🎓';
        storyForm.is_featured = true;
        storyForm.is_active = true;
    }
    showStoryModal.value = true;
};

const saveStory = () => {
    if (editingStory.value) {
        storyForm.put(route('admin.site-content.student-stories.update', editingStory.value.id), {
            onSuccess: () => {
                showStoryModal.value = false;
                editingStory.value = null;
            }
        });
    } else {
        storyForm.post(route('admin.site-content.student-stories.store'), {
            onSuccess: () => {
                showStoryModal.value = false;
            }
        });
    }
};

const deleteStory = (story) => {
    if (confirm('Are you sure you want to delete this student story?')) {
        useForm().delete(route('admin.site-content.student-stories.destroy', story.id));
    }
};

const colorOptions = [
    { label: 'Emerald to Blue', from: 'emerald-500', to: 'blue-500' },
    { label: 'Blue to Purple', from: 'blue-500', to: 'purple-500' },
    { label: 'Purple to Pink', from: 'purple-500', to: 'pink-500' },
    { label: 'Orange to Red', from: 'orange-500', to: 'red-500' },
    { label: 'Green to Teal', from: 'green-500', to: 'teal-500' },
    { label: 'Indigo to Blue', from: 'indigo-500', to: 'blue-500' },
];

const achievementIcons = ['🎓', '🏆', '⭐', '💼', '🌟', '🎯', '🚀', '💡'];
</script>

<template>
    <DashboardLayout 
        title="Site Content Management" 
        subtitle="Manage website content and student success stories">
        
        <!-- Website Content Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Website Content</h2>
                    <p class="text-sm text-slate-500">Manage dynamic content displayed on your website</p>
                </div>
                <button @click="openContentModal()" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Content
                    </span>
                </button>
            </div>

            <div v-if="contents.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No content found</h3>
                <p class="mt-1 text-sm text-slate-500">Get started by creating your first content item.</p>
            </div>

            <div v-else class="overflow-hidden border border-slate-200/50 rounded-2xl">
                <table class="min-w-full divide-y divide-slate-200/50">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Content Preview</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200/30">
                        <tr v-for="content in contents" :key="content.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-slate-900">{{ content.key }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-900">{{ content.title }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ content.content.substring(0, 100) }}{{ content.content.length > 100 ? '...' : '' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="content.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200'" 
                                      class="inline-flex px-3 py-1 text-xs font-semibold rounded-full border">
                                    {{ content.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <button @click="openContentModal(content)" 
                                        class="text-indigo-600 hover:text-indigo-800 transition-colors duration-150">Edit</button>
                                <button @click="deleteContent(content)" 
                                        class="text-red-600 hover:text-red-800 transition-colors duration-150">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Stories Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Student Success Stories</h2>
                    <p class="text-sm text-slate-500">Showcase inspiring student achievements and testimonials</p>
                </div>
                <button @click="openStoryModal()" 
                        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Story
                    </span>
                </button>
            </div>

            <div v-if="studentStories.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6-12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No stories found</h3>
                <p class="mt-1 text-sm text-slate-500">Share inspiring student success stories to motivate others.</p>
            </div>

            <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="story in studentStories" :key="story.id" 
                     class="bg-gradient-to-br from-white to-slate-50/80 rounded-2xl p-6 shadow-lg border border-slate-200/50 hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-center mb-4">
                        <div :class="`bg-gradient-to-r from-${story.avatar_color_from} to-${story.avatar_color_to}`" 
                             class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold mr-4 shadow-lg">
                            {{ story.name.split(' ').map(n => n[0]).join('').slice(0, 2) }}
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-900">{{ story.name }}</h4>
                            <p class="text-sm text-slate-600">{{ story.country_flag }} {{ story.location }}</p>
                        </div>
                    </div>
                    
                    <p class="text-sm text-slate-700 mb-4 leading-relaxed">{{ story.story.substring(0, 120) }}{{ story.story.length > 120 ? '...' : '' }}</p>
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <span :class="story.is_featured ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-slate-100 text-slate-800 border-slate-200'" 
                                  class="px-2 py-1 text-xs rounded-full font-medium border">
                                {{ story.is_featured ? 'Featured' : 'Regular' }}
                            </span>
                            <span :class="story.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200'" 
                                  class="px-2 py-1 text-xs rounded-full font-medium border">
                                {{ story.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center text-slate-600">
                            <span class="text-lg mr-1">{{ story.achievement_icon }}</span>
                            <span class="text-xs">{{ story.msce_credits }} Credits</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button @click="openStoryModal(story)" 
                                class="px-4 py-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-xl transition-all duration-150 text-sm font-medium">
                            Edit
                        </button>
                        <button @click="deleteStory(story)" 
                                class="px-4 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-xl transition-all duration-150 text-sm font-medium">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Modal -->
        <div v-if="showContentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 w-full max-w-2xl mx-4 shadow-2xl border border-slate-200/50">
                <h3 class="text-lg font-semibold mb-4 text-slate-800">{{ editingContent ? 'Edit Content' : 'Add New Content' }}</h3>
                
                <form @submit.prevent="saveContent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Content Key</label>
                        <input v-model="contentForm.key" type="text" required 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200">
                        <p class="text-xs text-slate-500 mt-1">Unique identifier for this content (e.g., hero_title, about_text)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                        <input v-model="contentForm.title" type="text" required 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Content</label>
                        <textarea v-model="contentForm.content" required rows="6"
                                  class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200"></textarea>
                    </div>
                    
                    <div class="flex items-center">
                        <input v-model="contentForm.is_active" type="checkbox" id="content_active" class="mr-2 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="content_active" class="text-sm text-slate-700">Active</label>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="showContentModal = false" 
                                class="px-6 py-3 border border-slate-300 rounded-2xl text-slate-700 hover:bg-slate-50 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="contentForm.processing"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-blue-600 hover:to-purple-700 disabled:opacity-50 transition-all duration-200">
                            {{ contentForm.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Story Modal -->
        <div v-if="showStoryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200/50">
                <h3 class="text-lg font-semibold mb-4 text-slate-800">{{ editingStory ? 'Edit Student Story' : 'Add New Student Story' }}</h3>
                
                <form @submit.prevent="saveStory" class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Student Name</label>
                            <input v-model="storyForm.name" type="text" required 
                                   class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
                            <input v-model="storyForm.location" type="text" required 
                                   class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Country Flag</label>
                            <input v-model="storyForm.country_flag" type="text" required 
                                   class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">MSCE Credits</label>
                            <input v-model="storyForm.msce_credits" type="number" min="0" max="10"
                                   class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Current Status</label>
                        <input v-model="storyForm.current_status" type="text" required 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        <p class="text-xs text-slate-500 mt-1">e.g., "University of Edinburgh - Engineering"</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Student Story</label>
                        <textarea v-model="storyForm.story" required rows="4"
                                  class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200"></textarea>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Avatar Colors</label>
                            <select v-model="storyForm.avatar_color_from" 
                                    @change="storyForm.avatar_color_to = colorOptions.find(c => c.from === storyForm.avatar_color_from)?.to || 'blue-500'"
                                    class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                                <option v-for="color in colorOptions" :key="color.from" :value="color.from">{{ color.label }}</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Achievement Icon</label>
                            <select v-model="storyForm.achievement_icon" 
                                    class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                                <option v-for="icon in achievementIcons" :key="icon" :value="icon">{{ icon }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input v-model="storyForm.is_featured" type="checkbox" id="story_featured" class="mr-2 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="story_featured" class="text-sm text-slate-700">Featured Story</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input v-model="storyForm.is_active" type="checkbox" id="story_active" class="mr-2 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="story_active" class="text-sm text-slate-700">Active</label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="showStoryModal = false" 
                                class="px-6 py-3 border border-slate-300 rounded-2xl text-slate-700 hover:bg-slate-50 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="storyForm.processing"
                                class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-semibold hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50 transition-all duration-200">
                            {{ storyForm.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>