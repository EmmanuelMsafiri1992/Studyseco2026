<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    post: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };
const showComments = ref(true);
const showDeleteModal = ref(false);

const commentForm = useForm({
    content: '',
    parent_id: null,
    attachments: []
});

const showEmojiPicker = ref(false);
const showAttachmentDialog = ref(false);
const isTyping = ref(false);
const typingTimeout = ref(null);

const replyForms = ref({});

// Computed property to check if user can edit or delete the post
const canEditOrDelete = computed(() => {
    return user.id === props.post.user_id || user.role === 'admin';
});

const deleteForm = useForm({});

const confirmDelete = () => {
    deleteForm.delete(route('community.destroy', props.post.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};

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

const toggleReaction = async (type = 'like') => {
    try {
        const response = await axios.post(route('community.react', props.post.id), { type });
        
        // Update the post data with the server response
        if (response.data.success) {
            // Update reaction counts
            props.post.likes_count = response.data.likes_count;
            
            // Update all reaction states
            props.post.user_has_liked = response.data.user_has_liked;
            props.post.user_has_love = response.data.user_has_love;
            props.post.user_has_helpful = response.data.user_has_helpful;
            props.post.user_has_funny = response.data.user_has_funny;
            props.post.user_has_solved = response.data.user_has_solved;
            props.post.user_reactions = response.data.user_reactions;
            
            // Show visual feedback
            showReactionFeedback(type, response.data.has_reaction);
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
        showErrorToast('Failed to react to post. Please try again.');
    }
};

// Visual feedback for reactions
const showReactionFeedback = (type, hasReacted) => {
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
    
    showSuccessToast(message);
};

const showErrorToast = (message) => {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 2000);
};

const submitComment = async () => {
    commentForm.post(route('community.comment', props.post.id), {
        onSuccess: () => {
            commentForm.reset();
            clearDraft();
            showSuccessToast('Comment posted! 🎉');
            // Page will automatically reload to show new comment due to back() response
        },
        onError: (errors) => {
            console.error('Error posting comment:', errors);
        }
    });
};

const submitReply = async (commentId) => {
    const form = replyForms.value[commentId];
    if (!form || !form.content) return;
    
    const replyForm = useForm({
        content: form.content,
        parent_id: commentId
    });
    
    replyForm.post(route('community.comment', props.post.id), {
        onSuccess: () => {
            replyForms.value[commentId] = { content: '', show: false };
            // Page will automatically reload to show new reply due to back() response
        },
        onError: (errors) => {
            console.error('Error posting reply:', errors);
        }
    });
};

const showReplyForm = (commentId) => {
    if (!replyForms.value[commentId]) {
        replyForms.value[commentId] = { content: '', show: false };
    }
    replyForms.value[commentId].show = !replyForms.value[commentId].show;
};

const markAsSolution = (comment) => {
    const form = useForm({});
    form.post(route('community.markSolution', comment.id), {
        onSuccess: () => {
            // Page will automatically reload to show solution status due to back() response
        },
        onError: (errors) => {
            console.error('Error marking solution:', errors);
        }
    });
};

// Enhanced Emoji and Sticker System
const showStickerPicker = ref(false);
const activeEmojiCategory = ref('smileys');
const activeStickerCategory = ref('study');

// Comprehensive emoji collection with categories
const emojiCategories = {
    smileys: {
        name: 'Smileys & Emotion',
        icon: '😀',
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🥸', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤐', '🥱', '😴', '😪', '😵', '🤤', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥴', '😮', '😯', '😲', '😧', '🥲', '😦', '😐', '😑', '😶', '😏', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕']
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
        emojis: ['🎃', '🎄', '🎆', '🎇', '🧨', '✨', '🎈', '🎉', '🎊', '🎋', '🎍', '🎎', '🎏', '🎐', '🎑', '🧧', '🎀', '🎁', '🎗️', '🎟️', '🎫', '🎖️', '🏆', '🏅', '🥇', '🥈', '🥉', '⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🪃', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛷', '⛸️', '🥌', '🎿', '⛷️', '🏂', '🪂', '🎯', '🪄', '🎱', '🔮', '🪅', '🎰', '🎲', '🧩', '🎮', '🕹️', '🎳']
    },
    symbols: {
        name: 'Symbols',
        icon: '❤️',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '♠️', '♥️', '♦️', '♣️', '♟️', '🔇', '🔈', '🔉', '🔊', '📢', '📣', '📯', '🔔', '🔕', '🎼', '🎵', '🎶', '🚭', '🚯', '🚱', '🚷', '📵', '🔞', '☢️', '☣️', '⬆️', '↗️', '➡️', '↘️', '⬇️', '↙️', '⬅️', '↖️', '↕️', '↔️', '↩️', '↪️', '⤴️', '⤵️', '🔃', '🔄', '🔙', '🔚', '🔛', '🔜', '🔝', '🛐', '⚛️', '🕉️', '✡️', '☸️', '☯️', '✝️', '☦️', '☪️', '☮️', '🕎', '🔯', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '⛎', '🔀', '🔁', '🔂', '▶️', '⏩', '⏭️', '⏯️', '◀️', '⏪', '⏮️', '🔼', '⏫', '🔽', '⏬', '⏸️', '⏹️', '⏺️', '⏏️', '🎦']
    }
};

// Sticker collections for enhanced interaction
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
    mood: {
        name: 'Mood & Feelings',
        icon: '😊',
        stickers: [
            { emoji: '😊', name: 'Happy' },
            { emoji: '😂', name: 'Laughing' },
            { emoji: '🤣', name: 'Rolling' },
            { emoji: '😍', name: 'Love' },
            { emoji: '🥰', name: 'Loving' },
            { emoji: '😘', name: 'Kiss' },
            { emoji: '🤔', name: 'Thinking' },
            { emoji: '🧐', name: 'Analyzing' },
            { emoji: '😎', name: 'Cool' },
            { emoji: '🤩', name: 'Amazed' },
            { emoji: '😴', name: 'Sleeping' },
            { emoji: '🤯', name: 'Mind Blown' },
            { emoji: '😵', name: 'Dizzy' },
            { emoji: '🤪', name: 'Crazy' },
            { emoji: '🥳', name: 'Celebrating' },
            { emoji: '😇', name: 'Angel' },
            { emoji: '🤓', name: 'Nerdy' },
            { emoji: '😏', name: 'Smirking' },
            { emoji: '😌', name: 'Peaceful' },
            { emoji: '🥺', name: 'Pleading' },
            { emoji: '😭', name: 'Crying' },
            { emoji: '😅', name: 'Nervous' },
            { emoji: '😰', name: 'Worried' },
            { emoji: '🤗', name: 'Hugging' }
        ]
    },
    tech: {
        name: 'Tech & Code',
        icon: '💻',
        stickers: [
            { emoji: '💻', name: 'Laptop' },
            { emoji: '🖥️', name: 'Desktop' },
            { emoji: '⌨️', name: 'Keyboard' },
            { emoji: '🖱️', name: 'Mouse' },
            { emoji: '🖨️', name: 'Printer' },
            { emoji: '💾', name: 'Floppy Disk' },
            { emoji: '💿', name: 'CD' },
            { emoji: '📱', name: 'Phone' },
            { emoji: '📞', name: 'Telephone' },
            { emoji: '☎️', name: 'Old Phone' },
            { emoji: '📟', name: 'Pager' },
            { emoji: '📠', name: 'Fax' },
            { emoji: '📡', name: 'Satellite' },
            { emoji: '🔌', name: 'Plug' },
            { emoji: '🔋', name: 'Battery' },
            { emoji: '💡', name: 'Bulb' },
            { emoji: '🕹️', name: 'Joystick' },
            { emoji: '📺', name: 'TV' },
            { emoji: '📻', name: 'Radio' },
            { emoji: '📷', name: 'Camera' },
            { emoji: '📹', name: 'Video Camera' },
            { emoji: '🎥', name: 'Movie Camera' },
            { emoji: '🎬', name: 'Clapper' },
            { emoji: '🎮', name: 'Game Controller' }
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
    commentForm.content += emoji;
    showEmojiPicker.value = false;
};

const addSticker = (sticker) => {
    commentForm.content += ` ${sticker.emoji} `;
    showStickerPicker.value = false;
};

// File attachment handling
const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    commentForm.attachments = [...commentForm.attachments, ...files];
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
    commentForm.attachments.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Comment reactions
const toggleCommentReaction = (comment, emoji) => {
    // This would require additional backend setup for comment reactions
    // For now, we'll add visual feedback
    comment.user_reaction = comment.user_reaction === emoji ? null : emoji;
};

// Typing indicators and auto-save
const handleTyping = () => {
    isTyping.value = true;
    
    // Auto-save to localStorage
    localStorage.setItem(`comment_draft_${props.post.id}`, commentForm.content);
    
    // Clear existing timeout
    if (typingTimeout.value) {
        clearTimeout(typingTimeout.value);
    }
    
    // Set new timeout to stop typing indicator
    typingTimeout.value = setTimeout(() => {
        isTyping.value = false;
    }, 2000);
};

// Load draft from localStorage on component mount
const loadDraft = () => {
    const draft = localStorage.getItem(`comment_draft_${props.post.id}`);
    if (draft) {
        commentForm.content = draft;
    }
};

// Clear draft when comment is successfully posted
const clearDraft = () => {
    localStorage.removeItem(`comment_draft_${props.post.id}`);
};

// Load draft when component mounts
onMounted(() => {
    loadDraft();
});

// Activity feedback
const showSuccessToast = (message) => {
    // Simple toast notification (you could use a proper toast library)
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
    <Head :title="post.title || 'Community Post'" />
    
    <DashboardLayout :title="post.title || 'Community Post'" subtitle="Join the discussion">
        <div class="space-y-6">
            <!-- Back to Community Button -->
            <div class="flex items-center space-x-4">
                <Link :href="route('community.index')" 
                      class="flex items-center space-x-2 px-4 py-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-2xl transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Community</span>
                </Link>
            </div>

            <!-- Main Post -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
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
                        
                        <div v-if="canEditOrDelete" class="flex items-center space-x-2">
                            <Link :href="route('community.edit', post.id)" 
                                  class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </Link>
                            <button @click="showDeleteModal = true" 
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Post Title -->
                    <h1 v-if="post.title" class="text-2xl font-bold text-slate-800 mt-4">{{ post.title }}</h1>
                </div>

                <!-- Post Content -->
                <div class="p-6">
                    <div class="prose max-w-none text-slate-700 mb-6">
                        <p class="text-lg leading-relaxed">{{ post.content }}</p>
                    </div>

                    <!-- Tags -->
                    <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-2 mb-6">
                        <span v-for="tag in post.tags" :key="tag" class="px-3 py-1 text-sm bg-slate-100 text-slate-600 rounded-full">
                            #{{ tag }}
                        </span>
                    </div>

                    <!-- Poll -->
                    <div v-if="post.poll_options && post.poll_options.length > 0" class="bg-slate-50 rounded-2xl p-6 mb-6">
                        <h4 class="font-semibold text-slate-800 mb-4">Poll</h4>
                        <div class="space-y-3">
                            <div v-for="(option, index) in post.poll_options" :key="index" class="flex items-center justify-between p-4 bg-white rounded-xl border hover:border-indigo-300 cursor-pointer transition-all duration-200">
                                <span class="font-medium">{{ option }}</span>
                                <span class="text-sm text-slate-500">
                                    {{ post.poll_votes && post.poll_votes[index] ? post.poll_votes[index].length : 0 }} votes
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Shared Resources -->
                    <div v-if="post.sharedResources && post.sharedResources.length > 0" class="bg-slate-50 rounded-2xl p-6 mb-6">
                        <h4 class="font-semibold text-slate-800 mb-4">Shared Resources</h4>
                        <div class="space-y-3">
                            <div v-for="resource in post.sharedResources" :key="resource.id" class="flex items-center justify-between p-4 bg-white rounded-xl border">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-slate-800">{{ resource.title }}</p>
                                        <p v-if="resource.description" class="text-sm text-slate-600">{{ resource.description }}</p>
                                    </div>
                                </div>
                                <Link :href="route('community.downloadResource', resource.id)" 
                                      class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors duration-200">
                                    Download
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post Actions -->
                <div class="px-6 py-4 border-t border-slate-200/50 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-6">
                            <!-- Enhanced Like Button -->
                            <button @click="toggleReaction('like')" 
                                    :class="['flex items-center space-x-2 px-4 py-2 rounded-xl transition-all duration-200 hover:scale-105 transform', 
                                            post.user_has_liked ? 'bg-red-100 text-red-600 shadow-md scale-105' : 'text-slate-500 hover:bg-red-50 hover:text-red-500']">
                                <svg class="w-5 h-5 transition-all duration-200" 
                                     :class="post.user_has_liked ? 'fill-current scale-110' : ''" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-sm font-medium">{{ post.likes_count || 0 }}</span>
                            </button>
                            
                            <!-- Additional Reaction Buttons -->
                            <div class="flex items-center space-x-2">
                                <button @click="toggleReaction('helpful')" 
                                        :class="['p-2 rounded-xl transition-all duration-200 hover:scale-110 transform', 
                                                post.user_has_helpful ? 'bg-yellow-100 text-yellow-600 scale-110' : 'text-slate-400 hover:bg-yellow-50 hover:text-yellow-500']">
                                    <span class="text-lg">💯</span>
                                </button>
                                <button @click="toggleReaction('funny')" 
                                        :class="['p-2 rounded-xl transition-all duration-200 hover:scale-110 transform', 
                                                post.user_has_funny ? 'bg-blue-100 text-blue-600 scale-110' : 'text-slate-400 hover:bg-blue-50 hover:text-blue-500']">
                                    <span class="text-lg">😂</span>
                                </button>
                                <button @click="toggleReaction('love')" 
                                        :class="['p-2 rounded-xl transition-all duration-200 hover:scale-110 transform', 
                                                post.user_has_love ? 'bg-pink-100 text-pink-600 scale-110' : 'text-slate-400 hover:bg-pink-50 hover:text-pink-500']">
                                    <span class="text-lg">💖</span>
                                </button>
                                <button v-if="post.type === 'question'" @click="toggleReaction('solved')" 
                                        :class="['p-2 rounded-xl transition-all duration-200 hover:scale-110 transform', 
                                                post.user_has_solved ? 'bg-green-100 text-green-600 scale-110' : 'text-slate-400 hover:bg-green-50 hover:text-green-500']">
                                    <span class="text-lg">✅</span>
                                </button>
                            </div>

                            <div class="flex items-center space-x-2 px-4 py-2 rounded-xl text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                                </svg>
                                <span class="text-sm font-medium">{{ post.comments_count }} comments</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 text-sm text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span>{{ post.views_count }} views</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6">
                    Comments ({{ post.comments_count }})
                </h3>

                <!-- Comment Form -->
                <form @submit.prevent="submitComment" class="mb-8">
                    <div class="flex space-x-4">
                        <img :src="user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=ffffff`" 
                             :alt="user.name" class="w-10 h-10 rounded-full flex-shrink-0">
                        <div class="flex-1 space-y-3">
                            <div class="relative">
                                <textarea v-model="commentForm.content" @input="handleTyping" rows="3" 
                                          class="w-full px-4 py-3 pr-12 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-all duration-200 resize-none" 
                                          placeholder="Share your thoughts or ask a follow-up question..."></textarea>
                                
                                <!-- Enhanced Input Tools -->
                                <div class="absolute right-3 top-3 flex items-center space-x-1">
                                    <!-- Emoji Picker Button -->
                                    <button type="button" @click="showEmojiPicker = !showEmojiPicker; showStickerPicker = false" 
                                            :class="['p-1 rounded-lg transition-colors duration-200', showEmojiPicker ? 'text-indigo-500 bg-indigo-50' : 'text-slate-400 hover:text-indigo-500']">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Sticker Picker Button -->
                                    <button type="button" @click="showStickerPicker = !showStickerPicker; showEmojiPicker = false" 
                                            :class="['p-1 rounded-lg transition-colors duration-200', showStickerPicker ? 'text-purple-500 bg-purple-50' : 'text-slate-400 hover:text-purple-500']">
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
                            
                            <!-- Attachment Preview -->
                            <div v-if="commentForm.attachments.length > 0" class="space-y-3">
                                <div v-for="(file, index) in commentForm.attachments" :key="index" 
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
                            
                            <div class="flex items-center justify-between">
                                <!-- Enhanced Action Buttons -->
                                <div class="flex items-center space-x-4">
                                    <!-- Enhanced Attachment Button -->
                                    <div class="relative">
                                        <button type="button" @click="showAttachmentDialog = true" 
                                                class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200 border border-slate-300 hover:border-indigo-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            <span>Attach Files</span>
                                            <span v-if="commentForm.attachments.length > 0" class="bg-indigo-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center ml-1">{{ commentForm.attachments.length }}</span>
                                        </button>
                                    </div>
                                    
                                    <!-- Quick Action Buttons -->
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
                                    
                                    <!-- Draft Indicator -->
                                    <div v-if="commentForm.content" class="flex items-center space-x-1 text-xs text-slate-400">
                                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                        <span>Draft saved</span>
                                    </div>
                                </div>
                                
                                <button type="submit" :disabled="commentForm.processing || !commentForm.content.trim()" 
                                        class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                    <span v-if="commentForm.processing" class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <span>Posting...</span>
                                    </span>
                                    <span v-else>Post Comment</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Typing Indicator -->
                <div v-if="isTyping" class="flex items-center space-x-3 text-sm text-slate-500 mb-4">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    <span>{{ user.name }} is typing...</span>
                </div>

                <!-- Comments List -->
                <div v-if="post.comments && post.comments.length > 0" class="space-y-6">
                    <div v-for="comment in post.comments" :key="comment.id" class="border-l-4 border-slate-200 pl-4 transition-all duration-300 hover:border-indigo-300">
                        <!-- Main Comment -->
                        <div class="bg-slate-50 rounded-2xl p-4 hover:bg-slate-100 transition-all duration-300 hover:shadow-md">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <img :src="comment.user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name)}&background=6366f1&color=ffffff`" 
                                         :alt="comment.user.name" class="w-8 h-8 rounded-full">
                                    <div>
                                        <span class="font-medium text-slate-800">{{ comment.user.name }}</span>
                                        <span class="text-sm text-slate-500 ml-2">{{ formatTime(comment.created_at) }}</span>
                                        <span v-if="comment.is_solution" class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                            ✓ Solution
                                        </span>
                                    </div>
                                </div>
                                <div v-if="post.can_mark_solution && post.type === 'question' && !comment.is_solution" class="flex items-center space-x-2">
                                    <button @click="markAsSolution(comment)" 
                                            class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors duration-200">
                                        Mark as Solution
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-slate-700 mb-3">{{ comment.content }}</p>
                            
                            <!-- Comment Attachments -->
                            <div v-if="comment.attachments && comment.attachments.length > 0" class="mb-4 space-y-3">
                                <div v-for="(attachment, index) in comment.attachments" :key="index">
                                    <div v-if="attachment.name && (attachment.name.toLowerCase().includes('.jpg') || attachment.name.toLowerCase().includes('.jpeg') || attachment.name.toLowerCase().includes('.png') || attachment.name.toLowerCase().includes('.gif'))" 
                                         class="relative group">
                                        <!-- Image Attachment -->
                                        <img :src="`/storage/${attachment.path}`" :alt="attachment.name" 
                                             class="w-full max-h-64 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity duration-200">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all duration-200 flex items-center justify-center">
                                            <button class="opacity-0 group-hover:opacity-100 bg-white bg-opacity-80 rounded-full p-2 transition-opacity duration-200">
                                                <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="mt-2 text-xs text-slate-500">{{ attachment.name }}</div>
                                    </div>
                                    <div v-else class="flex items-center space-x-3 bg-white rounded-lg p-4 border border-slate-200 hover:border-indigo-300 hover:shadow-sm transition-all duration-200">
                                        <!-- File Attachment -->
                                        <div class="text-2xl">
                                            {{ getFileIcon(attachment.name) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-800 truncate">{{ attachment.name }}</p>
                                            <p class="text-xs text-slate-500">{{ formatFileSize(attachment.size) }}</p>
                                        </div>
                                        <button class="text-indigo-600 hover:text-indigo-800 p-2 rounded-lg hover:bg-indigo-50 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <button @click="showReplyForm(comment.id)" 
                                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-200">
                                        Reply
                                    </button>
                                    
                                    <!-- Enhanced Quick Reaction Buttons -->
                                    <div class="flex items-center space-x-1">
                                        <button v-for="emoji in ['👍', '❤️', '😂', '🔥', '💯', '🎉']" :key="emoji"
                                                @click="toggleCommentReaction(comment, emoji)"
                                                :class="['text-sm p-2 rounded-lg transition-all duration-200 hover:bg-slate-100 hover:scale-110', 
                                                        comment.user_reaction === emoji ? 'bg-indigo-100 text-indigo-600 scale-110' : 'text-slate-400 hover:text-slate-600']">
                                            {{ emoji }}
                                        </button>
                                    </div>
                                </div>
                                <span v-if="comment.replies && comment.replies.length > 0" class="text-xs text-slate-500">
                                    {{ comment.replies.length }} {{ comment.replies.length === 1 ? 'reply' : 'replies' }}
                                </span>
                            </div>

                            <!-- Reply Form -->
                            <div v-if="replyForms[comment.id]?.show" class="mt-4 pt-4 border-t border-slate-200">
                                <div class="flex space-x-3">
                                    <img :src="user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=ffffff`" 
                                         :alt="user.name" class="w-8 h-8 rounded-full flex-shrink-0">
                                    <div class="flex-1 space-y-2">
                                        <div class="relative">
                                            <textarea v-model="replyForms[comment.id].content" rows="2" 
                                                      class="w-full px-3 py-2 pr-20 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-all duration-200 resize-none text-sm" 
                                                      :placeholder="`Reply to ${comment.user.name}...`"></textarea>
                                            <!-- Reply Quick Actions -->
                                            <div class="absolute right-2 top-2 flex items-center space-x-1">
                                                <button type="button" @click="replyForms[comment.id].content += ' 👍 '" 
                                                        class="p-1 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded transition-all duration-200">
                                                    👍
                                                </button>
                                                <button type="button" @click="replyForms[comment.id].content += ' ❤️ '" 
                                                        class="p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-all duration-200">
                                                    ❤️
                                                </button>
                                                <button type="button" @click="replyForms[comment.id].content += ' 😊 '" 
                                                        class="p-1 text-slate-400 hover:text-yellow-500 hover:bg-yellow-50 rounded transition-all duration-200">
                                                    😊
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center space-x-3">
                                                <button type="button" class="flex items-center space-x-1 text-xs text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 px-2 py-1 rounded-lg transition-all duration-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    <span>Attach</span>
                                                </button>
                                                <div class="flex items-center space-x-1">
                                                    <button type="button" @click="replyForms[comment.id].content += ' 🎯 '" class="text-xs hover:scale-110 transition-transform duration-200">🎯</button>
                                                    <button type="button" @click="replyForms[comment.id].content += ' 💡 '" class="text-xs hover:scale-110 transition-transform duration-200">💡</button>
                                                    <button type="button" @click="replyForms[comment.id].content += ' 🔥 '" class="text-xs hover:scale-110 transition-transform duration-200">🔥</button>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button @click="replyForms[comment.id].show = false" 
                                                        class="px-3 py-1 text-sm text-slate-600 hover:text-slate-800 transition-colors duration-200">
                                                    Cancel
                                                </button>
                                                <button @click="submitReply(comment.id)" 
                                                        :disabled="!replyForms[comment.id]?.content?.trim()" 
                                                        class="px-4 py-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                                    Reply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Replies -->
                        <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 ml-8 space-y-3">
                            <div v-for="reply in comment.replies" :key="reply.id" class="bg-white rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center space-x-3 mb-2">
                                    <img :src="reply.user.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(reply.user.name)}&background=6366f1&color=ffffff`" 
                                         :alt="reply.user.name" class="w-7 h-7 rounded-full">
                                    <div>
                                        <span class="font-medium text-slate-800 text-sm">{{ reply.user.name }}</span>
                                        <span class="text-xs text-slate-500 ml-2">{{ formatTime(reply.created_at) }}</span>
                                    </div>
                                </div>
                                <p class="text-slate-700 text-sm">{{ reply.content }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Comments State -->
                <div v-else class="text-center py-8">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-medium text-slate-800 mb-2">No comments yet</h4>
                    <p class="text-slate-500">Be the first to join the conversation!</p>
                </div>
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
                                <span>Share Files & Media</span>
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
                                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Upload Your Files</h4>
                                        <p class="text-gray-600 font-medium">Click here or drag and drop files</p>
                                        <p class="text-sm text-gray-500 mt-2">Support for documents, images, videos, and more</p>
                                        <p class="text-xs text-gray-400 mt-1">Max file size: 10MB per file</p>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Quick Upload Options -->
                            <div class="grid grid-cols-3 gap-3 col-span-2">
                                <button type="button" onclick="document.querySelector('#file-upload').setAttribute('accept', '.jpg,.jpeg,.png,.gif'); document.querySelector('#file-upload').click()" 
                                        class="flex flex-col items-center p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-200 transition-colors duration-200">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Images</span>
                                </button>
                                
                                <button type="button" onclick="document.querySelector('#file-upload').setAttribute('accept', '.pdf,.doc,.docx,.txt'); document.querySelector('#file-upload').click()" 
                                        class="flex flex-col items-center p-4 bg-white rounded-xl border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all duration-200 group">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-200 transition-colors duration-200">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Documents</span>
                                </button>
                                
                                <button type="button" onclick="document.querySelector('#file-upload').setAttribute('accept', '.mp4,.mp3,.avi,.mov'); document.querySelector('#file-upload').click()" 
                                        class="flex flex-col items-center p-4 bg-white rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-purple-200 transition-colors duration-200">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Media</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- File Tips -->
                        <div class="bg-white rounded-xl p-4 border border-gray-200">
                            <h5 class="font-semibold text-gray-800 mb-2 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>File Sharing Tips</span>
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                                    <span>Share study materials, notes, and resources</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                                    <span>Upload screenshots for better explanations</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                                    <span>Include relevant documents and presentations</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                                    <span>All files are shared securely with the community</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Selected: {{ commentForm.attachments.length }} files
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

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showDeleteModal = false"></div>
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
                        <button type="button" @click="confirmDelete" :disabled="deleteForm.processing"
                                class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-6 py-3 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span v-if="deleteForm.processing">Deleting...</span>
                            <span v-else>Delete Post</span>
                        </button>
                        <button type="button" @click="showDeleteModal = false" 
                                class="mt-3 w-full inline-flex justify-center rounded-2xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>