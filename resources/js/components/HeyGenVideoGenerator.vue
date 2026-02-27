<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    lesson: Object,
    show: Boolean,
});

const emit = defineEmits(['close', 'generated']);

const loading = ref(false);
const generating = ref(false);
const error = ref(null);
const success = ref(null);

// Form data
const script = ref('');
const selectedAvatar = ref(null);
const selectedVoice = ref(null);

// Available options
const avatars = ref([]);
const voices = ref([]);
const credits = ref(null);
const heygenConfigured = ref(false);

// Filters
const avatarSearch = ref('');
const voiceSearch = ref('');
const voiceLanguageFilter = ref('');

// Computed filtered lists
const filteredAvatars = computed(() => {
    let filtered = avatars.value;
    if (avatarSearch.value) {
        const search = avatarSearch.value.toLowerCase();
        filtered = filtered.filter(a =>
            a.avatar_name?.toLowerCase().includes(search) ||
            a.avatar_id?.toLowerCase().includes(search)
        );
    }
    return filtered.slice(0, 20); // Limit display
});

const filteredVoices = computed(() => {
    let filtered = voices.value;
    if (voiceLanguageFilter.value) {
        filtered = filtered.filter(v => v.language === voiceLanguageFilter.value);
    }
    if (voiceSearch.value) {
        const search = voiceSearch.value.toLowerCase();
        filtered = filtered.filter(v =>
            v.voice_name?.toLowerCase().includes(search) ||
            v.voice_id?.toLowerCase().includes(search)
        );
    }
    return filtered.slice(0, 20); // Limit display
});

const voiceLanguages = computed(() => {
    const languages = new Set(voices.value.map(v => v.language).filter(Boolean));
    return Array.from(languages).sort();
});

// Generation status
const generationStatus = ref(null);
let statusPollInterval = null;

onMounted(async () => {
    await checkConfiguration();
    if (heygenConfigured.value) {
        await Promise.all([
            loadAvatars(),
            loadVoices(),
            loadCredits(),
        ]);

        // Pre-fill if lesson has existing HeyGen data
        if (props.lesson) {
            script.value = props.lesson.heygen_script || '';
            selectedAvatar.value = props.lesson.heygen_avatar_id || null;
            selectedVoice.value = props.lesson.heygen_voice_id || null;

            if (props.lesson.heygen_status === 'processing' || props.lesson.heygen_status === 'pending') {
                startStatusPolling();
            }
        }
    }
});

const checkConfiguration = async () => {
    try {
        const response = await axios.get(route('heygen.status'));
        heygenConfigured.value = response.data.configured;
    } catch (err) {
        heygenConfigured.value = false;
    }
};

const loadAvatars = async () => {
    try {
        const response = await axios.get(route('heygen.avatars'));
        avatars.value = response.data.avatars || [];
    } catch (err) {
        console.error('Error loading avatars:', err);
    }
};

const loadVoices = async () => {
    try {
        const response = await axios.get(route('heygen.voices'));
        voices.value = response.data.voices || [];
    } catch (err) {
        console.error('Error loading voices:', err);
    }
};

const loadCredits = async () => {
    try {
        const response = await axios.get(route('heygen.credits'));
        credits.value = response.data.credits;
    } catch (err) {
        console.error('Error loading credits:', err);
    }
};

const generateVideo = async () => {
    if (!script.value.trim()) {
        error.value = 'Please enter a script for the video';
        return;
    }
    if (!selectedAvatar.value) {
        error.value = 'Please select an avatar';
        return;
    }
    if (!selectedVoice.value) {
        error.value = 'Please select a voice';
        return;
    }

    generating.value = true;
    error.value = null;
    success.value = null;

    try {
        const response = await axios.post(route('heygen.generate', props.lesson.id), {
            script: script.value,
            avatar_id: selectedAvatar.value,
            voice_id: selectedVoice.value,
        });

        if (response.data.success) {
            success.value = response.data.message;
            startStatusPolling();
        } else {
            error.value = response.data.error || 'Failed to start video generation';
        }
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to generate video';
    } finally {
        generating.value = false;
    }
};

const startStatusPolling = () => {
    if (statusPollInterval) {
        clearInterval(statusPollInterval);
    }

    pollStatus();
    statusPollInterval = setInterval(pollStatus, 5000);
};

const pollStatus = async () => {
    try {
        const response = await axios.get(route('heygen.generation-status', props.lesson.id));
        generationStatus.value = response.data.heygen;

        if (response.data.heygen.status === 'completed') {
            clearInterval(statusPollInterval);
            statusPollInterval = null;
            success.value = 'Video generated successfully!';
            emit('generated', response.data);
        } else if (response.data.heygen.status === 'failed') {
            clearInterval(statusPollInterval);
            statusPollInterval = null;
            error.value = response.data.heygen.error || 'Video generation failed';
        }
    } catch (err) {
        console.error('Error polling status:', err);
    }
};

const cancelGeneration = async () => {
    try {
        await axios.post(route('heygen.cancel', props.lesson.id));
        if (statusPollInterval) {
            clearInterval(statusPollInterval);
            statusPollInterval = null;
        }
        generationStatus.value = null;
        success.value = null;
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to cancel generation';
    }
};

const retryGeneration = async () => {
    try {
        const response = await axios.post(route('heygen.retry', props.lesson.id));
        if (response.data.success) {
            success.value = response.data.message;
            startStatusPolling();
        }
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to retry generation';
    }
};

const close = () => {
    if (statusPollInterval) {
        clearInterval(statusPollInterval);
    }
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>

            <!-- Modal Panel -->
            <div class="relative inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-indigo-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Generate AI Video</h3>
                                <p class="text-sm text-white/80">Create an avatar video for "{{ lesson?.title }}"</p>
                            </div>
                        </div>
                        <button @click="close" class="p-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                    <!-- Not Configured Warning -->
                    <div v-if="!heygenConfigured" class="p-4 bg-amber-50 border border-amber-200 rounded-xl mb-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-amber-800">HeyGen API Not Configured</h4>
                                <p class="text-sm text-amber-700 mt-1">
                                    Please add your HeyGen API key to the .env file:
                                    <code class="bg-amber-100 px-2 py-0.5 rounded">HEYGEN_API_KEY=your_api_key</code>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Generation Status -->
                    <div v-if="generationStatus && (generationStatus.status === 'pending' || generationStatus.status === 'processing')"
                         class="p-4 bg-blue-50 border border-blue-200 rounded-xl mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="animate-spin">
                                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-blue-800">Video Generation in Progress</h4>
                                <p class="text-sm text-blue-600">This may take a few minutes. You can close this modal and check back later.</p>
                            </div>
                            <button @click="cancelGeneration" class="px-3 py-1 text-sm bg-white border border-blue-300 rounded-lg hover:bg-blue-50">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-xl mb-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-red-700">{{ error }}</p>
                            </div>
                            <button v-if="generationStatus?.status === 'failed'" @click="retryGeneration" class="px-3 py-1 text-sm bg-red-100 border border-red-300 rounded-lg hover:bg-red-200">
                                Retry
                            </button>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-xl mb-6">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <p class="text-green-700">{{ success }}</p>
                        </div>
                    </div>

                    <!-- Credits Display -->
                    <div v-if="credits" class="mb-6 p-4 bg-gray-50 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Remaining Credits</span>
                            <span class="text-lg font-bold text-purple-600">{{ credits.remaining_quota || 'N/A' }}</span>
                        </div>
                    </div>

                    <div v-if="heygenConfigured" class="space-y-6">
                        <!-- Script Input -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Lesson Script
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="script"
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                placeholder="Write the script that the avatar will speak. Be clear and concise for best results..."
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">{{ script.length }} characters</p>
                        </div>

                        <!-- Avatar Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Select Avatar
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="avatarSearch"
                                type="text"
                                class="w-full px-4 py-2 mb-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Search avatars..."
                            />
                            <div class="grid grid-cols-4 gap-3 max-h-48 overflow-y-auto">
                                <button
                                    v-for="avatar in filteredAvatars"
                                    :key="avatar.avatar_id"
                                    @click="selectedAvatar = avatar.avatar_id"
                                    :class="[
                                        'p-2 border-2 rounded-xl text-center transition-all duration-200',
                                        selectedAvatar === avatar.avatar_id
                                            ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200'
                                            : 'border-gray-200 hover:border-purple-300'
                                    ]"
                                >
                                    <img
                                        v-if="avatar.preview_image_url"
                                        :src="avatar.preview_image_url"
                                        :alt="avatar.avatar_name"
                                        class="w-16 h-16 object-cover rounded-lg mx-auto mb-1"
                                    />
                                    <div v-else class="w-16 h-16 bg-gray-200 rounded-lg mx-auto mb-1 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium text-gray-700 truncate">{{ avatar.avatar_name || avatar.avatar_id }}</p>
                                </button>
                            </div>
                            <p v-if="avatars.length === 0 && !loading" class="text-sm text-gray-500 text-center py-4">
                                No avatars available. Check your HeyGen account.
                            </p>
                        </div>

                        <!-- Voice Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Select Voice
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-2 mb-3">
                                <input
                                    v-model="voiceSearch"
                                    type="text"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Search voices..."
                                />
                                <select
                                    v-model="voiceLanguageFilter"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                >
                                    <option value="">All Languages</option>
                                    <option v-for="lang in voiceLanguages" :key="lang" :value="lang">{{ lang }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                                <button
                                    v-for="voice in filteredVoices"
                                    :key="voice.voice_id"
                                    @click="selectedVoice = voice.voice_id"
                                    :class="[
                                        'p-3 border-2 rounded-xl text-left transition-all duration-200',
                                        selectedVoice === voice.voice_id
                                            ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200'
                                            : 'border-gray-200 hover:border-purple-300'
                                    ]"
                                >
                                    <p class="font-medium text-gray-700 text-sm">{{ voice.voice_name || voice.voice_id }}</p>
                                    <p class="text-xs text-gray-500">{{ voice.language }} - {{ voice.gender || 'Unknown' }}</p>
                                </button>
                            </div>
                            <p v-if="voices.length === 0 && !loading" class="text-sm text-gray-500 text-center py-4">
                                No voices available. Check your HeyGen account.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <button
                        @click="close"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        v-if="heygenConfigured"
                        @click="generateVideo"
                        :disabled="generating || !script.trim() || !selectedAvatar || !selectedVoice || (generationStatus?.status === 'processing')"
                        :class="[
                            'px-6 py-2 font-semibold rounded-xl transition-all duration-200',
                            generating || !script.trim() || !selectedAvatar || !selectedVoice || (generationStatus?.status === 'processing')
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                : 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white hover:from-purple-700 hover:to-indigo-700 shadow-lg hover:shadow-xl'
                        ]"
                    >
                        <span v-if="generating" class="flex items-center space-x-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Generating...</span>
                        </span>
                        <span v-else>Generate Video</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
