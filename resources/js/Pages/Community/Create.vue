<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    subjects: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

const form = useForm({
    type: 'post',
    title: '',
    content: '',
    subject_id: '',
    priority: 'medium',
    is_anonymous: false,
    tags: [],
    poll_options: [],
    poll_expires_at: '',
    attachments: []
});

const showEmojiPicker = ref(false);
const showStickerPicker = ref(false);
const showAttachmentDialog = ref(false);
const activeEmojiCategory = ref('smileys');
const activeStickerCategory = ref('study');
const tagInput = ref('');

// Enhanced Emoji and Sticker System (same as Show.vue)
const emojiCategories = {
    smileys: {
        name: 'Smileys & Emotion',
        icon: '😀',
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🥸', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤐', '🤨', '😐', '😑', '😶', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃']
    },
    gestures: {
        name: 'People & Body',
        icon: '👋',
        emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '👊', '✊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '💋', '🩸']
    },
    objects: {
        name: 'Objects',
        icon: '⚽',
        emojis: ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🪃', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛷', '⛸️', '🥌', '🎿', '⛷️', '🏂', '🪂', '🏋️‍♀️', '🏋️', '🏋️‍♂️', '🤼‍♀️', '🤼', '🤼‍♂️', '🤸‍♀️', '🤸', '🤸‍♂️', '⛹️‍♀️', '⛹️', '⛹️‍♂️', '🤺', '🤾‍♀️', '🤾', '🤾‍♂️', '🏌️‍♀️', '🏌️', '🏌️‍♂️', '🧘‍♀️', '🧘', '🧘‍♂️', '🏄‍♀️', '🏄', '🏄‍♂️', '🏊‍♀️', '🏊', '🏊‍♂️', '🤽‍♀️', '🤽', '🤽‍♂️', '🚣‍♀️', '🚣', '🚣‍♂️', '🧗‍♀️', '🧗', '🧗‍♂️', '🚵‍♀️', '🚵', '🚵‍♂️', '🚴‍♀️', '🚴', '🚴‍♂️', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🏵️', '🎗️']
    },
    nature: {
        name: 'Animals & Nature',
        icon: '🌱',
        emojis: ['🌱', '🌿', '🍀', '🎍', '🎋', '🍃', '🍂', '🍁', '🍄', '🐚', '🌾', '💐', '🌷', '🌹', '🥀', '🌺', '🌸', '🌼', '🌻', '🌞', '🌝', '🌛', '🌜', '🌚', '🌕', '🌖', '🌗', '🌘', '🌑', '🌒', '🌓', '🌔', '🌙', '🌎', '🌍', '🌏', '🪐', '💫', '⭐', '🌟', '✨', '⚡', '☄️', '💥', '🔥', '🌪️', '🌈', '☀️', '🌤️', '⛅', '🌦️', '🌧️', '⛈️', '🌩️', '🌨️', '❄️', '☃️', '⛄', '🌬️', '💨', '💧', '💦', '☔', '☂️', '🌊', '🌫️']
    },
    food: {
        name: 'Food & Drink',
        icon: '🍎',
        emojis: ['🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶️', '🫑', '🌽', '🥕', '🫒', '🧄', '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🫓', '🥙', '🌮', '🌯', '🫔', '🥗', '🥘', '🫕', '🥫', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙', '🍚', '🍘', '🍥', '🥠', '🥮', '🍢', '🍡', '🍧', '🍨', '🍦', '🥧', '🧁', '🍰', '🎂', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩', '🍪', '🌰', '🥜', '🍯']
    },
    activities: {
        name: 'Activities',
        icon: '⚽',
        emojis: ['🎃', '🎄', '🎆', '🎇', '🧨', '✨', '🎈', '🎉', '🎊', '🎋', '🎍', '🎎', '🎏', '🎐', '🎑', '🧧', '🎀', '🎁', '🎗️', '🎟️', '🎫', '🎖️', '🏆', '🏅', '🥇', '🥈', '🥉', '⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🪃', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛷', '⛸️', '🥌', '🎿', '⛷️', '🏂', '🪂', '🏋️‍♀️', '🏋️', '🏋️‍♂️', '🤼‍♀️', '🤼', '🤼‍♂️', '🤸‍♀️', '🤸', '🤸‍♂️', '⛹️‍♀️', '⛹️', '⛹️‍♂️', '🤺', '🤾‍♀️', '🤾', '🤾‍♂️', '🏌️‍♀️', '🏌️', '🏌️‍♂️', '🧘‍♀️', '🧘', '🧘‍♂️', '🏄‍♀️', '🏄', '🏄‍♂️', '🏊‍♀️', '🏊', '🏊‍♂️', '🤽‍♀️', '🤽', '🤽‍♂️', '🚣‍♀️', '🚣', '🚣‍♂️', '🧗‍♀️', '🧗', '🧗‍♂️', '🚵‍♀️', '🚵', '🚵‍♂️', '🚴‍♀️', '🚴', '🚴‍♂️', '🎯', '🪀', '🎱', '🔮', '🪀', '🎰', '🎲', '🧩', '🎮', '🕹️', '🎳']
    },
    symbols: {
        name: 'Symbols',
        icon: '❤️',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '♠️', '♥️', '♦️', '♣️', '♟️', '🔇', '🔈', '🔉', '🔊', '📢', '📣', '📯', '🔔', '🔕', '🎼', '🎵', '🎶', '🚭', '🚯', '🚱', '🚷', '📵', '🔞', '☢️', '☣️', '⬆️', '↗️', '➡️', '↘️', '⬇️', '↙️', '⬅️', '↖️', '↕️', '↔️', '↩️', '↪️', '⤴️', '⤵️', '🔃', '🔄', '🔙', '🔚', '🔛', '🔜', '🔝', '🛐', '⚛️', '🕉️', '✡️', '☸️', '☯️', '✝️', '☦️', '☪️', '☮️', '🕎', '🔯', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '⛎', '🔀', '🔁', '🔂', '▶️', '⏩', '⏭️', '⏯️', '◀️', '⏪', '⏮️', '🔼', '⏫', '🔽', '⏬', '⏸️', '⏹️', '⏺️', '⏏️', '🎦']
    }
};

// Sticker collections (same as Show.vue)
const stickerCategories = {
    study: {
        name: 'Study & Learning',
        icon: '📚',
        stickers: [
            { emoji: '📚', name: 'Books' },
            { emoji: '📖', name: 'Open Book' },
            { emoji: '📝', name: 'Writing' },
            { emoji: '✏️', name: 'Pencil' },
            { emoji: '🖊️', name: 'Pen' },
            { emoji: '📊', name: 'Chart' },
            { emoji: '📈', name: 'Growth' },
            { emoji: '🧮', name: 'Calculator' },
            { emoji: '🔬', name: 'Microscope' },
            { emoji: '🧪', name: 'Test Tube' },
            { emoji: '⚗️', name: 'Experiment' },
            { emoji: '🔭', name: 'Telescope' },
            { emoji: '📐', name: 'Ruler' },
            { emoji: '📏', name: 'Straight Ruler' },
            { emoji: '🗂️', name: 'Files' },
            { emoji: '📁', name: 'Folder' },
            { emoji: '📋', name: 'Clipboard' },
            { emoji: '🎓', name: 'Graduation' },
            { emoji: '🏫', name: 'School' },
            { emoji: '🎒', name: 'Backpack' },
            { emoji: '💡', name: 'Idea' },
            { emoji: '🧠', name: 'Brain' },
            { emoji: '🤓', name: 'Nerd' },
            { emoji: '💻', name: 'Laptop' }
        ]
    },
    reactions: {
        name: 'Reactions',
        icon: '🎉',
        stickers: [
            { emoji: '🎉', name: 'Party' },
            { emoji: '👏', name: 'Clap' },
            { emoji: '💯', name: 'Perfect' },
            { emoji: '🔥', name: 'Fire' },
            { emoji: '⭐', name: 'Star' },
            { emoji: '✨', name: 'Sparkles' },
            { emoji: '💪', name: 'Strong' },
            { emoji: '👍', name: 'Thumbs Up' },
            { emoji: '👌', name: 'OK' },
            { emoji: '✅', name: 'Check' },
            { emoji: '❤️', name: 'Love' },
            { emoji: '😍', name: 'Heart Eyes' },
            { emoji: '🤩', name: 'Star Eyes' },
            { emoji: '🙌', name: 'Praise' },
            { emoji: '🚀', name: 'Rocket' },
            { emoji: '⚡', name: 'Lightning' },
            { emoji: '💎', name: 'Diamond' },
            { emoji: '🏆', name: 'Trophy' },
            { emoji: '🥇', name: 'Gold Medal' },
            { emoji: '🎯', name: 'Target' },
            { emoji: '💫', name: 'Shooting Star' },
            { emoji: '🌟', name: 'Glowing Star' },
            { emoji: '🔮', name: 'Crystal Ball' },
            { emoji: '🎪', name: 'Circus' }
        ]
    },
    school: {
        name: 'School Life',
        icon: '🎒',
        stickers: [
            { emoji: '🎒', name: 'Backpack' },
            { emoji: '📚', name: 'Books' },
            { emoji: '📖', name: 'Open Book' },
            { emoji: '📝', name: 'Memo' },
            { emoji: '📄', name: 'Document' },
            { emoji: '📃', name: 'Page' },
            { emoji: '📑', name: 'Bookmark Tabs' },
            { emoji: '📊', name: 'Bar Chart' },
            { emoji: '📈', name: 'Chart Up' },
            { emoji: '📉', name: 'Chart Down' },
            { emoji: '🗒️', name: 'Notepad' },
            { emoji: '🗓️', name: 'Calendar' },
            { emoji: '📅', name: 'Date' },
            { emoji: '📆', name: 'Calendar Tear' },
            { emoji: '🗃️', name: 'Card File' },
            { emoji: '🗳️', name: 'Ballot Box' },
            { emoji: '🗂️', name: 'Card Index' },
            { emoji: '📋', name: 'Clipboard' },
            { emoji: '📌', name: 'Pin' },
            { emoji: '📍', name: 'Round Pin' },
            { emoji: '📎', name: 'Paperclip' },
            { emoji: '🖇️', name: 'Linked Clips' },
            { emoji: '📐', name: 'Triangular Ruler' },
            { emoji: '📏', name: 'Straight Ruler' }
        ]
    }
};

const addEmoji = (emoji) => {
    form.content += emoji;
    showEmojiPicker.value = false;
};

const addSticker = (sticker) => {
    form.content += ` ${sticker.emoji} `;
    showStickerPicker.value = false;
};

const addPollOption = () => {
    form.poll_options.push('');
};

const removePollOption = (index) => {
    form.poll_options.splice(index, 1);
};

const addTag = () => {
    if (tagInput.value.trim() && !form.tags.includes(tagInput.value.trim())) {
        form.tags.push(tagInput.value.trim());
        tagInput.value = '';
    }
};

const removeTag = (index) => {
    form.tags.splice(index, 1);
};

// File attachment handling
const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    form.attachments = [...form.attachments, ...files];
    showAttachmentDialog.value = false;
};

const isImage = (file) => {
    return file && file.type && file.type.startsWith('image/');
};

const getFilePreviewUrl = (file) => {
    if (isImage(file)) {
        return URL.createObjectURL(file);
    }
    return null;
};

const getFileIcon = (fileName) => {
    const ext = fileName.split('.').pop().toLowerCase();
    const iconMap = {
        pdf: '📄',
        doc: '📝',
        docx: '📝',
        txt: '📄',
        jpg: '🖼️',
        jpeg: '🖼️',
        png: '🖼️',
        gif: '🖼️',
        mp4: '🎥',
        mp3: '🎵',
        default: '📎'
    };
    return iconMap[ext] || iconMap.default;
};

const removeAttachment = (index) => {
    form.attachments.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submitPost = () => {
    form.post(route('community.store'), {
        onSuccess: () => {
            // Will redirect to the community index or post show page
        },
        onError: (errors) => {
            console.error('Create post errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Create Post" />
    
    <DashboardLayout title="Create New Post" subtitle="Share your thoughts, ask questions, or contribute resources">
        <div class="space-y-6">
            <!-- Back Button -->
            <div class="flex items-center space-x-4">
                <Link :href="route('community.index')" 
                      class="flex items-center space-x-2 px-4 py-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-2xl transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Community</span>
                </Link>
            </div>

            <!-- Create Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-8">
                <form @submit.prevent="submitPost">
                    <div class="space-y-6">
                        <!-- Post Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">What type of post is this?</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-for="type in [
                                    { value: 'post', label: 'General Post', icon: '💬', desc: 'Share thoughts or updates' },
                                    { value: 'question', label: 'Question', icon: '❓', desc: 'Ask for help or advice' },
                                    { value: 'resource', label: 'Resource', icon: '📚', desc: 'Share study materials' },
                                    { value: 'announcement', label: 'Announcement', icon: '📢', desc: 'Important notifications' }
                                ]" :key="type.value"
                                     :class="['p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200 hover:shadow-md', 
                                             form.type === type.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300']"
                                     @click="form.type = type.value">
                                    <div class="text-center">
                                        <div class="text-2xl mb-2">{{ type.icon }}</div>
                                        <h3 class="font-medium text-gray-800">{{ type.label }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">{{ type.desc }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Title (Optional)</label>
                            <input v-model="form.title" type="text" 
                                   class="w-full border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200" 
                                   placeholder="Give your post a catchy title...">
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Content*</label>
                            <div class="relative">
                                <textarea v-model="form.content" rows="8" required
                                          class="w-full px-4 py-3 pr-20 border border-gray-300 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 resize-none transition-all duration-200" 
                                          placeholder="What's on your mind? Share your thoughts, ask a question, or help others..."></textarea>
                                
                                <!-- Enhanced Input Tools -->
                                <div class="absolute right-3 top-3 flex items-center space-x-1">
                                    <!-- Emoji Picker Button -->
                                    <button type="button" @click="showEmojiPicker = !showEmojiPicker; showStickerPicker = false" 
                                            :class="['p-2 rounded-lg transition-colors duration-200', showEmojiPicker ? 'text-indigo-500 bg-indigo-50' : 'text-slate-400 hover:text-indigo-500']">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Sticker Picker Button -->
                                    <button type="button" @click="showStickerPicker = !showStickerPicker; showEmojiPicker = false" 
                                            :class="['p-2 rounded-lg transition-colors duration-200', showStickerPicker ? 'text-purple-500 bg-purple-50' : 'text-slate-400 hover:text-purple-500']">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Enhanced Emoji Picker -->
                                <div v-if="showEmojiPicker" class="absolute right-0 top-12 bg-white border border-slate-200 rounded-2xl shadow-2xl z-20 w-80 max-h-96 overflow-hidden">
                                    <!-- Category Tabs -->
                                    <div class="flex overflow-x-auto border-b border-slate-200 p-2 space-x-1">
                                        <button v-for="(category, key) in emojiCategories" :key="key" type="button"
                                                @click="activeEmojiCategory = key"
                                                :class="['px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap', 
                                                        activeEmojiCategory === key ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100']">
                                            {{ category.icon }} {{ category.name }}
                                        </button>
                                    </div>
                                    
                                    <!-- Emoji Grid -->
                                    <div class="p-3 max-h-64 overflow-y-auto">
                                        <div class="grid grid-cols-8 gap-1">
                                            <button v-for="emoji in emojiCategories[activeEmojiCategory].emojis" :key="emoji" type="button"
                                                    @click="addEmoji(emoji)"
                                                    class="p-2 hover:bg-slate-100 rounded-lg text-lg transition-all duration-200 hover:scale-110">
                                                {{ emoji }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Enhanced Sticker Picker -->
                                <div v-if="showStickerPicker" class="absolute right-0 top-12 bg-white border border-slate-200 rounded-2xl shadow-2xl z-20 w-80 max-h-96 overflow-hidden">
                                    <!-- Category Tabs -->
                                    <div class="flex overflow-x-auto border-b border-slate-200 p-2 space-x-1">
                                        <button v-for="(category, key) in stickerCategories" :key="key" type="button"
                                                @click="activeStickerCategory = key"
                                                :class="['px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap', 
                                                        activeStickerCategory === key ? 'bg-purple-100 text-purple-700' : 'text-slate-600 hover:bg-slate-100']">
                                            {{ category.icon }} {{ category.name }}
                                        </button>
                                    </div>
                                    
                                    <!-- Sticker Grid -->
                                    <div class="p-3 max-h-64 overflow-y-auto">
                                        <div class="grid grid-cols-4 gap-2">
                                            <button v-for="sticker in stickerCategories[activeStickerCategory].stickers" :key="sticker.name" type="button"
                                                    @click="addSticker(sticker)"
                                                    class="p-3 hover:bg-slate-100 rounded-xl transition-all duration-200 hover:scale-105 flex flex-col items-center space-y-1 group">
                                                <span class="text-2xl group-hover:scale-110 transition-transform duration-200">{{ sticker.emoji }}</span>
                                                <span class="text-xs text-slate-500 group-hover:text-slate-700 transition-colors duration-200">{{ sticker.name }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.content" class="text-red-500 text-sm mt-1">{{ form.errors.content }}</div>
                        </div>

                        <!-- Attachment Preview -->
                        <div v-if="form.attachments.length > 0" class="space-y-3">
                            <h4 class="text-sm font-medium text-gray-700">Attachments</h4>
                            <div v-for="(file, index) in form.attachments" :key="index" 
                                 class="bg-slate-50 rounded-xl p-3 hover:bg-slate-100 transition-colors duration-200">
                                <div v-if="isImage(file)" class="space-y-2">
                                    <!-- Image Preview -->
                                    <div class="relative">
                                        <img :src="getFilePreviewUrl(file)" :alt="file.name" 
                                             class="w-full max-h-48 object-cover rounded-lg">
                                        <button type="button" @click="removeAttachment(index)" 
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors duration-200">
                                            ×
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-slate-800">{{ file.name }}</p>
                                            <p class="text-xs text-slate-500">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex items-center justify-between">
                                    <!-- File Preview -->
                                    <div class="flex items-center space-x-3">
                                        <div class="text-2xl">
                                            {{ getFileIcon(file.name) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-800">{{ file.name }}</p>
                                            <p class="text-xs text-slate-500">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeAttachment(index)" 
                                            class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Tags (Optional)</label>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span v-for="(tag, index) in form.tags" :key="index" 
                                      class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-700">
                                    #{{ tag }}
                                    <button type="button" @click="removeTag(index)" 
                                            class="ml-2 text-indigo-500 hover:text-indigo-700">×</button>
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                <input v-model="tagInput" type="text" 
                                       class="flex-1 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200" 
                                       placeholder="Add a tag..."
                                       @keypress.enter.prevent="addTag">
                                <button type="button" @click="addTag" 
                                        class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-xl hover:bg-indigo-200 transition-colors duration-200">
                                    Add
                                </button>
                            </div>
                        </div>

                        <!-- Anonymous Option -->
                        <div class="flex items-center space-x-3">
                            <input v-model="form.is_anonymous" type="checkbox" id="anonymous" 
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="anonymous" class="text-sm text-gray-700 font-medium">
                                Post anonymously
                            </label>
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

                        <!-- Enhanced Action Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <div class="flex items-center space-x-4">
                                <!-- Enhanced Attachment Button -->
                                <div class="relative">
                                    <button type="button" @click="showAttachmentDialog = true" 
                                            class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 border border-slate-300 hover:border-indigo-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span>Add Files</span>
                                        <span v-if="form.attachments.length > 0" class="bg-indigo-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center ml-1">{{ form.attachments.length }}</span>
                                    </button>
                                </div>
                                
                                <!-- Quick Emoji Actions -->
                                <div class="flex items-center space-x-1">
                                    <button type="button" @click="addEmoji('👍')" 
                                            class="p-2 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                                        👍
                                    </button>
                                    <button type="button" @click="addEmoji('❤️')" 
                                            class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all duration-200">
                                        ❤️
                                    </button>
                                    <button type="button" @click="addEmoji('🎉')" 
                                            class="p-2 text-slate-400 hover:text-yellow-500 hover:bg-yellow-50 rounded-lg transition-all duration-200">
                                        🎉
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <Link :href="route('community.index')" 
                                      class="px-6 py-3 border border-gray-300 rounded-2xl text-gray-700 hover:bg-gray-50 font-medium transition-all duration-200">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing || !form.content.trim()" 
                                        class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                    <span v-if="form.processing" class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <span>Creating...</span>
                                    </span>
                                    <span v-else>Create Post 🚀</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Attachment Dialog -->
        <div v-if="showAttachmentDialog" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showAttachmentDialog = false">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm" @click="showAttachmentDialog = false"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900 flex items-center space-x-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span>Attach Files & Media</span>
                            </h3>
                            <button type="button" @click="showAttachmentDialog = false" 
                                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Upload Area -->
                            <div class="col-span-2">
                                <div class="border-2 border-dashed border-indigo-300 rounded-2xl p-8 hover:border-indigo-400 hover:bg-indigo-25 transition-all duration-200 bg-white">
                                    <input type="file" multiple @change="handleFileUpload" 
                                           class="hidden" id="file-upload" 
                                           accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.mp4,.mp3,.ppt,.pptx,.xls,.xlsx,.zip,.rar">
                                    <label for="file-upload" class="cursor-pointer block text-center">
                                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl mx-auto mb-4">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Share Your Files</h4>
                                        <p class="text-gray-600 font-medium">Drag files here or click to browse</p>
                                        <p class="text-sm text-gray-500 mt-2">Documents, images, videos and more</p>
                                        <p class="text-xs text-gray-400 mt-1">Max file size: 10MB per file</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Selected: {{ form.attachments.length }} files
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" @click="showAttachmentDialog = false" 
                                    class="px-6 py-2 border border-gray-300 rounded-2xl text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                Cancel
                            </button>
                            <button type="button" @click="showAttachmentDialog = false" 
                                    class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200">
                                Done
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>