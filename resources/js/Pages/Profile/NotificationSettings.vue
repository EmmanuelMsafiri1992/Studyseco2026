<template>
    <Head title="Notification Settings" />
    
    <DashboardLayout title="Notification Settings" subtitle="Manage your notification preferences and sound alerts">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">🔔 Notification Settings</h1>
                <p class="text-gray-600">Manage your notification preferences and sound alerts</p>
            </div>

            <!-- Sound Settings -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Sound Notifications</h2>
                
                <div class="space-y-6">
                    <!-- Master Sound Toggle -->
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div>
                            <h3 class="font-medium text-gray-900">Enable Notification Sounds</h3>
                            <p class="text-sm text-gray-600 mt-1">Play audio alerts when you receive notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                :checked="soundEnabled" 
                                @change="toggleSounds"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Sound Types -->
                    <div v-if="soundEnabled" class="space-y-4">
                        <h3 class="font-medium text-gray-900">Test Different Sound Types</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Default Notification -->
                            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Default</h4>
                                        <p class="text-sm text-gray-600">General notifications</p>
                                    </div>
                                    <button 
                                        @click="testSound('default')"
                                        class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                                    >
                                        Test
                                    </button>
                                </div>
                            </div>

                            <!-- Success -->
                            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Success</h4>
                                        <p class="text-sm text-gray-600">Completed actions</p>
                                    </div>
                                    <button 
                                        @click="testSound('success')"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                                    >
                                        Test
                                    </button>
                                </div>
                            </div>

                            <!-- Message -->
                            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Message</h4>
                                        <p class="text-sm text-gray-600">New messages</p>
                                    </div>
                                    <button 
                                        @click="testSound('message')"
                                        class="px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700"
                                    >
                                        Test
                                    </button>
                                </div>
                            </div>

                            <!-- Achievement -->
                            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Achievement</h4>
                                        <p class="text-sm text-gray-600">Unlocked achievements</p>
                                    </div>
                                    <button 
                                        @click="testSound('achievement')"
                                        class="px-3 py-1 bg-yellow-600 text-white rounded text-sm hover:bg-yellow-700"
                                    >
                                        Test
                                    </button>
                                </div>
                            </div>

                            <!-- Warning -->
                            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Warning</h4>
                                        <p class="text-sm text-gray-600">Important alerts</p>
                                    </div>
                                    <button 
                                        @click="testSound('warning')"
                                        class="px-3 py-1 bg-orange-600 text-white rounded text-sm hover:bg-orange-700"
                                    >
                                        Test
                                    </button>
                                </div>
                            </div>

                            <!-- Error -->
                            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Error</h4>
                                        <p class="text-sm text-gray-600">Error messages</p>
                                    </div>
                                    <button 
                                        @click="testSound('error')"
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700"
                                    >
                                        Test
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Notification Preferences -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Notification Preferences</h2>
                
                <div class="space-y-4">
                    <!-- New Messages -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">New Messages</h3>
                            <p class="text-sm text-gray-600 mt-1">Notifications for new messages from teachers</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                checked
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Assignment Updates -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">Assignment Updates</h3>
                            <p class="text-sm text-gray-600 mt-1">Notifications for new assignments and deadlines</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                checked
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Achievements -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">Achievements</h3>
                            <p class="text-sm text-gray-600 mt-1">Notifications when you unlock new achievements</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                checked
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Payment Updates -->
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">Payment Updates</h3>
                            <p class="text-sm text-gray-600 mt-1">Notifications for payment status changes</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                checked
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Browser Permissions Info -->
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <div class="w-6 h-6 text-yellow-600 mr-3">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-yellow-800 mb-1">Browser Permissions</h3>
                        <p class="text-sm text-yellow-700">
                            If sounds don't play, you may need to interact with the page first (click somewhere) or enable audio autoplay in your browser settings.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { soundEnabled, playNotificationSound, toggleNotificationSounds } from '@/composables/useNotificationSounds'

const toggleSounds = () => {
    toggleNotificationSounds()
}

const testSound = (type) => {
    playNotificationSound(type)
}
</script>