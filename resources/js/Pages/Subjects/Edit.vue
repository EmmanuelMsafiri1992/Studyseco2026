<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    subject: Object,
    departments: Array,
});
const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const form = useForm({
    name: props.subject?.name || '',
    code: props.subject?.code || '',
    description: props.subject?.description || '',
    department: props.subject?.department || '',
    grade_level: props.subject?.grade_level || '',
    credits: props.subject?.credits || 1,
    teacher_name: props.subject?.teacher_name || '',
    cover_image: null,
});

const coverImageInput = ref(null);
const photoPreview = ref(null);

const selectNewPhoto = () => {
    coverImageInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = coverImageInput.value.files[0];
    
    if (photo) {
        form.cover_image = photo;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(photo);
    }
};

const submit = () => {
    form.put(route('subjects.update', props.subject.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Form submission successful
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        }
    });
};

// Malawian secondary school grade levels
const gradeLevels = [
    'Form 1',
    'Form 2', 
    'Form 3',
    'Form 4'
];

// Use departments from database or fallback
const departmentOptions = props.departments && props.departments.length > 0 ? props.departments : [
    { name: 'Mathematics' },
    { name: 'Sciences' },
    { name: 'Languages' },
    { name: 'Social Studies' },
    { name: 'Commercial Subjects' },
    { name: 'Technical Subjects' },
    { name: 'Life Skills' },
    { name: 'Arts' },
    { name: 'Physical Education' },
    { name: 'Computer Studies' }
];
</script>

<template>
    <Head title="Edit Subject" />
    
    <DashboardLayout title="Edit Subject" subtitle="Update subject information">
        <div class="space-y-6">
            <!-- Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <form @submit.prevent="submit">
                    <!-- Cover Image Upload -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-slate-700 mb-4">Subject Cover Image</label>
                        <div class="flex items-center space-x-6">
                            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center overflow-hidden">
                                <img v-if="photoPreview" :src="photoPreview" alt="Cover preview" class="w-full h-full object-cover">
                                <img v-else-if="subject.cover_image" :src="`/storage/${subject.cover_image}`" alt="Current cover" class="w-full h-full object-cover">
                                <svg v-else class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <button type="button" @click="selectNewPhoto" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-medium transition-colors duration-200">
                                    Change Image
                                </button>
                                <p class="text-xs text-slate-500 mt-1">JPG, PNG up to 2MB</p>
                            </div>
                        </div>
                        <input ref="coverImageInput" type="file" class="hidden" @change="updatePhotoPreview" accept="image/*">
                        <div v-if="form.errors.cover_image" class="mt-2 text-sm text-red-600">{{ form.errors.cover_image }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Subject Name*</label>
                            <input v-model="form.name" type="text" required 
                                   class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                   placeholder="e.g. Mathematics, Physical Science">
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Subject Code*</label>
                            <input v-model="form.code" type="text" required 
                                   class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                   placeholder="e.g. MATH101, PHYS102">
                            <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Department/Subject Area*</label>
                            <select v-model="form.department" required 
                                    class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option value="">Select Department</option>
                                <option v-for="dept in departmentOptions" :key="dept.name" :value="dept.name">{{ dept.name }}</option>
                            </select>
                            <div v-if="form.errors.department" class="mt-1 text-sm text-red-600">{{ form.errors.department }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Grade Level*</label>
                            <select v-model="form.grade_level" required 
                                    class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option value="">Select Grade Level</option>
                                <option v-for="level in gradeLevels" :key="level" :value="level">{{ level }}</option>
                            </select>
                            <div v-if="form.errors.grade_level" class="mt-1 text-sm text-red-600">{{ form.errors.grade_level }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Credits</label>
                            <input v-model.number="form.credits" type="number" min="1" max="10" 
                                   class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                            <div v-if="form.errors.credits" class="mt-1 text-sm text-red-600">{{ form.errors.credits }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Teacher/Instructor</label>
                            <input v-model="form.teacher_name" type="text" 
                                   class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                   placeholder="e.g. Mr. John Smith">
                            <div v-if="form.errors.teacher_name" class="mt-1 text-sm text-red-600">{{ form.errors.teacher_name }}</div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Subject Description</label>
                        <textarea v-model="form.description" rows="4" 
                                  class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 resize-none"
                                  placeholder="Provide a brief description of this subject, including key topics and learning objectives..."></textarea>
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <Link :href="route('subjects.index')" class="px-6 py-3 bg-slate-100 text-slate-700 rounded-2xl font-semibold hover:bg-slate-200 transition-all duration-200">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 disabled:opacity-50">
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Subject</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>