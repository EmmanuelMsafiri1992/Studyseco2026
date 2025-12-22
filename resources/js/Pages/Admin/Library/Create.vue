<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    subjects: Array,
    types: Object,
    categories: Array,
    gradeLevels: Array,
    examBoards: Array,
});

const form = useForm({
    title: '',
    description: '',
    type: '',
    category: '',
    subject_id: '',
    grade_level: '',
    exam_board: '',
    year: new Date().getFullYear(),
    file: null,
    thumbnail: null,
    access_permissions: {
        roles: [],
        users: []
    },
    is_protected: false,
    protection_settings: {
        prevent_download: false,
        watermark: false,
        time_limit: null
    }
});

const filePreview = ref(null);
const thumbnailPreview = ref(null);
const newCategory = ref('');
const showNewCategory = ref(false);

// File handling
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.file = file;
        
        // Show preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                filePreview.value = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            filePreview.value = null;
        }

        // Auto-generate title from filename if empty
        if (!form.title) {
            form.title = file.name.replace(/\.[^/.]+$/, ""); // Remove extension
        }
    }
};

const handleThumbnailChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.thumbnail = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            thumbnailPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const addNewCategory = () => {
    if (newCategory.value.trim()) {
        form.category = newCategory.value.trim();
        newCategory.value = '';
        showNewCategory.value = false;
    }
};

const submit = () => {
    form.post(route('admin.library.store'), {
        onSuccess: () => {
            // Success handled by redirect
        }
    });
};

// Watch for type changes to show relevant fields
const showPastPaperFields = ref(false);
const showBookFields = ref(false);

watch(() => form.type, (newType) => {
    showPastPaperFields.value = newType === 'past_paper';
    showBookFields.value = newType === 'book';
});

const getTypeIcon = (type) => {
    const icons = {
        book: '📚',
        past_paper: '📄',
        document: '📋',
        video: '🎥',
        audio: '🎵',
        presentation: '📊'
    };
    return icons[type] || '📁';
};
</script>

<template>
    <Head title="Add New Resource" />
    
    <DashboardLayout title="Add New Resource" subtitle="Upload books, papers, and educational materials">
        <div class="max-w-4xl mx-auto">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Basic Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="text-2xl">📚</span>
                        <h3 class="text-lg font-semibold text-slate-900">Basic Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Title *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter resource title..."
                            />
                            <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter resource description..."
                            />
                            <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Type *</label>
                            <select
                                v-model="form.type"
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select type...</option>
                                <option v-for="(label, value) in types" :key="value" :value="value">
                                    {{ getTypeIcon(value) }} {{ label }}
                                </option>
                            </select>
                            <div v-if="form.errors.type" class="text-red-600 text-sm mt-1">{{ form.errors.type }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                            <select
                                v-model="form.subject_id"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select subject...</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.subject_id" class="text-red-600 text-sm mt-1">{{ form.errors.subject_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Category</label>
                            <div class="flex space-x-2">
                                <select
                                    v-if="!showNewCategory"
                                    v-model="form.category"
                                    class="flex-1 px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Select category...</option>
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>
                                <input
                                    v-else
                                    v-model="newCategory"
                                    type="text"
                                    placeholder="New category name..."
                                    class="flex-1 px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    @keyup.enter="addNewCategory"
                                />
                                <button
                                    v-if="!showNewCategory"
                                    type="button"
                                    @click="showNewCategory = true"
                                    class="px-4 py-3 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200"
                                >
                                    New
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    @click="addNewCategory"
                                    class="px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700"
                                >
                                    Add
                                </button>
                            </div>
                            <div v-if="form.errors.category" class="text-red-600 text-sm mt-1">{{ form.errors.category }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Grade Level</label>
                            <select
                                v-model="form.grade_level"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select grade level...</option>
                                <option v-for="grade in gradeLevels" :key="grade" :value="grade">
                                    Grade {{ grade }}
                                </option>
                            </select>
                            <div v-if="form.errors.grade_level" class="text-red-600 text-sm mt-1">{{ form.errors.grade_level }}</div>
                        </div>
                    </div>
                </div>

                <!-- Past Paper Specific Fields -->
                <div v-if="showPastPaperFields" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="text-2xl">📄</span>
                        <h3 class="text-lg font-semibold text-slate-900">Past Paper Details</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Year</label>
                            <input
                                v-model="form.year"
                                type="number"
                                min="2000"
                                :max="new Date().getFullYear() + 1"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <div v-if="form.errors.year" class="text-red-600 text-sm mt-1">{{ form.errors.year }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Exam Board</label>
                            <select
                                v-model="form.exam_board"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select exam board...</option>
                                <option v-for="board in examBoards" :key="board" :value="board">
                                    {{ board }}
                                </option>
                            </select>
                            <div v-if="form.errors.exam_board" class="text-red-600 text-sm mt-1">{{ form.errors.exam_board }}</div>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="text-2xl">📎</span>
                        <h3 class="text-lg font-semibold text-slate-900">File Upload</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Main File</label>
                            <input
                                type="file"
                                @change="handleFileChange"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mp3,.jpg,.jpeg,.png"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p class="text-sm text-slate-500 mt-1">Max size: 1GB. Supported formats: PDF, DOC, PPT, MP4, MP3, images</p>
                            <div v-if="form.errors.file" class="text-red-600 text-sm mt-1">{{ form.errors.file }}</div>
                            
                            <div v-if="filePreview" class="mt-4">
                                <img :src="filePreview" alt="File preview" class="max-w-xs h-32 object-cover rounded-lg border border-slate-200" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Thumbnail (Optional)</label>
                            <input
                                type="file"
                                @change="handleThumbnailChange"
                                accept="image/*"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p class="text-sm text-slate-500 mt-1">Upload a custom thumbnail image</p>
                            <div v-if="form.errors.thumbnail" class="text-red-600 text-sm mt-1">{{ form.errors.thumbnail }}</div>
                            
                            <div v-if="thumbnailPreview" class="mt-4">
                                <img :src="thumbnailPreview" alt="Thumbnail preview" class="max-w-xs h-32 object-cover rounded-lg border border-slate-200" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access & Protection Settings -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="text-2xl">🔒</span>
                        <h3 class="text-lg font-semibold text-slate-900">Access & Protection Settings</h3>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-3">
                            <input
                                v-model="form.is_protected"
                                type="checkbox"
                                id="is_protected"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="is_protected" class="text-sm font-medium text-slate-700">
                                Enable protection settings
                            </label>
                        </div>

                        <div v-if="form.is_protected" class="space-y-4 p-4 bg-slate-50 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <input
                                    v-model="form.protection_settings.prevent_download"
                                    type="checkbox"
                                    id="prevent_download"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="prevent_download" class="text-sm text-slate-700">
                                    Prevent downloads (view only)
                                </label>
                            </div>

                            <div class="flex items-center space-x-3">
                                <input
                                    v-model="form.protection_settings.watermark"
                                    type="checkbox"
                                    id="watermark"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="watermark" class="text-sm text-slate-700">
                                    Add watermark
                                </label>
                            </div>

                            <div>
                                <label for="time_limit" class="block text-sm font-medium text-slate-700 mb-2">
                                    Access time limit (minutes)
                                </label>
                                <input
                                    v-model="form.protection_settings.time_limit"
                                    type="number"
                                    id="time_limit"
                                    min="0"
                                    placeholder="Leave blank for unlimited"
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between">
                    <Link
                        :href="route('admin.library.index')"
                        class="px-6 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        Cancel
                    </Link>
                    
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Resource</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>