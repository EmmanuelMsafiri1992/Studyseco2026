<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    teacher: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

const form = useForm({
    name: props.teacher.name,
    email: props.teacher.email,
    phone: props.teacher.phone || '',
    is_active: props.teacher.is_active,
});

const submit = () => {
    form.put(route('teachers.update', props.teacher.id), {
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this teacher? This action cannot be undone.')) {
        router.delete(route('teachers.destroy', props.teacher.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Edit Teacher: ${teacher.name}`" />
    
    <DashboardLayout :title="`Edit Teacher: ${teacher.name}`" subtitle="Update teacher information">
        <div class="space-y-6">
                <!-- Edit Form -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Name *</label>
                                <input v-model="form.name" type="text" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                                <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Email *</label>
                                <input v-model="form.email" type="email" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>

                            <!-- Phone Field -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Phone</label>
                                <input v-model="form.phone" type="tel" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                            </div>

                            <!-- Status Field -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Account Status</label>
                                <div class="mt-2">
                                    <label class="flex items-center">
                                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">Active Account</span>
                                    </label>
                                </div>
                                <div v-if="form.errors.is_active" class="text-red-500 text-xs mt-1">{{ form.errors.is_active }}</div>
                            </div>
                        </div>

                        <!-- Teacher Information Display -->
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-lg font-semibold text-slate-800 mb-4">Teacher Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-sm text-slate-600">Teacher ID:</span>
                                    <p class="font-medium text-slate-800">{{ teacher.id }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-slate-600">Registration Date:</span>
                                    <p class="font-medium text-slate-800">{{ formatDate(teacher.created_at) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-slate-600">Role:</span>
                                    <p class="font-medium text-slate-800">Teacher</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                            <Link :href="route('teachers.show', teacher.id)" class="text-slate-600 hover:text-slate-800 transition-colors duration-200">
                                ← Back to Teacher
                            </Link>

                            <div class="flex gap-4">
                                <Link :href="route('teachers.index')" class="px-6 py-3 bg-slate-100 text-slate-700 rounded-2xl font-semibold hover:bg-slate-200 transition-all duration-200">
                                    Cancel
                                </Link>

                                <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="form.processing">Updating...</span>
                                    <span v-else>Update Teacher</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone (Admin Only) -->
                <div v-if="user.role === 'admin'" class="bg-red-50 border border-red-200 rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Danger Zone</h3>
                    <p class="text-red-600 mb-4">
                        Permanently delete this teacher account and all associated data. This action cannot be undone.
                    </p>
                    <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Teacher Account
                    </button>
                </div>
        </div>
    </DashboardLayout>
</template>