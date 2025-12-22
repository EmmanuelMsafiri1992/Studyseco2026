<template>
    <Head title="My Students" />
    
    <DashboardLayout 
        title="My Assigned Students" 
        subtitle="Manage and communicate with your assigned students">
        <div class="max-w-7xl mx-auto">
            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-sm">👥</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-900">Total Students</p>
                                <p class="text-xl font-bold text-blue-600">{{ assignedStudents.length }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-600 font-semibold text-sm">✅</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-900">Active Access</p>
                                <p class="text-xl font-bold text-green-600">{{ activeStudents }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <span class="text-amber-600 font-semibold text-sm">🔄</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-amber-900">Trial Students</p>
                                <p class="text-xl font-bold text-amber-600">{{ trialStudents }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-purple-600 font-semibold text-sm">📚</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-900">Avg Subjects</p>
                                <p class="text-xl font-bold text-purple-600">{{ averageSubjects }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Students</label>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by name or email..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                        <select v-model="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Students</option>
                            <option value="active">Active Access</option>
                            <option value="trial">Trial Students</option>
                            <option value="expired">Expired Access</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select v-model="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="name">Name (A-Z)</option>
                            <option value="recent">Recently Assigned</option>
                            <option value="subjects">Subject Count</option>
                            <option value="expiry">Access Expiry</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Students ({{ filteredStudents.length }})</h2>
                </div>
                
                <div v-if="filteredStudents.length === 0" class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">👥</div>
                    <p class="text-gray-500 text-lg">No students match your search criteria.</p>
                    <p class="text-gray-400 text-sm mt-2">Try adjusting your filters or search terms.</p>
                </div>
                
                <div v-else class="divide-y divide-gray-200">
                    <div 
                        v-for="student in filteredStudents" 
                        :key="student.id"
                        class="p-6 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-lg">
                                        {{ student.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ student.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ student.email }}</p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                            📚 {{ student.subjects_count }} subjects
                                        </span>
                                        <span 
                                            :class="[
                                                'text-xs px-2 py-1 rounded',
                                                getStatusColor(student)
                                            ]"
                                        >
                                            {{ getStatusText(student) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Assigned {{ formatDate(student.tutor_assigned_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button 
                                    @click="openMessageModal(student)"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
                                    </svg>
                                    <span>Message</span>
                                </button>
                                <button 
                                    @click="scheduleSession(student)"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Schedule</span>
                                </button>
                                <button 
                                    @click="viewDetails(student)"
                                    class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700"
                                >
                                    View Details
                                </button>
                            </div>
                        </div>

                        <!-- Student Details Expanded -->
                        <div v-if="expandedStudent === student.id" class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Enrollment Details</h4>
                                    <p class="text-sm text-gray-600">ID: {{ student.enrollment_id }}</p>
                                    <p class="text-sm text-gray-600">Type: {{ student.is_trial ? 'Trial' : 'Paid' }}</p>
                                    <p class="text-sm text-gray-600">Status: {{ student.status }}</p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Access Information</h4>
                                    <p class="text-sm text-gray-600">
                                        Expires: {{ formatDate(student.access_expires_at) }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Days Remaining: {{ getDaysRemaining(student.access_expires_at) }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Actions</h4>
                                    <div class="space-y-2">
                                        <button class="text-blue-600 hover:text-blue-800 text-sm">View Progress</button>
                                        <button class="text-green-600 hover:text-green-800 text-sm block">Schedule Meeting</button>
                                        <button class="text-purple-600 hover:text-purple-800 text-sm block">Share Materials</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <div v-if="showMessageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Send Message to {{ selectedStudent?.name }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input 
                            v-model="messageForm.subject"
                            type="text" 
                            placeholder="Message subject..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea 
                            v-model="messageForm.message"
                            rows="4" 
                            placeholder="Type your message here..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>
                    <div class="flex space-x-4 pt-4">
                        <button @click="closeMessageModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="sendMessage" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Send Message
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    assignedStudents: Array,
    stats: Object
})

const searchQuery = ref('')
const statusFilter = ref('')
const sortBy = ref('name')
const expandedStudent = ref(null)
const showMessageModal = ref(false)
const selectedStudent = ref(null)

const messageForm = ref({
    subject: '',
    message: ''
})

const filteredStudents = computed(() => {
    let filtered = props.assignedStudents || []

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(student => 
            student.name.toLowerCase().includes(query) ||
            student.email.toLowerCase().includes(query)
        )
    }

    // Status filter
    if (statusFilter.value) {
        filtered = filtered.filter(student => {
            if (statusFilter.value === 'active') {
                return !student.is_trial && new Date(student.access_expires_at) > new Date()
            }
            if (statusFilter.value === 'trial') {
                return student.is_trial
            }
            if (statusFilter.value === 'expired') {
                return new Date(student.access_expires_at) <= new Date()
            }
            return true
        })
    }

    // Sort
    filtered.sort((a, b) => {
        switch (sortBy.value) {
            case 'name':
                return a.name.localeCompare(b.name)
            case 'recent':
                return new Date(b.tutor_assigned_at) - new Date(a.tutor_assigned_at)
            case 'subjects':
                return b.subjects_count - a.subjects_count
            case 'expiry':
                return new Date(a.access_expires_at) - new Date(b.access_expires_at)
            default:
                return 0
        }
    })

    return filtered
})

const activeStudents = computed(() => {
    return props.assignedStudents?.filter(student => 
        !student.is_trial && new Date(student.access_expires_at) > new Date()
    ).length || 0
})

const trialStudents = computed(() => {
    return props.assignedStudents?.filter(student => student.is_trial).length || 0
})

const averageSubjects = computed(() => {
    if (!props.assignedStudents?.length) return 0
    const total = props.assignedStudents.reduce((sum, student) => sum + student.subjects_count, 0)
    return Math.round(total / props.assignedStudents.length)
})

const getStatusColor = (student) => {
    if (student.is_trial) {
        return 'bg-amber-100 text-amber-800'
    }
    if (new Date(student.access_expires_at) <= new Date()) {
        return 'bg-red-100 text-red-800'
    }
    return 'bg-green-100 text-green-800'
}

const getStatusText = (student) => {
    if (student.is_trial) {
        return '🔄 Trial'
    }
    if (new Date(student.access_expires_at) <= new Date()) {
        return '❌ Expired'
    }
    return '✅ Active'
}

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'N/A'
}

const getDaysRemaining = (expiryDate) => {
    if (!expiryDate) return 'N/A'
    const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24))
    return days > 0 ? `${days} days` : 'Expired'
}

const viewDetails = (student) => {
    expandedStudent.value = expandedStudent.value === student.id ? null : student.id
}

const openMessageModal = (student) => {
    selectedStudent.value = student
    messageForm.value = { subject: '', message: '' }
    showMessageModal.value = true
}

const closeMessageModal = () => {
    showMessageModal.value = false
    selectedStudent.value = null
    messageForm.value = { subject: '', message: '' }
}

const sendMessage = () => {
    // TODO: Implement message sending functionality
    console.log('Sending message to:', selectedStudent.value.name)
    console.log('Message:', messageForm.value)
    closeMessageModal()
}

const scheduleSession = (student) => {
    // TODO: Implement session scheduling
    console.log('Scheduling session with:', student.name)
}
</script>