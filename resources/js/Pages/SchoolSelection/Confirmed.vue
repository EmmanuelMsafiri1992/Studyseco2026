<template>
    <Head title="School Confirmed" />
    
    <DashboardLayout title="School Confirmed" subtitle="Your exam school has been approved">
        <div class="max-w-4xl mx-auto">
            <!-- Confirmation Banner -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-8 mb-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-green-800 mb-2">🎉 Congratulations!</h1>
                    <p class="text-green-700 text-lg mb-4">Your school selection has been approved</p>
                    <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg font-medium">
                        ✅ Confirmed at {{ confirmedSchool.secondarySchool?.name }}
                    </div>
                </div>
            </div>

            <!-- School Information Card -->
            <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
                <div class="flex items-start gap-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">🏫</span>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ confirmedSchool.secondarySchool?.name }}</h2>
                        <div class="grid md:grid-cols-2 gap-4 text-gray-600">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ confirmedSchool.secondarySchool?.formatted_address }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>{{ confirmedSchool.secondarySchool?.phone || 'Contact details will be provided' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-9 10h10a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Code: {{ confirmedSchool.secondarySchool?.code }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Confirmed on {{ new Date(confirmedSchool.confirmed_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                <h3 class="text-xl font-bold text-blue-900 mb-4">📋 Next Steps</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-blue-600 text-sm font-bold">1</span>
                        </div>
                        <div>
                            <p class="text-blue-800 font-medium">Contact Details</p>
                            <p class="text-blue-700 text-sm">You will receive contact details for a student representative within 24-48 hours</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-blue-600 text-sm font-bold">2</span>
                        </div>
                        <div>
                            <p class="text-blue-800 font-medium">Exam Registration</p>
                            <p class="text-blue-700 text-sm">Contact the school directly to register for exams and get exam dates</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-blue-600 text-sm font-bold">3</span>
                        </div>
                        <div>
                            <p class="text-blue-800 font-medium">Exam Preparation</p>
                            <p class="text-blue-700 text-sm">Continue your studies using the platform's learning materials and resources</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-800 mb-2">⚠️ Important Notice</h4>
                        <div class="text-amber-700 text-sm space-y-1">
                            <p>• You cannot change your school selection once approved</p>
                            <p>• You are responsible for contacting the school for exam registration</p>
                            <p>• Exam dates and arrangements are handled directly by the school</p>
                            <p>• Keep your contact information updated in your profile</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Applications History -->
            <div v-if="allSelections && allSelections.length > 1" class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">All Your Applications</h3>
                    <p class="text-gray-600 text-sm mt-1">Your complete application history</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div 
                            v-for="selection in allSelections" 
                            :key="selection.id"
                            :class="[
                                'border rounded-lg p-4',
                                selection.status === 'confirmed' ? 'border-green-200 bg-green-50' : 
                                selection.status === 'rejected' ? 'border-red-200 bg-red-50' :
                                'border-gray-200 bg-gray-50'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span 
                                            :class="[
                                                'px-2 py-1 rounded text-xs font-medium',
                                                selection.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                                selection.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                                'bg-yellow-100 text-yellow-800'
                                            ]"
                                        >
                                            Priority #{{ selection.priority_order }}
                                        </span>
                                        <h4 class="font-medium text-gray-900">{{ selection.secondary_school.name }}</h4>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ selection.secondary_school.formatted_address }}</p>
                                </div>
                                <div class="text-right">
                                    <span 
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium',
                                            selection.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                            selection.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                            'bg-yellow-100 text-yellow-800'
                                        ]"
                                    >
                                        {{ selection.status === 'confirmed' ? '✅ Approved' :
                                           selection.status === 'rejected' ? '❌ Rejected' :
                                           '⏳ Pending' }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="selection.rejection_reason" class="mt-3 p-3 bg-red-100 rounded text-sm text-red-700">
                                <strong>Rejection reason:</strong> {{ selection.rejection_reason }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex items-center justify-center gap-4">
                <Link 
                    :href="route('dashboard')" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Return to Dashboard
                </Link>
                <Link 
                    :href="route('profile.edit')" 
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                >
                    Update Profile
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    confirmedSchool: Object,
    allSelections: Array
})
</script>