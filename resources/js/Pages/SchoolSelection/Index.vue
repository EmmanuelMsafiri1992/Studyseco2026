<template>
    <Head title="School Selection" />
    
    <DashboardLayout title="School Selection" subtitle="Choose schools for your exam centers">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Select Schools for Exam Centers</h1>
                <p class="text-gray-600 mb-4">Choose at least 5 secondary schools where you would like to sit for your exams. Schools will review and confirm your application.</p>
                
                <!-- Status Badge -->
                <div v-if="confirmedSchool" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    ✅ Confirmed at {{ confirmedSchool.secondarySchool?.name || 'Selected School' }}
                </div>
                <div v-else-if="hasRequiredSelections" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    ⏳ {{ userSelections?.length || 0 }} schools selected - awaiting confirmation
                </div>
                <div v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                    📝 Need {{ 5 - (userSelections?.length || 0) }} more school selections
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Available Schools -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Available Schools</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex-1">
                                        <input 
                                            v-model="searchQuery"
                                            type="text" 
                                            placeholder="Search schools by name, region, or district..." 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                    </div>
                                    <select v-model="filterRegion" class="px-4 py-2 border border-gray-300 rounded-lg">
                                        <option value="">All Regions</option>
                                        <option v-for="region in uniqueRegions" :key="region" :value="region">{{ region }}</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <select v-model="filterDistrict" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            <option value="">All Districts</option>
                                            <option v-for="district in filteredDistricts" :key="district" :value="district">{{ district }}</option>
                                        </select>
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600">View:</label>
                                            <button 
                                                @click="viewMode = 'detailed'"
                                                :class="viewMode === 'detailed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'"
                                                class="px-3 py-1 rounded text-sm"
                                            >
                                                Detailed
                                            </button>
                                            <button 
                                                @click="viewMode = 'compact'"
                                                :class="viewMode === 'compact' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'"
                                                class="px-3 py-1 rounded text-sm"
                                            >
                                                Compact
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Showing {{ paginatedSchools.length }} of {{ filteredSchools.length }} schools
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="filteredSchools.length === 0" class="text-center py-12">
                                <div class="text-gray-400 text-6xl mb-4">🏫</div>
                                <p class="text-gray-500">No schools match your search criteria.</p>
                            </div>
                            
                            <!-- Detailed View -->
                            <div v-else-if="viewMode === 'detailed'" class="grid gap-4">
                                <div 
                                    v-for="school in paginatedSchools" 
                                    :key="school.id"
                                    :class="[
                                        'border-2 rounded-lg p-4 cursor-pointer transition-all',
                                        isSchoolSelected(school.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300',
                                        school.available_slots === 0 ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    @click="toggleSchoolSelection(school)"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="font-semibold text-gray-900">{{ school.name }}</h3>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ school.code }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-2">{{ school.formatted_address }}</p>
                                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                                <span>📞 {{ school.phone || 'N/A' }}</span>
                                                <span>👥 {{ school.capacity }} capacity</span>
                                                <span :class="school.available_slots > 0 ? 'text-green-600' : 'text-red-600'">
                                                    {{ school.available_slots }} slots available
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex flex-col items-end gap-2">
                                            <div v-if="isSchoolSelected(school.id)" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                Priority #{{ getSchoolPriority(school.id) }}
                                            </div>
                                            <button 
                                                v-if="!isSchoolSelected(school.id) && selectedSchools.length < 5 && school.available_slots > 0"
                                                @click.stop="addSchoolToSelection(school)"
                                                class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700"
                                            >
                                                Select
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Compact View -->
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div 
                                    v-for="school in paginatedSchools" 
                                    :key="school.id"
                                    :class="[
                                        'border-2 rounded-lg p-3 cursor-pointer transition-all',
                                        isSchoolSelected(school.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300',
                                        school.available_slots === 0 ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    @click="toggleSchoolSelection(school)"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-medium text-gray-900 text-sm truncate">{{ school.name }}</h3>
                                                <div v-if="isSchoolSelected(school.id)" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                                    #{{ getSchoolPriority(school.id) }}
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-600 truncate">{{ school.district }}, {{ school.region }}</p>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="text-xs text-gray-500">{{ school.capacity }} cap.</span>
                                                <span :class="school.available_slots > 0 ? 'text-green-600' : 'text-red-600'" class="text-xs">
                                                    {{ school.available_slots }} slots
                                                </span>
                                            </div>
                                        </div>
                                        <button 
                                            v-if="!isSchoolSelected(school.id) && selectedSchools.length < 5 && school.available_slots > 0"
                                            @click.stop="addSchoolToSelection(school)"
                                            class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 ml-2"
                                        >
                                            Select
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div v-if="totalPages > 1" class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="currentPage = Math.max(1, currentPage - 1)"
                                        :disabled="currentPage === 1"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                                    >
                                        Previous
                                    </button>
                                    
                                    <div class="flex gap-1">
                                        <button 
                                            v-for="page in visiblePages" 
                                            :key="page"
                                            @click="currentPage = page"
                                            :class="[
                                                'px-3 py-2 text-sm border rounded-lg',
                                                page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'
                                            ]"
                                        >
                                            {{ page }}
                                        </button>
                                    </div>
                                    
                                    <button 
                                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                        :disabled="currentPage === totalPages"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Schools Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm sticky top-8">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Your School Selections</h2>
                            <p class="text-sm text-gray-600">Drag to reorder by preference</p>
                        </div>

                        <div class="p-6">
                            <div v-if="selectedSchools.length === 0" class="text-center py-8">
                                <div class="text-gray-400 text-4xl mb-4">📋</div>
                                <p class="text-gray-500 text-sm">No schools selected yet.</p>
                                <p class="text-gray-400 text-xs mt-1">Select at least 5 schools from the left.</p>
                            </div>
                            
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="(selection, index) in selectedSchools" 
                                    :key="selection.school.id"
                                    class="border border-gray-200 rounded-lg p-3"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded font-medium">
                                                    #{{ index + 1 }}
                                                </span>
                                                <h4 class="font-medium text-gray-900 text-sm">{{ selection.school.name }}</h4>
                                            </div>
                                            <p class="text-xs text-gray-500">{{ selection.school.district }}, {{ selection.school.region }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button 
                                                v-if="index > 0"
                                                @click="movePriority(index, -1)"
                                                class="text-gray-400 hover:text-gray-600 p-1"
                                            >
                                                ↑
                                            </button>
                                            <button 
                                                v-if="index < selectedSchools.length - 1"
                                                @click="movePriority(index, 1)"
                                                class="text-gray-400 hover:text-gray-600 p-1"
                                            >
                                                ↓
                                            </button>
                                            <button 
                                                @click="removeSchoolFromSelection(selection.school.id)"
                                                class="text-red-400 hover:text-red-600 p-1"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <button 
                                    @click="saveSelections"
                                    :disabled="selectedSchools.length < 5 || form.processing"
                                    :class="[
                                        'w-full py-3 px-4 rounded-lg font-medium',
                                        selectedSchools.length >= 5 && !form.processing
                                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    ]"
                                >
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else-if="selectedSchools.length < 5">
                                        Need {{ 5 - selectedSchools.length }} more schools
                                    </span>
                                    <span v-else>Save School Selections ({{ selectedSchools.length }})</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Selections -->
            <div v-if="(userSelections?.length || 0) > 0" class="mt-8">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Your Current Applications</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-4">
                            <div 
                                v-for="selection in userSelections || []" 
                                :key="selection.id"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                                Priority #{{ selection.priority_order }}
                                            </span>
                                            <h3 class="font-medium text-gray-900">{{ selection.secondary_school.name }}</h3>
                                        </div>
                                        <p class="text-sm text-gray-600">{{ selection.secondary_school.formatted_address }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span 
                                            :class="[
                                                'px-2 py-1 rounded text-xs font-medium',
                                                selection.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                                selection.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                                'bg-yellow-100 text-yellow-800'
                                            ]"
                                        >
                                            {{ selection.status.charAt(0).toUpperCase() + selection.status.slice(1) }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="selection.rejection_reason" class="mt-2 p-2 bg-red-50 rounded text-sm text-red-700">
                                    <strong>Reason:</strong> {{ selection.rejection_reason }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    schools: Array,
    userSelections: Array,
    hasRequiredSelections: Boolean,
    confirmedSchool: Object
})

const searchQuery = ref('')
const filterRegion = ref('')
const filterDistrict = ref('')
const selectedSchools = ref([])
const viewMode = ref('compact')
const currentPage = ref(1)
const itemsPerPage = ref(4)

const form = useForm({
    school_selections: []
})

const uniqueRegions = computed(() => {
    return [...new Set((props.schools || []).map(school => school.region))].sort()
})

const filteredDistricts = computed(() => {
    const schoolsInRegion = filterRegion.value 
        ? (props.schools || []).filter(school => school.region === filterRegion.value)
        : (props.schools || [])
    return [...new Set(schoolsInRegion.map(school => school.district))].sort()
})

const filteredSchools = computed(() => {
    let filtered = (props.schools || []).filter(school => {
        const matchesSearch = !searchQuery.value || 
            school.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            school.region.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            school.district.toLowerCase().includes(searchQuery.value.toLowerCase())
        
        const matchesRegion = !filterRegion.value || school.region === filterRegion.value
        const matchesDistrict = !filterDistrict.value || school.district === filterDistrict.value
        
        return matchesSearch && matchesRegion && matchesDistrict
    })
    
    return filtered.sort((a, b) => (b.available_slots || 0) - (a.available_slots || 0))
})

const totalPages = computed(() => {
    return Math.ceil(filteredSchools.value.length / itemsPerPage.value)
})

const paginatedSchools = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredSchools.value.slice(start, end)
})

const visiblePages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    const delta = 2
    
    let start = Math.max(1, current - delta)
    let end = Math.min(total, current + delta)
    
    if (end - start < delta * 2) {
        start = Math.max(1, end - delta * 2)
        end = Math.min(total, start + delta * 2)
    }
    
    const pages = []
    for (let i = start; i <= end; i++) {
        pages.push(i)
    }
    return pages
})

const isSchoolSelected = (schoolId) => {
    return selectedSchools.value.some(selection => selection.school.id === schoolId)
}

const getSchoolPriority = (schoolId) => {
    const index = selectedSchools.value.findIndex(selection => selection.school.id === schoolId)
    return index >= 0 ? index + 1 : null
}

const addSchoolToSelection = (school) => {
    if (selectedSchools.value.length >= 5) {
        alert('You can select maximum 5 schools.')
        return
    }
    
    if (!isSchoolSelected(school.id)) {
        selectedSchools.value.push({
            school: school,
            priority: selectedSchools.value.length + 1
        })
    }
}

const removeSchoolFromSelection = (schoolId) => {
    selectedSchools.value = selectedSchools.value.filter(selection => selection.school.id !== schoolId)
    // Reorder priorities
    selectedSchools.value.forEach((selection, index) => {
        selection.priority = index + 1
    })
}

const movePriority = (currentIndex, direction) => {
    const newIndex = currentIndex + direction
    if (newIndex >= 0 && newIndex < selectedSchools.value.length) {
        const temp = selectedSchools.value[currentIndex]
        selectedSchools.value[currentIndex] = selectedSchools.value[newIndex]
        selectedSchools.value[newIndex] = temp
        
        // Update priorities
        selectedSchools.value.forEach((selection, index) => {
            selection.priority = index + 1
        })
    }
}

const saveSelections = () => {
    if (selectedSchools.value.length < 5) {
        alert('Please select at least 5 schools.')
        return
    }
    
    form.school_selections = selectedSchools.value.map((selection, index) => ({
        school_id: selection.school.id,
        priority: index + 1
    }))
    
    form.post(route('school-selections.store'), {
        onSuccess: () => {
            selectedSchools.value = []
            router.reload()
        }
    })
}

const toggleSchoolSelection = (school) => {
    if (school.available_slots === 0) return
    
    if (isSchoolSelected(school.id)) {
        removeSchoolFromSelection(school.id)
    } else {
        addSchoolToSelection(school)
    }
}

// Reset to page 1 when filters change
watch([searchQuery, filterRegion, filterDistrict], () => {
    currentPage.value = 1
})

// Clear district filter when region changes
watch(filterRegion, () => {
    filterDistrict.value = ''
})
</script>