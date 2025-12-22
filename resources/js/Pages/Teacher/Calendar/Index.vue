<template>
    <Head title="Teaching Calendar" />
    
    <DashboardLayout 
        title="Teaching Calendar" 
        subtitle="Manage your teaching schedule and student sessions">
        <div class="max-w-7xl mx-auto">
            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-sm">📅</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-900">Today's Sessions</p>
                                <p class="text-xl font-bold text-blue-600">{{ (sessions || []).length }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-600 font-semibold text-sm">⏰</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-900">Upcoming</p>
                                <p class="text-xl font-bold text-green-600">{{ (upcomingSessions || []).length }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-purple-600 font-semibold text-sm">📊</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-900">This Week</p>
                                <p class="text-xl font-bold text-purple-600">12</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <span class="text-amber-600 font-semibold text-sm">⭐</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-amber-900">This Month</p>
                                <p class="text-xl font-bold text-amber-600">48</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Calendar View</h2>
                    <div class="flex space-x-2">
                        <button @click="openScheduleModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Schedule Session</span>
                        </button>
                    </div>
                </div>
                
                <!-- Calendar Grid -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="grid grid-cols-7 bg-gray-50">
                        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                             :key="day" 
                             class="p-3 text-center text-sm font-medium text-gray-700 border-b border-gray-200">
                            {{ day }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7">
                        <div v-for="date in calendarDates" 
                             :key="date" 
                             @click="selectDate(date)"
                             class="h-24 p-2 border-b border-r border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors"
                             :class="{ 'bg-blue-50 ring-2 ring-blue-300': selectedDate === date }">
                            <div class="text-sm font-medium text-gray-900 mb-1">{{ date }}</div>
                            <div v-if="date === 15" class="text-xs bg-blue-100 text-blue-800 p-1 rounded mb-1">
                                10:00 AM - Math
                            </div>
                            <div v-if="date === 17" class="text-xs bg-green-100 text-green-800 p-1 rounded mb-1">
                                2:00 PM - Physics
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Sessions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Upcoming Sessions</h2>
                    <button class="text-blue-600 hover:text-blue-800 text-sm">View All</button>
                </div>
                
                <div v-if="(upcomingSessions || []).length === 0" class="text-center py-8">
                    <div class="text-gray-400 text-6xl mb-4">📅</div>
                    <p class="text-gray-500 text-lg">No upcoming sessions scheduled.</p>
                    <button @click="openScheduleModal" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Schedule Your First Session
                    </button>
                </div>
                
                <div v-else class="space-y-4">
                    <div v-for="session in sampleSessions" 
                         :key="session.id"
                         class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-semibold">{{ session.subject.charAt(0) }}</span>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ session.subject }} Session</h3>
                                <p class="text-sm text-gray-600">{{ session.student }}</p>
                                <p class="text-xs text-gray-500">{{ session.date }} at {{ session.time }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span :class="[
                                'text-xs px-2 py-1 rounded',
                                session.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'
                            ]">
                                {{ session.status }}
                            </span>
                            <button class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Session Modal -->
        <div v-if="showScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule New Session</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                        <select v-model="scheduleForm.student" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a student</option>
                            <option value="john_doe">John Doe</option>
                            <option value="jane_smith">Jane Smith</option>
                            <option value="mike_johnson">Mike Johnson</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <select v-model="scheduleForm.subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select subject</option>
                            <option value="mathematics">Mathematics</option>
                            <option value="physics">Physics</option>
                            <option value="chemistry">Chemistry</option>
                            <option value="biology">Biology</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input type="date" v-model="scheduleForm.date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                            <input type="time" v-model="scheduleForm.time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                        <select v-model="scheduleForm.duration" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="30">30 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="90">1.5 hours</option>
                            <option value="120">2 hours</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                        <textarea v-model="scheduleForm.notes" rows="3" placeholder="Session notes or agenda..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex space-x-4 pt-4">
                        <button @click="closeScheduleModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="scheduleSession" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Schedule Session
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
    sessions: Array,
    upcoming_sessions: Array,
    calendar_events: Array
})

const upcomingSessions = computed(() => props.upcoming_sessions || [])
const selectedDate = ref(null)
const showScheduleModal = ref(false)
const scheduleForm = ref({
    student: '',
    subject: '',
    date: '',
    time: '',
    duration: '60',
    notes: ''
})

// Sample data for demonstration
const sampleSessions = ref([
    {
        id: 1,
        subject: 'Mathematics',
        student: 'John Doe',
        date: 'Tomorrow',
        time: '10:00 AM',
        status: 'confirmed'
    },
    {
        id: 2,
        subject: 'Physics',
        student: 'Jane Smith',
        date: 'Friday',
        time: '2:00 PM',
        status: 'pending'
    },
    {
        id: 3,
        subject: 'Chemistry',
        student: 'Mike Johnson',
        date: 'Monday',
        time: '11:30 AM',
        status: 'confirmed'
    }
])

// Generate calendar dates (simplified for demo)
const calendarDates = computed(() => {
    const dates = []
    for (let i = 1; i <= 30; i++) {
        dates.push(i)
    }
    return dates
})

const selectDate = (date) => {
    selectedDate.value = date
    console.log(`Selected date: ${date}`)
    
    // Auto-fill the date in schedule form if modal is open
    if (showScheduleModal.value) {
        const today = new Date()
        const selectedDateObj = new Date(today.getFullYear(), today.getMonth(), date)
        scheduleForm.value.date = selectedDateObj.toISOString().split('T')[0]
    }
}

const openScheduleModal = () => {
    showScheduleModal.value = true
    
    // Pre-fill with selected date if any
    if (selectedDate.value) {
        const today = new Date()
        const selectedDateObj = new Date(today.getFullYear(), today.getMonth(), selectedDate.value)
        scheduleForm.value.date = selectedDateObj.toISOString().split('T')[0]
    } else {
        // Default to today
        scheduleForm.value.date = new Date().toISOString().split('T')[0]
    }
}

const closeScheduleModal = () => {
    showScheduleModal.value = false
    scheduleForm.value = {
        student: '',
        subject: '',
        date: '',
        time: '',
        duration: '60',
        notes: ''
    }
}

const scheduleSession = () => {
    if (!scheduleForm.value.student || !scheduleForm.value.subject || !scheduleForm.value.date || !scheduleForm.value.time) {
        alert('Please fill in all required fields.')
        return
    }
    
    console.log('Scheduling session:', scheduleForm.value)
    
    // TODO: Send to backend
    // router.post('/teacher/calendar', scheduleForm.value)
    
    alert('Session scheduled successfully!')
    closeScheduleModal()
}
</script>