<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    subjects: Array
});

const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

// Use subjects from props or empty array
const allSubjects = ref(props.subjects || []);

const searchQuery = ref('');
const selectedDepartment = ref('all');

const departmentOptions = computed(() => {
    const departments = new Set(allSubjects.value.map(s => s.department));
    return ['all', ...departments];
});

const filteredSubjects = computed(() => {
    return allSubjects.value.filter(subject => {
        const matchesSearch = subject.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             subject.code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             (subject.description && subject.description.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
                             (subject.teacher_name && subject.teacher_name.toLowerCase().includes(searchQuery.value.toLowerCase()));
        const matchesDepartment = selectedDepartment.value === 'all' || subject.department === selectedDepartment.value;
        return matchesSearch && matchesDepartment;
    });
});

const deleteSubject = (subject) => {
    if (confirm(`Are you sure you want to delete "${subject.name}"? This action cannot be undone and will permanently remove the subject and all its associated topics and lessons.`)) {
        router.delete(route('subjects.destroy', subject.id), {
            onSuccess: () => {
                // Remove the subject from the local array to update the UI
                const index = allSubjects.value.findIndex(s => s.id === subject.id);
                if (index !== -1) {
                    allSubjects.value.splice(index, 1);
                }
            },
            onError: (errors) => {
                console.error('Failed to delete subject:', errors);
                alert('Failed to delete subject. Please try again.');
            }
        });
    }
};
</script>

<template>
    <Head title="Academics" />
    
    <DashboardLayout title="Academics Management" subtitle="Explore and manage all subjects">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <input v-model="searchQuery" type="text" placeholder="Search subjects..." class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <select v-model="selectedDepartment" class="bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white appearance-none">
                                <option value="all">All Departments</option>
                                <option v-for="department in departmentOptions.slice(1)" :key="department" :value="department">{{ department }}</option>
                            </select>
                            <Link v-if="user?.role === 'admin'" :href="route('subjects.create')" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Add Subject</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800">All Subjects</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ filteredSubjects.length }} subjects found</p>

                    <div v-if="filteredSubjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="subject in filteredSubjects" :key="subject.id" class="bg-white rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden flex flex-col transition-all duration-300 hover:shadow-2xl hover:scale-105">
                            <div class="relative h-40 bg-gray-100">
                                <img :src="subject.cover_image ? `/storage/${subject.cover_image}` : 'https://images.unsplash.com/photo-1542435503-956c469947f6?auto=format&fit=crop&q=80&w=300&h=160&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'" alt="Subject Thumbnail" class="object-cover w-full h-full">
                                <div class="absolute bottom-0 left-0 bg-gradient-to-t from-black/60 to-transparent w-full h-1/2"></div>
                                <div class="absolute bottom-4 left-4 text-white">
                                    <h3 class="text-xl font-bold">{{ subject.name }}</h3>
                                    <p class="text-sm font-medium opacity-80">{{ subject.department }} - {{ subject.grade_level }}</p>
                                </div>
                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur text-slate-800 text-xs font-semibold px-3 py-1 rounded-full shadow">
                                    {{ subject.credits }} Credits
                                </div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div class="mb-4">
                                    <p class="text-slate-600 text-sm mb-2 line-clamp-2">{{ subject.description || 'No description available.' }}</p>
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2 text-sm text-slate-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>{{ subject.teacher_name || 'No teacher assigned' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-400">
                                        <span>{{ subject.topic_count || 0 }} topics</span>
                                        <span>{{ subject.lesson_count || 0 }} lessons</span>
                                        <span v-if="subject.total_duration">{{ Math.floor(subject.total_duration / 60) }}h {{ subject.total_duration % 60 }}m</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-slate-200/50">
                                    <!-- Admin/Teacher: Manage Subject Button -->
                                    <Link v-if="user?.role === 'admin' || user?.role === 'teacher'" 
                                          :href="route('subjects.show', subject.id)" 
                                          class="flex items-center space-x-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                        <span>Manage Subject</span>
                                    </Link>
                                    
                                    <!-- Student: Start Learning Button -->
                                    <Link v-else-if="user?.role === 'student'" 
                                          :href="route('subjects.show', subject.id)" 
                                          class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        <span>Start Learning</span>
                                    </Link>
                                    
                                    <!-- Fallback for other roles -->
                                    <Link v-else 
                                          :href="route('subjects.show', subject.id)" 
                                          class="text-slate-600 hover:text-slate-800 text-sm font-medium">View Subject</Link>
                                    <div class="flex items-center space-x-2" v-if="user?.role === 'admin'">
                                        <Link :href="route('subjects.edit', subject.id)" class="p-2 text-amber-600 hover:bg-amber-100 rounded-full transition-colors duration-150">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </Link>
                                        <button @click="deleteSubject(subject)" class="p-2 text-red-600 hover:bg-red-100 rounded-full transition-colors duration-150">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center p-12 bg-white/70 backdrop-blur-sm rounded-3xl shadow-lg border border-slate-200/50">
                        <p class="text-lg font-medium text-slate-500">No subjects found. Try adjusting your search or filters.</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200/50 bg-white/70 backdrop-blur-sm rounded-3xl shadow-xl flex items-center justify-between">
                    <p class="text-sm text-slate-600">Showing 1 to {{ filteredSubjects.length }} of {{ filteredSubjects.length }} results</p>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">Previous</button>
                        <button class="px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded-lg">1</button>
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">2</button>
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">3</button>
                        <button class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150">Next</button>
                    </div>
                </div>

    </DashboardLayout>
</template>
