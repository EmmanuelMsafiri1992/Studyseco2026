<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    subjects: Array,
    paymentMethods: Array,
});
const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('students.store'));
};
</script>

<template>
    <Head title="Add New Student" />
    
    <DashboardLayout title="Add New Student" subtitle="Enter student details below">
        <div class="space-y-6">
            <!-- Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Name *</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Email *</label>
                            <input v-model="form.email" type="email" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Phone</label>
                            <input v-model="form.phone" type="tel" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Password *</label>
                            <input v-model="form.password" type="password" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Confirm Password *</label>
                            <input v-model="form.password_confirmation" type="password" class="mt-1 block w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Note about Student Registration</h3>
                            <p class="text-blue-700 text-sm">
                                This creates a basic student account. Students will complete their enrollment with subjects, payment methods, and other details through the enrollment process on the public website.
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-4">
                        <Link href="/students" class="px-6 py-3 bg-slate-100 text-slate-700 rounded-2xl font-semibold hover:bg-slate-200 transition-all duration-200">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Student</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>