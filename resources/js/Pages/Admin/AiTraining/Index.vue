<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    materials: Object,
    subjects: Array,
    stats: Object
});

const showUploadModal = ref(false);
const selectedMaterials = ref([]);
const fileInput = ref(null);

const uploadForm = useForm({
    title: '',
    subject_id: '',
    material_type: 'book',
    description: '',
    file: null,
    tags: ''
});

const materialTypes = [
    { value: 'book', label: 'Textbook', icon: '📚' },
    { value: 'past_paper', label: 'Past Paper', icon: '📄' },
    { value: 'notes', label: 'Study Notes', icon: '📝' },
    { value: 'questions', label: 'Questions', icon: '❓' },
    { value: 'answers', label: 'Answers', icon: '✅' }
];

const openUploadModal = () => {
    showUploadModal.value = true;
    uploadForm.reset();
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    uploadForm.reset();
};

const selectFile = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadForm.file = file;
    }
};

const submitUpload = () => {
    uploadForm.post(route('admin.ai-training.store'), {
        onSuccess: () => {
            closeUploadModal();
        }
    });
};

const deleteMaterial = (material) => {
    if (confirm(`Are you sure you want to delete "${material.title}"?`)) {
        router.delete(route('admin.ai-training.destroy', material.id));
    }
};

const processMaterial = (material) => {
    router.post(route('admin.ai-training.process', material.id));
};

const bulkProcess = () => {
    if (selectedMaterials.value.length === 0) {
        alert('Please select materials to process.');
        return;
    }
    
    if (confirm(`Process ${selectedMaterials.value.length} selected materials?`)) {
        router.post(route('admin.ai-training.bulk-process'), {
            material_ids: selectedMaterials.value
        });
    }
};

const toggleSelection = (materialId) => {
    const index = selectedMaterials.value.indexOf(materialId);
    if (index > -1) {
        selectedMaterials.value.splice(index, 1);
    } else {
        selectedMaterials.value.push(materialId);
    }
};

const toggleSelectAll = () => {
    const materialsData = props.materials?.data || [];
    if (selectedMaterials.value.length === materialsData.length) {
        selectedMaterials.value = [];
    } else {
        selectedMaterials.value = materialsData.map(m => m.id);
    }
};

const getStatusColor = (status) => {
    const colors = {
        'pending': 'yellow',
        'processing': 'blue',
        'completed': 'green',
        'failed': 'red'
    };
    return colors[status] || 'gray';
};

const getTypeIcon = (type) => {
    const typeObj = materialTypes.find(t => t.value === type);
    return typeObj ? typeObj.icon : '📁';
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="AI Training Materials" />
    
    <DashboardLayout title="AI Training Materials" subtitle="Manage AI knowledge base and training data">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Materials</p>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total_materials?.toLocaleString() || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">📚</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Processed</p>
                        <p class="text-2xl font-bold text-slate-900">{{ Object.values(stats.by_type || {}).reduce((a, b) => a + b, 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">✅</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Subjects Covered</p>
                        <p class="text-2xl font-bold text-slate-900">{{ Object.keys(stats.by_subject || {}).length }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">🎯</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Size</p>
                        <p class="text-2xl font-bold text-slate-900">{{ formatFileSize(stats.total_size || 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">💾</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <button @click="openUploadModal" 
                        class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Upload Material</span>
                </button>
                
                <button v-if="selectedMaterials.length > 0" 
                        @click="bulkProcess"
                        class="px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span>Process Selected ({{ selectedMaterials.length }})</span>
                </button>
            </div>
        </div>

        <!-- Materials Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Training Materials</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       :checked="selectedMaterials.length === (materials?.data?.length || 0) && (materials?.data?.length || 0) > 0"
                                       :indeterminate="selectedMaterials.length > 0 && selectedMaterials.length < (materials?.data?.length || 0)"
                                       @change="toggleSelectAll"
                                       class="rounded border-slate-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Material</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Uploaded</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="material in materials?.data || []" :key="material.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       :checked="selectedMaterials.includes(material.id)"
                                       @change="toggleSelection(material.id)"
                                       class="rounded border-slate-300">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">{{ getTypeIcon(material.type) }}</span>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ material.title }}</div>
                                        <div class="text-sm text-slate-500" v-if="material.content">{{ material.content.substring(0, 60) }}...</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    {{ material.subject?.name || 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                    {{ materialTypes.find(t => t.value === material.type)?.label || material.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    material.is_processed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                ]">
                                    {{ material.is_processed ? 'Processed' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ formatFileSize(material.file_size) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ new Date(material.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button v-if="!material.is_processed"
                                            @click="processMaterial(material)"
                                            class="text-green-600 hover:text-green-900">
                                        Process
                                    </button>
                                    <button @click="deleteMaterial(material)"
                                            class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        Showing {{ materials?.from || 0 }} to {{ materials?.to || 0 }} of {{ materials?.total || 0 }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link v-if="materials?.prev_page_url" :href="materials.prev_page_url" 
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                            Previous
                        </Link>
                        <Link v-if="materials?.next_page_url" :href="materials.next_page_url"
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 shadow-2xl border border-slate-200 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-900">Upload Training Material</h3>
                    <button @click="closeUploadModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitUpload" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Title</label>
                            <input v-model="uploadForm.title" type="text" required 
                                   class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                            <select v-model="uploadForm.subject_id" required 
                                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Subject</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Material Type</label>
                        <select v-model="uploadForm.material_type" required 
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option v-for="type in materialTypes" :key="type.value" :value="type.value">
                                {{ type.icon }} {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea v-model="uploadForm.description" rows="3" 
                                  class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                  placeholder="Brief description of the material..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">File</label>
                        <input ref="fileInput" type="file" @change="handleFileSelect" 
                               accept=".pdf,.doc,.docx,.txt" class="hidden">
                        <button type="button" @click="selectFile" 
                                class="w-full p-4 border-2 border-dashed border-slate-300 rounded-lg hover:border-indigo-400 transition-colors">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm text-slate-600">
                                    {{ uploadForm.file ? uploadForm.file.name : 'Click to upload file' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">PDF, DOC, DOCX, TXT up to 50MB</p>
                            </div>
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tags (comma separated)</label>
                        <input v-model="uploadForm.tags" type="text" 
                               placeholder="e.g. algebra, equations, form-4" 
                               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeUploadModal" 
                                class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="uploadForm.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            {{ uploadForm.processing ? 'Uploading...' : 'Upload Material' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>