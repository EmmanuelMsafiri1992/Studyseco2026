<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    posts: Object,
    subjects: Array,
    filters: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const selectedPost = ref(null);
const showDeleteModal = ref(null);

const commentForm = useForm({
    content: '',
    parent_id: null
});

const deleteForm = useForm({});

const showComments = ref({});

const getPostIcon = (type) => {
    const icons = {
        post: "M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5",
        question: "M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        resource: "M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
        announcement: "M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
    };
    return icons[type] || icons.post;
};

const getPriorityColor = (priority) => {
    const colors = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-blue-100 text-blue-800', 
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800'
    };
    return colors[priority] || colors.medium;
};

const formatTime = (date) => {
    const now = new Date();
    const postDate = new Date(date);
    const diffMs = now - postDate;
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return postDate.toLocaleDateString();
};

const toggleReaction = async (post, type = 'like') => {
    try {
        const response = await axios.post(route('community.react', post.id), { type });
        
        // Update the post data with the server response
        if (response.data.success) {
            // Update reaction counts
            post.likes_count = response.data.likes_count;
            
            // Update all reaction states
            post.user_has_liked = response.data.user_has_liked;
            post.user_has_love = response.data.user_has_love;
            post.user_has_helpful = response.data.user_has_helpful;
            post.user_has_funny = response.data.user_has_funny;
            post.user_has_solved = response.data.user_has_solved;
            post.user_reactions = response.data.user_reactions;
            
            // Show visual feedback
            showReactionFeedback(post.id, type, response.data.has_reaction);
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
        showErrorToast('Failed to react to post. Please try again.');
    }
};

// Visual feedback for reactions
const showReactionFeedback = (postId, type, hasReacted) => {
    const typeEmojis = {
        like: '❤️',
        love: '💖',
        helpful: '💯',
        funny: '😂',
        solved: '✅'
    };
    
    const emoji = typeEmojis[type] || '👍';
    const message = hasReacted 
        ? `${emoji} You reacted with ${type}!` 
        : `${type} reaction removed`;
    
    showToast(message, hasReacted ? 'success' : 'info');
};

// Toast notification system
const showToast = (message, type = 'success') => {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 2000);
};

const showErrorToast = (message) => {
    showToast(message, 'error');
};

const submitComment = async (post) => {
    commentForm.post(route('community.comment', post.id), {
        onSuccess: () => {
            commentForm.reset();
            // Comments count will be updated automatically when page refreshes
        },
        onError: (errors) => {
            console.error('Error posting comment:', errors);
        }
    });
};

const toggleComments = (postId) => {
    showComments.value[postId] = !showComments.value[postId];
};


const confirmDelete = (postId) => {
    deleteForm.delete(route('community.destroy', postId), {
        onSuccess: () => {
            showDeleteModal.value = null;
        }
    });
};

// Share post functionality
const sharePost = async (post) => {
    const postUrl = route('community.show', post.id);
    const shareTitle = post.title || 'Check out this post from our community!';
    const shareText = `${shareTitle}\n\n${post.content.substring(0, 100)}${post.content.length > 100 ? '...' : ''}`;
    
    if (navigator.share) {
        // Use native sharing API if available (mobile)
        try {
            await navigator.share({
                title: shareTitle,
                text: shareText,
                url: window.location.origin + postUrl
            });
        } catch (err) {
            console.log('Error sharing:', err);
            fallbackShare(postUrl);
        }
    } else {
        // Fallback to copying to clipboard
        fallbackShare(postUrl);
    }
};

const fallbackShare = async (postUrl) => {
    try {
        const fullUrl = window.location.origin + postUrl;
        await navigator.clipboard.writeText(fullUrl);
        showShareToast('Link copied to clipboard!');
    } catch (err) {
        // If clipboard API fails, show a prompt
        const fullUrl = window.location.origin + postUrl;
        prompt('Copy this link:', fullUrl);
    }
};

const showShareToast = (message) => {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-2xl shadow-lg z-50 transform translate-x-full transition-transform duration-300';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
};
</script>

<template>
    <Head title="Community Feed" />
    
    <DashboardLayout title="Community Feed" subtitle="Share, ask, learn together">
        <div class="space-y-6">
            <!-- Navigation Tabs -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-2 shadow-xl border border-slate-200/50">
                <div class="flex space-x-1">
                    <Link :href="route('chat.index')" 
                          class="flex-1 text-center py-3 px-4 rounded-2xl font-semibold transition-all duration-200 text-slate-600 hover:text-slate-800 hover:bg-slate-100">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                            </svg>
                            <span>Chat Groups</span>
                        </div>
                    </Link>
                    <Link :href="route('community.index')" 
                          class="flex-1 text-center py-3 px-4 rounded-2xl font-semibold transition-all duration-200 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <span>Community Feed</span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Filters and Create Post -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('community.index', { filter: 'all' })" 
                              :class="['px-4 py-2 rounded-2xl font-medium transition-all duration-200', 
                                       filters.filter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']">
                            All Posts
                        </Link>
                        <Link :href="route('community.index', { filter: 'question' })" 
                              :class="['px-4 py-2 rounded-2xl font-medium transition-all duration-200', 
                                       filters.filter === 'question' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']">
                            Questions
                        </Link>
                        <Link :href="route('community.index', { filter: 'resource' })" 
                              :class="['px-4 py-2 rounded-2xl font-medium transition-all duration-200', 
                                       filters.filter === 'resource' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']">
                            Resources
                        </Link>
                        <Link :href="route('community.index', { filter: 'announcement' })" 
                              :class="['px-4 py-2 rounded-2xl font-medium transition-all duration-200', 
                                       filters.filter === 'announcement' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']">
                            Announcements
                        </Link>
                    </div>

                    <!-- Create Post Button -->
                    <Link :href="route('community.create')" 
                            class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Create Post</span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Posts Feed -->
            <div class="space-y-6">
                <div v-for="post in posts.data" :key="post.id" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                    <!-- Post Header -->
                    <div class="p-6 border-b border-slate-200/50">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-4">
                                <img :src="post.is_anonymous ? 'https://ui-avatars.com/api/?name=Anonymous&background=6366f1&color=ffffff' : 
                                           (post.user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}&background=6366f1&color=ffffff`)" 
                                     :alt="post.is_anonymous ? 'Anonymous' : post.user.name"
                                     class="w-12 h-12 rounded-full">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h3 class="font-semibold text-slate-800">
                                            {{ post.is_anonymous ? 'Anonymous' : post.user.name }}
                                        </h3>
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getPostIcon(post.type)"></path>
                                            </svg>
                                            <span class="text-sm text-slate-500 capitalize">{{ post.type }}</span>
                                        </div>
                                        <span v-if="post.priority !== 'medium'" :class="['px-2 py-1 text-xs font-medium rounded-full', getPriorityColor(post.priority)]">
                                            {{ post.priority.toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-slate-500">
                                        <span>{{ formatTime(post.created_at) }}</span>
                                        <span v-if="post.subject" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                            {{ post.subject.name }}
                                        </span>
                                        <span v-if="post.is_solved" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                            ✓ Solved
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="user.id === post.user_id || user.role === 'admin'" class="flex items-center space-x-2">
                                <Link :href="route('community.edit', post.id)" 
                                      class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </Link>
                                <button @click="showDeleteModal = post.id" 
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Post Title (if exists) -->
                        <h2 v-if="post.title" class="text-xl font-bold text-slate-800 mt-4">{{ post.title }}</h2>
                    </div>

                    <!-- Post Content -->
                    <div class="p-6">
                        <div class="prose max-w-none text-slate-700 mb-4">
                            {{ post.content }}
                        </div>

                        <!-- Tags -->
                        <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-2 mb-4">
                            <span v-for="tag in post.tags" :key="tag" class="px-3 py-1 text-sm bg-slate-100 text-slate-600 rounded-full">
                                #{{ tag }}
                            </span>
                        </div>

                        <!-- Poll (if exists) -->
                        <div v-if="post.poll_options && post.poll_options.length > 0" class="bg-slate-50 rounded-2xl p-4 mb-4">
                            <h4 class="font-semibold text-slate-800 mb-3">Poll</h4>
                            <div class="space-y-2">
                                <div v-for="(option, index) in post.poll_options" :key="index" class="flex items-center justify-between p-3 bg-white rounded-xl border hover:border-indigo-300 cursor-pointer">
                                    <span>{{ option }}</span>
                                    <span class="text-sm text-slate-500">
                                        {{ post.poll_votes && post.poll_votes[index] ? post.poll_votes[index].length : 0 }} votes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Actions -->
                    <div class="px-6 py-4 border-t border-slate-200/50 bg-slate-50/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-6">
                                <!-- Like Button -->
                                <button @click="toggleReaction(post, 'like')" 
                                        :class="['flex items-center space-x-2 px-3 py-2 rounded-xl transition-all duration-200 hover:scale-105', 
                                                post.user_has_liked ? 'bg-red-100 text-red-600 shadow-md' : 'text-slate-500 hover:bg-red-50 hover:text-red-500']">
                                    <svg class="w-5 h-5 transition-all duration-200" 
                                         :class="post.user_has_liked ? 'fill-current scale-110' : ''" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">{{ post.likes_count || 0 }}</span>
                                </button>
                                
                                <!-- Additional Reaction Buttons -->
                                <div class="flex items-center space-x-1">
                                    <button @click="toggleReaction(post, 'helpful')" 
                                            :class="['p-2 rounded-lg transition-all duration-200 hover:scale-110 transform', 
                                                    post.user_has_helpful ? 'bg-yellow-100 text-yellow-600 scale-110' : 'text-slate-400 hover:bg-yellow-50 hover:text-yellow-500']">
                                        💯
                                    </button>
                                    <button @click="toggleReaction(post, 'funny')" 
                                            :class="['p-2 rounded-lg transition-all duration-200 hover:scale-110 transform', 
                                                    post.user_has_funny ? 'bg-blue-100 text-blue-600 scale-110' : 'text-slate-400 hover:bg-blue-50 hover:text-blue-500']">
                                        😂
                                    </button>
                                    <button @click="toggleReaction(post, 'love')" 
                                            :class="['p-2 rounded-lg transition-all duration-200 hover:scale-110 transform', 
                                                    post.user_has_love ? 'bg-pink-100 text-pink-600 scale-110' : 'text-slate-400 hover:bg-pink-50 hover:text-pink-500']">
                                        💖
                                    </button>
                                </div>

                                <button @click="toggleComments(post.id)" class="flex items-center space-x-2 px-3 py-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                                    </svg>
                                    <span class="text-sm font-medium">{{ post.comments_count }}</span>
                                </button>

                                <button @click="sharePost(post)" class="flex items-center space-x-2 px-3 py-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Share</span>
                                </button>
                            </div>

                            <div class="flex items-center space-x-2 text-sm text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span>{{ post.views_count }} views</span>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <div v-if="showComments[post.id]" class="mt-4 pt-4 border-t border-slate-200">
                            <!-- Enhanced Comment Form -->
                            <form @submit.prevent="submitComment(post)" class="mb-4">
                                <div class="flex space-x-3">
                                    <img :src="user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=ffffff`" 
                                         :alt="user.name" class="w-8 h-8 rounded-full flex-shrink-0">
                                    <div class="flex-1 space-y-2">
                                        <div class="relative">
                                            <input v-model="commentForm.content" type="text" placeholder="Write a comment..." 
                                                   class="w-full px-4 py-2 pr-20 bg-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                            <!-- Quick Emoji Buttons -->
                                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
                                                <button type="button" @click="commentForm.content += ' 👍'" class="text-slate-400 hover:text-indigo-500 text-sm p-1 rounded hover:bg-indigo-50 transition-all duration-200">👍</button>
                                                <button type="button" @click="commentForm.content += ' ❤️'" class="text-slate-400 hover:text-red-500 text-sm p-1 rounded hover:bg-red-50 transition-all duration-200">❤️</button>
                                                <button type="button" @click="commentForm.content += ' 😊'" class="text-slate-400 hover:text-yellow-500 text-sm p-1 rounded hover:bg-yellow-50 transition-all duration-200">😊</button>
                                            </div>
                                        </div>
                                        <!-- View full post link -->
                                        <div class="flex justify-between items-center">
                                            <Link :href="route('community.show', post.id)" class="text-xs text-indigo-600 hover:text-indigo-800 transition-colors duration-200">
                                                💬 View full discussion with attachments & reactions
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Comments List -->
                            <div class="space-y-3">
                                <div v-for="comment in post.comments" :key="comment.id" class="flex space-x-3">
                                    <img :src="comment.user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name)}&background=6366f1&color=ffffff`" 
                                         :alt="comment.user.name" class="w-8 h-8 rounded-full flex-shrink-0">
                                    <div class="flex-1 bg-slate-100 rounded-2xl px-4 py-2">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-medium text-sm text-slate-800">{{ comment.user.name }}</span>
                                            <span class="text-xs text-slate-500">{{ formatTime(comment.created_at) }}</span>
                                        </div>
                                        <p class="text-sm text-slate-700">{{ comment.content }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="posts.data.length === 0" class="bg-white/80 backdrop-blur-sm rounded-3xl p-12 text-center shadow-xl border border-slate-200/50">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No posts yet</h3>
                    <p class="text-slate-500 mb-6">Be the first to start a conversation in the community!</p>
                    <Link :href="route('community.create')" 
                            class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Create First Post
                    </Link>
                </div>
            </div>
        </div>


        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showDeleteModal = null"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Post</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this post? This action cannot be undone and all comments and reactions will also be deleted.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="confirmDelete(showDeleteModal)" :disabled="deleteForm.processing"
                                class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-6 py-3 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span v-if="deleteForm.processing">Deleting...</span>
                            <span v-else>Delete Post</span>
                        </button>
                        <button type="button" @click="showDeleteModal = null" 
                                class="mt-3 w-full inline-flex justify-center rounded-2xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>