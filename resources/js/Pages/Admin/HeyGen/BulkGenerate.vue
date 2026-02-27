<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    subjects: Array,
    lessonsNeedingVideos: Array,
    lessonsGenerating: Array,
    stats: Object,
    heygenConfigured: Boolean,
    avatars: Array,
    voices: Array,
    credits: Object,
});

// State
const selectedLessons = ref([]);
const lessonScripts = ref({});
const defaultAvatar = ref(null);
const defaultVoice = ref(null);
const lessonAvatars = ref({});
const lessonVoices = ref({});
const useDefaultForAll = ref(true);
const generating = ref(false);
const error = ref(null);
const success = ref(null);

// Filters
const subjectFilter = ref('');
const statusFilter = ref('all');
const searchQuery = ref('');
const avatarSearch = ref('');
const voiceSearch = ref('');
const voiceLanguageFilter = ref('');

// Polling
let statusPollInterval = null;
const generationStatuses = ref({});

// Computed
const allLessons = computed(() => {
    const lessons = [];
    props.subjects.forEach(subject => {
        subject.topics?.forEach(topic => {
            topic.lessons?.forEach(lesson => {
                lessons.push({
                    ...lesson,
                    topic_name: topic.name,
                    subject_name: subject.name,
                    subject_id: subject.id,
                });
            });
        });
    });
    return lessons;
});

const filteredLessons = computed(() => {
    let filtered = allLessons.value;

    if (subjectFilter.value) {
        filtered = filtered.filter(l => l.subject_id === parseInt(subjectFilter.value));
    }

    if (statusFilter.value === 'no_video') {
        filtered = filtered.filter(l => !l.video_path);
    } else if (statusFilter.value === 'has_video') {
        filtered = filtered.filter(l => l.video_path);
    } else if (statusFilter.value === 'generating') {
        filtered = filtered.filter(l => ['pending', 'processing'].includes(l.heygen_status));
    } else if (statusFilter.value === 'failed') {
        filtered = filtered.filter(l => l.heygen_status === 'failed');
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(l =>
            l.title.toLowerCase().includes(query) ||
            l.topic_name?.toLowerCase().includes(query) ||
            l.subject_name?.toLowerCase().includes(query)
        );
    }

    return filtered;
});

const filteredAvatars = computed(() => {
    let filtered = props.avatars || [];
    if (avatarSearch.value) {
        const search = avatarSearch.value.toLowerCase();
        filtered = filtered.filter(a =>
            a.avatar_name?.toLowerCase().includes(search) ||
            a.avatar_id?.toLowerCase().includes(search)
        );
    }
    return filtered.slice(0, 30);
});

const filteredVoices = computed(() => {
    let filtered = props.voices || [];
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
    return filtered.slice(0, 30);
});

const voiceLanguages = computed(() => {
    const languages = new Set((props.voices || []).map(v => v.language).filter(Boolean));
    return Array.from(languages).sort();
});

const canGenerate = computed(() => {
    if (selectedLessons.value.length === 0) return false;
    if (useDefaultForAll.value && (!defaultAvatar.value || !defaultVoice.value)) return false;

    // Check all selected lessons have scripts and settings
    for (const lessonId of selectedLessons.value) {
        const script = lessonScripts.value[lessonId];
        if (!script || script.trim().length < 10) return false;

        if (!useDefaultForAll.value) {
            if (!lessonAvatars.value[lessonId] || !lessonVoices.value[lessonId]) return false;
        }
    }

    return true;
});

const selectedCount = computed(() => selectedLessons.value.length);

const estimatedCredits = computed(() => {
    // Rough estimate: 1 credit per 30 seconds, assume average 2 min video
    return selectedLessons.value.length * 4;
});

// Methods
const toggleLesson = (lessonId) => {
    const index = selectedLessons.value.indexOf(lessonId);
    if (index === -1) {
        selectedLessons.value.push(lessonId);
        // Initialize script if not exists
        if (!lessonScripts.value[lessonId]) {
            const lesson = allLessons.value.find(l => l.id === lessonId);
            if (lesson) {
                generateScriptSuggestion(lessonId);
            }
        }
    } else {
        selectedLessons.value.splice(index, 1);
    }
};

const selectAll = () => {
    selectedLessons.value = filteredLessons.value.map(l => l.id);
    // Generate scripts for all selected
    selectedLessons.value.forEach(id => {
        if (!lessonScripts.value[id]) {
            generateScriptSuggestion(id);
        }
    });
};

const deselectAll = () => {
    selectedLessons.value = [];
};

const generateScriptSuggestion = async (lessonId) => {
    try {
        const response = await axios.post(route('admin.heygen.suggest-script'), {
            lesson_id: lessonId,
        });
        if (response.data.success) {
            lessonScripts.value[lessonId] = response.data.script;
        }
    } catch (err) {
        console.error('Error generating script:', err);
        // Fallback to basic script
        const lesson = allLessons.value.find(l => l.id === lessonId);
        if (lesson) {
            lessonScripts.value[lessonId] = `Welcome to this lesson on ${lesson.title}. ${lesson.description || ''} Let's begin!`;
        }
    }
};

const generateBulk = async () => {
    if (!canGenerate.value) return;

    generating.value = true;
    error.value = null;
    success.value = null;

    const lessons = selectedLessons.value.map(lessonId => ({
        id: lessonId,
        script: lessonScripts.value[lessonId],
        avatar_id: useDefaultForAll.value ? defaultAvatar.value : lessonAvatars.value[lessonId],
        voice_id: useDefaultForAll.value ? defaultVoice.value : lessonVoices.value[lessonId],
    }));

    try {
        const response = await axios.post(route('admin.heygen.generate'), { lessons });

        if (response.data.success) {
            success.value = response.data.message;
            startStatusPolling();
            // Clear selection
            selectedLessons.value = [];
        } else {
            error.value = response.data.errors?.join(', ') || 'Failed to queue videos';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to generate videos';
    } finally {
        generating.value = false;
    }
};

const startStatusPolling = () => {
    if (statusPollInterval) {
        clearInterval(statusPollInterval);
    }

    pollStatuses();
    statusPollInterval = setInterval(pollStatuses, 5000);
};

const pollStatuses = async () => {
    const generatingIds = allLessons.value
        .filter(l => ['pending', 'processing'].includes(l.heygen_status))
        .map(l => l.id);

    if (generatingIds.length === 0) {
        if (statusPollInterval) {
            clearInterval(statusPollInterval);
            statusPollInterval = null;
        }
        return;
    }

    try {
        const response = await axios.post(route('admin.heygen.status'), {
            lesson_ids: generatingIds,
        });

        if (response.data.success) {
            generationStatuses.value = response.data.lessons;

            // Update local lesson data
            Object.entries(response.data.lessons).forEach(([id, status]) => {
                const lesson = allLessons.value.find(l => l.id === parseInt(id));
                if (lesson) {
                    lesson.heygen_status = status.heygen_status;
                    lesson.video_path = status.video_path;
                }
            });
        }
    } catch (err) {
        console.error('Error polling statuses:', err);
    }
};

const cancelAll = async () => {
    const generatingIds = allLessons.value
        .filter(l => ['pending', 'processing'].includes(l.heygen_status))
        .map(l => l.id);

    if (generatingIds.length === 0) return;

    try {
        const response = await axios.post(route('admin.heygen.cancel-bulk'), {
            lesson_ids: generatingIds,
        });

        if (response.data.success) {
            success.value = response.data.message;
            // Update local statuses
            generatingIds.forEach(id => {
                const lesson = allLessons.value.find(l => l.id === id);
                if (lesson) {
                    lesson.heygen_status = 'failed';
                }
            });
        }
    } catch (err) {
        error.value = 'Failed to cancel generations';
    }
};

const retryFailed = async () => {
    const failedIds = allLessons.value
        .filter(l => l.heygen_status === 'failed')
        .map(l => l.id);

    if (failedIds.length === 0) return;

    try {
        const response = await axios.post(route('admin.heygen.retry-failed'), {
            lesson_ids: failedIds,
        });

        if (response.data.success) {
            success.value = response.data.message;
            startStatusPolling();
        }
    } catch (err) {
        error.value = 'Failed to retry generations';
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'text-green-600 bg-green-100';
        case 'processing': case 'pending': return 'text-blue-600 bg-blue-100';
        case 'failed': return 'text-red-600 bg-red-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'completed': return 'Completed';
        case 'processing': return 'Processing';
        case 'pending': return 'Queued';
        case 'failed': return 'Failed';
        default: return 'No Video';
    }
};

onMounted(() => {
    // Start polling if there are generating lessons
    if (props.lessonsGenerating?.length > 0) {
        startStatusPolling();
    }
});

onUnmounted(() => {
    if (statusPollInterval) {
        clearInterval(statusPollInterval);
    }
});
</script>

<template>
    <Head title="Bulk AI Video Generation" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Bulk AI Video Generation</h2>
                    <p class="text-sm text-gray-500 mt-1">Generate AI avatar videos for multiple lessons at once</p>
                </div>
                <div v-if="credits" class="text-right">
                    <p class="text-sm text-gray-500">HeyGen Credits</p>
                    <p class="text-2xl font-bold text-purple-600">{{ credits.remaining_quota || 'N/A' }}</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Not Configured Warning -->
                <div v-if="!heygenConfigured" class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <h4 class="font-semibold text-amber-800">HeyGen API Not Configured</h4>
                            <p class="text-sm text-amber-700 mt-1">
                                Add your HeyGen API key to <code class="bg-amber-100 px-2 py-0.5 rounded">.env</code>:
                                <code class="bg-amber-100 px-2 py-0.5 rounded block mt-1">HEYGEN_API_KEY=your_api_key</code>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stats.total_lessons }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">With Video</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.lessons_with_video }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">No Video</p>
                        <p class="text-2xl font-bold text-amber-600">{{ stats.lessons_without_video }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Queued</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats.heygen_pending }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Processing</p>
                        <p class="text-2xl font-bold text-purple-600">{{ stats.heygen_processing }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.heygen_completed }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Failed</p>
                        <p class="text-2xl font-bold text-red-600">{{ stats.heygen_failed }}</p>
                    </div>
                </div>

                <!-- Messages -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-red-700">{{ error }}</p>
                    </div>
                </div>

                <div v-if="success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-green-700">{{ success }}</p>
                    </div>
                </div>

                <div v-if="heygenConfigured" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Lesson Selection -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Filters -->
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search lessons..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Subject</label>
                                    <select v-model="subjectFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">All Subjects</option>
                                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                    <select v-model="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="all">All Lessons</option>
                                        <option value="no_video">No Video</option>
                                        <option value="has_video">Has Video</option>
                                        <option value="generating">Generating</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="flex items-center justify-between bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center space-x-4">
                                <button @click="selectAll" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                    Select All ({{ filteredLessons.length }})
                                </button>
                                <button @click="deselectAll" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                    Deselect All
                                </button>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">
                                    {{ selectedCount }} selected
                                </span>
                                <button
                                    v-if="stats.heygen_failed > 0"
                                    @click="retryFailed"
                                    class="px-3 py-1 text-sm bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200"
                                >
                                    Retry Failed
                                </button>
                                <button
                                    v-if="stats.heygen_pending + stats.heygen_processing > 0"
                                    @click="cancelAll"
                                    class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                                >
                                    Cancel All
                                </button>
                            </div>
                        </div>

                        <!-- Lessons List -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="max-h-[500px] overflow-y-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                                <input
                                                    type="checkbox"
                                                    :checked="selectedLessons.length === filteredLessons.length && filteredLessons.length > 0"
                                                    @change="selectedLessons.length === filteredLessons.length ? deselectAll() : selectAll()"
                                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                                />
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lesson</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject / Topic</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr
                                            v-for="lesson in filteredLessons"
                                            :key="lesson.id"
                                            :class="[
                                                'hover:bg-gray-50 cursor-pointer transition-colors',
                                                selectedLessons.includes(lesson.id) ? 'bg-purple-50' : ''
                                            ]"
                                            @click="toggleLesson(lesson.id)"
                                        >
                                            <td class="px-4 py-3">
                                                <input
                                                    type="checkbox"
                                                    :checked="selectedLessons.includes(lesson.id)"
                                                    @click.stop
                                                    @change="toggleLesson(lesson.id)"
                                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-gray-800 text-sm">{{ lesson.title }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="text-xs text-gray-500">{{ lesson.subject_name }}</p>
                                                <p class="text-xs text-gray-400">{{ lesson.topic_name }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(lesson.heygen_status || (lesson.video_path ? 'completed' : 'none'))]">
                                                    {{ lesson.video_path ? 'Has Video' : getStatusText(lesson.heygen_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div v-if="filteredLessons.length === 0" class="p-8 text-center text-gray-500">
                                    No lessons found matching your filters.
                                </div>
                            </div>
                        </div>

                        <!-- Script Editor for Selected Lessons -->
                        <div v-if="selectedLessons.length > 0" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <h3 class="font-semibold text-gray-800 mb-4">Scripts for Selected Lessons</h3>
                            <div class="space-y-4 max-h-[400px] overflow-y-auto">
                                <div v-for="lessonId in selectedLessons" :key="lessonId" class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-medium text-gray-700 text-sm">
                                            {{ allLessons.find(l => l.id === lessonId)?.title }}
                                        </p>
                                        <button
                                            @click="generateScriptSuggestion(lessonId)"
                                            class="text-xs text-purple-600 hover:text-purple-800"
                                        >
                                            Regenerate Script
                                        </button>
                                    </div>
                                    <textarea
                                        v-model="lessonScripts[lessonId]"
                                        rows="3"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                        placeholder="Enter the script for this lesson..."
                                    ></textarea>
                                    <p class="text-xs text-gray-400 mt-1">{{ (lessonScripts[lessonId] || '').length }} characters</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings -->
                    <div class="space-y-6">
                        <!-- Default Settings -->
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <h3 class="font-semibold text-gray-800 mb-4">Avatar & Voice Settings</h3>

                            <div class="mb-4">
                                <label class="flex items-center space-x-2">
                                    <input
                                        v-model="useDefaultForAll"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                    />
                                    <span class="text-sm text-gray-700">Use same avatar & voice for all lessons</span>
                                </label>
                            </div>

                            <!-- Avatar Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Avatar</label>
                                <input
                                    v-model="avatarSearch"
                                    type="text"
                                    placeholder="Search avatars..."
                                    class="w-full px-3 py-2 mb-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                                <div class="grid grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                                    <button
                                        v-for="avatar in filteredAvatars"
                                        :key="avatar.avatar_id"
                                        @click="defaultAvatar = avatar.avatar_id"
                                        :class="[
                                            'p-2 border-2 rounded-lg transition-all duration-200',
                                            defaultAvatar === avatar.avatar_id
                                                ? 'border-purple-500 bg-purple-50'
                                                : 'border-gray-200 hover:border-purple-300'
                                        ]"
                                    >
                                        <img
                                            v-if="avatar.preview_image_url"
                                            :src="avatar.preview_image_url"
                                            :alt="avatar.avatar_name"
                                            class="w-12 h-12 object-cover rounded-lg mx-auto mb-1"
                                        />
                                        <div v-else class="w-12 h-12 bg-gray-200 rounded-lg mx-auto mb-1 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-600 truncate text-center">{{ avatar.avatar_name || avatar.avatar_id }}</p>
                                    </button>
                                </div>
                            </div>

                            <!-- Voice Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Voice</label>
                                <div class="flex space-x-2 mb-2">
                                    <input
                                        v-model="voiceSearch"
                                        type="text"
                                        placeholder="Search..."
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    />
                                    <select
                                        v-model="voiceLanguageFilter"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    >
                                        <option value="">All</option>
                                        <option v-for="lang in voiceLanguages" :key="lang" :value="lang">{{ lang }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1 max-h-48 overflow-y-auto">
                                    <button
                                        v-for="voice in filteredVoices"
                                        :key="voice.voice_id"
                                        @click="defaultVoice = voice.voice_id"
                                        :class="[
                                            'w-full p-2 border-2 rounded-lg text-left transition-all duration-200',
                                            defaultVoice === voice.voice_id
                                                ? 'border-purple-500 bg-purple-50'
                                                : 'border-gray-200 hover:border-purple-300'
                                        ]"
                                    >
                                        <p class="font-medium text-gray-700 text-sm">{{ voice.voice_name || voice.voice_id }}</p>
                                        <p class="text-xs text-gray-500">{{ voice.language }} - {{ voice.gender || 'Unknown' }}</p>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <div class="mb-4 text-sm text-gray-600">
                                <p>Selected: <span class="font-semibold text-purple-600">{{ selectedCount }}</span> lessons</p>
                                <p>Est. credits: <span class="font-semibold text-purple-600">~{{ estimatedCredits }}</span></p>
                            </div>

                            <button
                                @click="generateBulk"
                                :disabled="!canGenerate || generating"
                                :class="[
                                    'w-full py-3 px-4 font-semibold rounded-xl transition-all duration-200',
                                    canGenerate && !generating
                                        ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white hover:from-purple-700 hover:to-indigo-700 shadow-lg hover:shadow-xl'
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]"
                            >
                                <span v-if="generating" class="flex items-center justify-center space-x-2">
                                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>Queueing...</span>
                                </span>
                                <span v-else>Generate {{ selectedCount }} Videos</span>
                            </button>

                            <p v-if="!canGenerate && selectedLessons.length > 0" class="mt-2 text-xs text-amber-600">
                                Please ensure all selected lessons have scripts and avatar/voice settings.
                            </p>
                        </div>

                        <!-- Currently Generating -->
                        <div v-if="lessonsGenerating.length > 0" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <h3 class="font-semibold text-gray-800 mb-4">Currently Generating</h3>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <div
                                    v-for="lesson in lessonsGenerating"
                                    :key="lesson.id"
                                    class="flex items-center space-x-3 p-2 bg-blue-50 rounded-lg"
                                >
                                    <svg class="w-4 h-4 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <p class="text-sm text-blue-800 truncate flex-1">{{ lesson.title }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
