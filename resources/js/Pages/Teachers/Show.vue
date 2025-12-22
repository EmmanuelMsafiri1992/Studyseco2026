<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    teacher: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this teacher? This action cannot be undone.')) {
        router.delete(route('teachers.destroy', props.teacher.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Teacher: ${teacher.name}`" />
    
    <DashboardLayout :title="`Teacher: ${teacher.name}`" subtitle="View teacher details and information">
        <div class="space-y-6">
                <!-- Teacher Info Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                                {{ teacher.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-slate-800">{{ teacher.name }}</h1>
                                <p class="text-slate-600">Teacher ID: {{ teacher.id }}</p>
                                <p class="text-sm text-slate-500">Joined {{ formatDate(teacher.created_at) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('teachers.edit', teacher.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
                            >
                                Edit Teacher
                            </Link>
                            <button
                                v-if="user.role === 'admin'"
                                @click="confirmDelete"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
                            >
                                Delete Teacher
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-3">Contact Information</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-slate-600">Email:</span>
                                    <p class="font-medium text-slate-800">{{ teacher.email }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-slate-600">Phone:</span>
                                    <p class="font-medium text-slate-800">{{ teacher.phone || 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-3">Account Status</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-slate-600">Status:</span>
                                    <span :class="['inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ml-2', teacher.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700']">
                                        {{ teacher.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-slate-600">Role:</span>
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ml-2 bg-blue-100 text-blue-700">
                                        Teacher
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-start">
                    <Link
                        :href="route('teachers.index')"
                        class="px-6 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors duration-200"
                    >
                        ← Back to Teachers
                    </Link>
                </div>
        </div>
    </DashboardLayout>
</template>