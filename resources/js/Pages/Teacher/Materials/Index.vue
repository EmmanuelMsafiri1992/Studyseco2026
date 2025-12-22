<template>
    <Head title="Teaching Materials" />
    
    <DashboardLayout 
        title="Teaching Materials" 
        subtitle="Manage and share educational resources with your students">
        <div class="max-w-7xl mx-auto">
            <!-- Upload Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-sm">📁</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-900">Total Materials</p>
                                <p class="text-xl font-bold text-blue-600">{{ (materials || []).length }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-600 font-semibold text-sm">📊</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-900">This Month</p>
                                <p class="text-xl font-bold text-green-600">{{ uploadStats.this_month }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-purple-600 font-semibold text-sm">📚</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-900">Subjects</p>
                                <p class="text-xl font-bold text-purple-600">{{ (subjects || []).length }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <span class="text-amber-600 font-semibold text-sm">👁️</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-amber-900">Total Views</p>
                                <p class="text-xl font-bold text-amber-600">248</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Upload New Material</h2>
                </div>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Upload Teaching Materials</h3>
                    <p class="text-gray-600 mb-4">Drag and drop files here, or click to select</p>
                    <input ref="fileInput" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.mp4,.mov,.avi,.mp3,.wav" class="hidden" @change="handleFileSelect">
                    <button @click="$refs.fileInput.click()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Choose Files
                    </button>
                    <p class="text-xs text-gray-500 mt-2">Supported: PDF, DOC, PPT, images (max 50MB each)</p>
                </div>
                
                <!-- Upload Form -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <select v-model="form.subject_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Subject</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Material Type</label>
                        <select v-model="form.material_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="lesson_plan">Lesson Plan</option>
                            <option value="worksheet">Worksheet</option>
                            <option value="presentation">Presentation</option>
                            <option value="reference">Reference Material</option>
                            <option value="assignment">Assignment</option>
                        </select>
                    </div>
                </div>
                
                <!-- Additional Form Fields -->
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input v-model="form.title" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Enter material title">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (optional)</label>
                        <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Brief description of the material"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                            <input v-model="form.grade_level" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., Form 1-4">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <input v-model="form.duration" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., 45">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                            <input v-model="form.tags" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="comma, separated, tags">
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.is_public" type="checkbox" id="is_public" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_public" class="ml-2 text-sm text-gray-700">Make this material public (visible to other teachers)</label>
                    </div>
                    <div class="flex justify-end">
                        <button @click="uploadMaterial" :disabled="!form.file" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Upload Material
                        </button>
                    </div>
                </div>
            </div>

            <!-- Materials List -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Your Materials</h2>
                    <div class="flex space-x-2">
                        <input 
                            type="text" 
                            placeholder="Search materials..." 
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        >
                        <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">All Subjects</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select>
                    </div>
                </div>
                
                <div v-if="(materials || []).length === 0" class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📚</div>
                    <p class="text-gray-500 text-lg">No materials uploaded yet.</p>
                    <p class="text-gray-400 text-sm mt-2">Upload your first teaching material to get started.</p>
                </div>
                
                <div v-else class="divide-y divide-gray-200">
                    <div v-for="material in materials" 
                         :key="material.id"
                         class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                                     :class="material.color">
                                    <span class="text-white font-semibold text-sm">
                                        {{ material.icon }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ material.title }}</h3>
                                    <p class="text-sm text-gray-600">{{ material.subject }} • {{ material.material_type }}</p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-xs text-gray-500">
                                            📅 {{ formatDate(material.created_at) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            📁 {{ material.file_size }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            📥 {{ material.download_count }} downloads
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            👁️ {{ material.view_count }} views
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button @click="shareMaterial(material)" class="text-blue-600 hover:text-blue-800 text-sm bg-blue-50 px-3 py-1 rounded">
                                    Share
                                </button>
                                <button @click="downloadMaterial(material)" class="text-green-600 hover:text-green-800 text-sm bg-green-50 px-3 py-1 rounded">
                                    Download
                                </button>
                                <button @click="deleteMaterial(material)" class="text-red-600 hover:text-red-800 text-sm bg-red-50 px-3 py-1 rounded">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    materials: Array,
    subjects: Array,
    uploadStats: Object
})

const form = ref({
    title: '',
    subject_id: '',
    material_type: '',
    description: '',
    grade_level: '',
    duration: '',
    tags: '',
    is_public: false,
    file: null
})

// Sample materials for demonstration
const sampleMaterials = ref([
    {
        id: 1,
        title: 'Algebra Fundamentals',
        subject: 'Mathematics',
        type: 'lesson_plan',
        size: '2.3 MB',
        downloads: 15,
        created_at: '2024-01-15'
    },
    {
        id: 2,
        title: 'Physics Lab Manual',
        subject: 'Physics',
        type: 'reference',
        size: '5.7 MB',
        downloads: 23,
        created_at: '2024-01-12'
    },
    {
        id: 3,
        title: 'Chemistry Worksheet Set 1',
        subject: 'Chemistry',
        type: 'worksheet',
        size: '1.8 MB',
        downloads: 31,
        created_at: '2024-01-10'
    }
])

const getFileTypeColor = (type) => {
    const colors = {
        lesson_plan: 'bg-blue-600',
        worksheet: 'bg-green-600',
        presentation: 'bg-purple-600',
        reference: 'bg-indigo-600',
        assignment: 'bg-amber-600'
    }
    return colors[type] || 'bg-gray-600'
}

const getFileTypeIcon = (type) => {
    const icons = {
        lesson_plan: '📋',
        worksheet: '📝',
        presentation: '📊',
        reference: '📚',
        assignment: '📋'
    }
    return icons[type] || '📄'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const shareMaterial = (material) => {
    // Create shareable link
    const shareUrl = `${window.location.origin}/materials/share/${material.id}`
    
    if (navigator.share) {
        navigator.share({
            title: material.title,
            text: `Check out this teaching material: ${material.title}`,
            url: shareUrl
        }).catch(console.error)
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(shareUrl).then(() => {
            alert('Share link copied to clipboard!')
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea')
            textArea.value = shareUrl
            document.body.appendChild(textArea)
            textArea.select()
            document.execCommand('copy')
            document.body.removeChild(textArea)
            alert('Share link copied to clipboard!')
        })
    }
}

const downloadMaterial = (material) => {
    // Create a temporary download link
    const link = document.createElement('a')
    link.href = `/teacher/materials/${material.id}/download`
    link.download = material.file_name || material.title
    link.target = '_blank'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

const deleteMaterial = (material) => {
    if (confirm(`Are you sure you want to delete "${material.title}"? This action cannot be undone.`)) {
        router.delete(`/teacher/materials/${material.id}`, {
            onSuccess: () => {
                console.log('Material deleted successfully')
            },
            onError: (errors) => {
                console.error('Delete errors:', errors)
                alert('Error deleting material. Please try again.')
            }
        })
    }
}

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files)
    
    if (files.length === 0) return
    
    // For now, handle single file upload
    form.value.file = files[0]
    
    console.log(`Selected file: ${files[0].name}, size: ${(files[0].size / 1024 / 1024).toFixed(2)}MB`)
}

const uploadMaterial = () => {
    if (!form.value.title || !form.value.subject_id || !form.value.material_type || !form.value.file) {
        alert('Please fill in all required fields and select a file')
        return
    }

    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null) {
            formData.append(key, form.value[key])
        }
    })

    // Use Inertia's router for form submission
    router.post('/teacher/materials', formData, {
        forceFormData: true,
        onSuccess: () => {
            // Reset form
            form.value = {
                title: '',
                subject_id: '',
                material_type: '',
                description: '',
                grade_level: '',
                duration: '',
                tags: '',
                is_public: false,
                file: null
            }
            // Clear file input
            if (fileInput.value) {
                fileInput.value.value = ''
            }
        },
        onError: (errors) => {
            console.error('Upload errors:', errors)
        }
    })
}
</script>