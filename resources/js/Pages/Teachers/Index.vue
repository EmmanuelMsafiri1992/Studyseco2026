<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    teachers: Object, // Laravel pagination object
    stats: Object,
    filters: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

// Use real data from backend (Laravel pagination)
const teachers = ref((props.teachers?.data || props.teachers) || []);

const searchQuery = ref(props.filters?.search || '');
const selectedDepartment = ref(props.filters?.department || 'all');

// Removed filteredTeachers - using server-side filtering through Laravel controller

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedDepartment.value && selectedDepartment.value !== 'all') params.set('department', selectedDepartment.value);
    
    router.get(route('teachers.index'), Object.fromEntries(params));
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedDepartment.value = 'all';
    router.get(route('teachers.index'));
};

const deleteTeacher = (teacher) => {
    if (confirm(`Are you sure you want to delete ${teacher.name}? This action cannot be undone.`)) {
        router.delete(route('teachers.destroy', teacher.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Remove the teacher from the local array
                const index = teachers.value.findIndex(t => t.id === teacher.id);
                if (index !== -1) {
                    teachers.value.splice(index, 1);
                }
            }
        });
    }
};
</script>

<template>
    <Head title="Faculty Management" />

    <DashboardLayout 
        title="Faculty Management" 
        subtitle="Manage and monitor all teachers in your institution"
        :stats="stats">

        <!-- Search and Filter Bar -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input v-model="searchQuery" @input="applyFilters" type="text" placeholder="Search teachers..." class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                        <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <select v-model="selectedDepartment" @change="applyFilters" class="bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white appearance-none">
                        <option value="all">All Departments</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Science">Science</option>
                        <option value="English">English</option>
                    </select>
                    <button @click="resetFilters" class="text-slate-500 hover:text-slate-700 px-4 py-3 rounded-2xl text-sm transition-colors">
                        Clear Filters
                    </button>
                    <Link href="/teachers/create" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Teacher</span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Teachers Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
            <div class="p-6 border-b border-slate-200/50">
                <h2 class="text-xl font-bold text-slate-800">All Teachers</h2>
                <p class="text-sm text-slate-500 mt-1">{{ teachers?.length || 0 }} teachers found</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Teacher ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Email</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Phone</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/50">
                        <tr v-for="teacher in teachers" :key="teacher.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ teacher.id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {{ teacher.name.charAt(0) }}
                                    </div>
                                    <span class="text-sm font-medium text-slate-800">{{ teacher.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-800">{{ teacher.email }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ teacher.phone || 'Not provided' }}</td>
                            <td class="px-6 py-4">
                                <span :class="teacher.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ teacher.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('teachers.show', teacher.id)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</Link>
                                    <Link :href="route('teachers.edit', teacher.id)" class="text-amber-600 hover:text-amber-800 text-sm font-medium">Edit</Link>
                                    <button @click="deleteTeacher(teacher)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200/50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">Showing {{ (teachers?.length || 0) > 0 ? 1 : 0 }} to {{ teachers?.length || 0 }} of {{ teachers?.length || 0 }} results</p>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">Previous</button>
                        <button class="px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded-lg">1</button>
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">2</button>
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">3</button>
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>