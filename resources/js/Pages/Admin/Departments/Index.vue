<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    departments: Array,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const deleteDepartment = (department) => {
    if (confirm(`Are you sure you want to delete "${department.name}" department? This action cannot be undone.`)) {
        router.delete(route('admin.departments.destroy', department.id), {
            onError: (errors) => {
                console.error('Failed to delete department:', errors);
                alert('Failed to delete department. Please try again.');
            }
        });
    }
};
</script>

<template>
    <Head title="Department Management" />
    
    <DashboardLayout title="Department Management" subtitle="Manage academic departments and their settings">
        <div class="space-y-6">
            <!-- Header with Add Button -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Departments</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ departments.length }} departments configured</p>
                </div>
                <Link :href="route('admin.departments.create')" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Add Department</span>
                    </div>
                </Link>
            </div>

            <!-- Departments Grid -->
            <div v-if="departments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="department in departments" :key="department.id" class="bg-white rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-105">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                                    <span class="text-indigo-600 font-bold text-lg">{{ department.code }}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">{{ department.name }}</h3>
                                    <p class="text-sm text-slate-500">{{ department.code }}</p>
                                </div>
                            </div>
                            <div :class="['w-3 h-3 rounded-full', department.is_active ? 'bg-green-400' : 'bg-red-400']"></div>
                        </div>
                        
                        <p class="text-slate-600 text-sm mb-4 line-clamp-2">{{ department.description || 'No description provided.' }}</p>
                        
                        <div class="flex items-center text-xs text-slate-400 mb-4">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ department.head_of_department || 'No head assigned' }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200/50">
                            <Link :href="route('admin.departments.show', department.id)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors duration-200">
                                View Details
                            </Link>
                            <div class="flex items-center space-x-2">
                                <Link :href="route('admin.departments.edit', department.id)" class="p-2 text-amber-600 hover:bg-amber-100 rounded-full transition-colors duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </Link>
                                <button @click="deleteDepartment(department)" class="p-2 text-red-600 hover:bg-red-100 rounded-full transition-colors duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center p-12 bg-white/70 backdrop-blur-sm rounded-3xl shadow-lg border border-slate-200/50">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-slate-900">No departments found</h3>
                <p class="mt-2 text-sm text-slate-500">Get started by creating your first academic department.</p>
                <div class="mt-6">
                    <Link :href="route('admin.departments.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Department
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>