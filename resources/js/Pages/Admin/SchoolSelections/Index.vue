<template>
    <DashboardLayout>
        <Head title="School Selection Management" />
        
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">School Selection Management</h1>
                <p class="text-gray-600">Review and approve student applications for exam centers</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <span class="text-yellow-600 font-semibold text-sm">⏳</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Applications</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ pendingCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-600 font-semibold text-sm">✅</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Approved Today</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ approvedTodayCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <span class="text-red-600 font-semibold text-sm">❌</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Rejected Today</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ rejectedTodayCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-sm">📊</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Applications</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ selections.data.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">School</label>
                        <input 
                            v-model="filters.school" 
                            @input="applyFilters"
                            type="text" 
                            placeholder="Search school name..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <input 
                            v-model="filters.student" 
                            @input="applyFilters"
                            type="text" 
                            placeholder="Search student name..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select v-model="filters.priority" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Priorities</option>
                            <option value="1">Priority 1</option>
                            <option value="2">Priority 2</option>
                            <option value="3">Priority 3</option>
                            <option value="4">Priority 4</option>
                            <option value="5">Priority 5</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">School Applications</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="selection in filteredSelections" :key="selection.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-medium text-sm">
                                                {{ selection.user?.name?.charAt(0).toUpperCase() || '?' }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900">{{ selection.user?.name || 'Unknown User' }}</p>
                                            <p class="text-sm text-gray-500">{{ selection.user?.email || 'No email' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ selection.secondary_school?.name || 'Unknown School' }}</p>
                                        <p class="text-sm text-gray-500">{{ selection.secondary_school?.district || 'Unknown' }}, {{ selection.secondary_school?.region || 'Unknown' }}</p>
                                        <p class="text-xs text-gray-400">{{ selection.secondary_school?.available_slots || 0 }} slots available</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        #{{ selection.priority_order }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            selection.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                            selection.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                            'bg-yellow-100 text-yellow-800'
                                        ]"
                                    >
                                        {{ selection.status.charAt(0).toUpperCase() + selection.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(selection.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div v-if="selection.status === 'pending'" class="flex space-x-2">
                                        <button 
                                            @click="showApprovalModal(selection)" 
                                            class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700"
                                        >
                                            Approve
                                        </button>
                                        <button 
                                            @click="showRejectionModal(selection)" 
                                            class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                    <div v-else class="flex space-x-2">
                                        <button 
                                            @click="showDetailsModal(selection)" 
                                            class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700"
                                        >
                                            View Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="selections?.links" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ selections?.from || 0 }} to {{ selections?.to || 0 }} of {{ selections?.total || 0 }} results
                        </div>
                        <div class="flex space-x-1">
                            <Link
                                v-for="link in (selections?.links || [])"
                                v-if="link?.url"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 text-sm border',
                                    link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                ]"
                            />
                            <span
                                v-for="link in (selections?.links || [])"
                                v-if="!link?.url"
                                :key="link.label"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 text-sm border cursor-not-allowed',
                                    'bg-gray-100 text-gray-400 border-gray-300'
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Modal -->
        <div v-if="showModal && modalType === 'approve'" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Application</h3>
                <p class="text-gray-600 mb-4">
                    Are you sure you want to approve <strong>{{ selectedSelection?.user.name }}</strong>'s 
                    application for <strong>{{ selectedSelection?.secondary_school.name }}</strong>?
                </p>
                <div v-if="selectedSelection?.secondary_school.available_slots <= 0" class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                    <p class="text-red-700 text-sm">⚠️ Warning: This school has no available slots remaining.</p>
                </div>
                <div class="flex space-x-4">
                    <button @click="closeModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="approveSelection" :disabled="processing" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                        {{ processing ? 'Processing...' : 'Approve' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div v-if="showModal && modalType === 'reject'" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Application</h3>
                <p class="text-gray-600 mb-4">
                    Provide a reason for rejecting <strong>{{ selectedSelection?.user.name }}</strong>'s 
                    application for <strong>{{ selectedSelection?.secondary_school.name }}</strong>:
                </p>
                <textarea 
                    v-model="rejectionReason" 
                    rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter rejection reason..."
                    required
                ></textarea>
                <div class="flex space-x-4 mt-4">
                    <button @click="closeModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="rejectSelection" :disabled="processing || !rejectionReason.trim()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                        {{ processing ? 'Processing...' : 'Reject' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    selections: Object
})

const showModal = ref(false)
const modalType = ref('')
const selectedSelection = ref(null)
const rejectionReason = ref('')
const processing = ref(false)

const filters = ref({
    status: '',
    school: '',
    student: '',
    priority: ''
})

const filteredSelections = computed(() => {
    let filtered = props.selections?.data || []

    if (filters.value.status) {
        filtered = filtered.filter(selection => selection.status === filters.value.status)
    }
    if (filters.value.school) {
        filtered = filtered.filter(selection => 
            selection.secondary_school?.name?.toLowerCase().includes(filters.value.school.toLowerCase())
        )
    }
    if (filters.value.student) {
        filtered = filtered.filter(selection => 
            selection.user?.name?.toLowerCase().includes(filters.value.student.toLowerCase())
        )
    }
    if (filters.value.priority) {
        filtered = filtered.filter(selection => selection.priority_order == filters.value.priority)
    }

    return filtered
})

const pendingCount = computed(() => 
    (props.selections?.data || []).filter(s => s.status === 'pending').length
)

const approvedTodayCount = computed(() => 
    (props.selections?.data || []).filter(s => 
        s.status === 'confirmed' && 
        s.confirmed_at && new Date(s.confirmed_at).toDateString() === new Date().toDateString()
    ).length
)

const rejectedTodayCount = computed(() => 
    (props.selections?.data || []).filter(s => 
        s.status === 'rejected' && 
        s.rejected_at && new Date(s.rejected_at).toDateString() === new Date().toDateString()
    ).length
)

const showApprovalModal = (selection) => {
    selectedSelection.value = selection
    modalType.value = 'approve'
    showModal.value = true
}

const showRejectionModal = (selection) => {
    selectedSelection.value = selection
    modalType.value = 'reject'
    rejectionReason.value = ''
    showModal.value = true
}

const showDetailsModal = (selection) => {
    // Implementation for showing selection details
    console.log('Show details for:', selection)
}

const closeModal = () => {
    showModal.value = false
    selectedSelection.value = null
    rejectionReason.value = ''
    modalType.value = ''
}

const approveSelection = () => {
    processing.value = true
    
    router.patch(route('admin.school-selections.update', selectedSelection.value.id), {
        status: 'confirmed'
    }, {
        onSuccess: () => {
            closeModal()
            processing.value = false
        },
        onError: () => {
            processing.value = false
        }
    })
}

const rejectSelection = () => {
    if (!rejectionReason.value.trim()) return
    
    processing.value = true
    
    router.patch(route('admin.school-selections.update', selectedSelection.value.id), {
        status: 'rejected',
        rejection_reason: rejectionReason.value
    }, {
        onSuccess: () => {
            closeModal()
            processing.value = false
        },
        onError: () => {
            processing.value = false
        }
    })
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const applyFilters = () => {
    // Filters are applied automatically through computed property
}
</script>