<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    role: Object,
    permissions: Object,
});

const form = useForm({
    name: props.role.name,
    slug: props.role.slug,
    description: props.role.description || '',
    priority: props.role.priority,
    permissions: props.role.permissions?.map(p => p.id) || [],
    is_active: props.role.is_active,
});

const selectedPermissions = ref(new Set());

onMounted(() => {
    // Initialize selected permissions
    if (props.role.permissions) {
        props.role.permissions.forEach(permission => {
            selectedPermissions.value.add(permission.id);
        });
    }
});

// Generate slug from name
const generateSlug = () => {
    form.slug = form.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

// Toggle permission selection
const togglePermission = (permissionId) => {
    if (selectedPermissions.value.has(permissionId)) {
        selectedPermissions.value.delete(permissionId);
    } else {
        selectedPermissions.value.add(permissionId);
    }
    form.permissions = Array.from(selectedPermissions.value);
};

// Toggle all permissions in a category
const toggleCategory = (category) => {
    const categoryPermissions = props.permissions[category] || [];
    const allSelected = categoryPermissions.every(p => selectedPermissions.value.has(p.id));
    
    categoryPermissions.forEach(permission => {
        if (allSelected) {
            selectedPermissions.value.delete(permission.id);
        } else {
            selectedPermissions.value.add(permission.id);
        }
    });
    
    form.permissions = Array.from(selectedPermissions.value);
};

const submit = () => {
    form.put(route('admin.roles.update', props.role.id));
};

const isSystemRole = ['super-admin', 'admin', 'teacher', 'student'].includes(props.role.slug);
</script>

<template>
    <Head :title="`Edit Role: ${role.name}`" />

    <DashboardLayout 
        :title="`Edit Role: ${role.name}`" 
        subtitle="Update role information and permissions">

        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <div class="mb-6">
                <Link :href="route('admin.roles.show', role.id)" class="inline-flex items-center space-x-2 text-slate-600 hover:text-slate-800 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to Role</span>
                </Link>
            </div>

            <!-- System Role Warning -->
            <div v-if="isSystemRole" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span class="text-amber-800 font-medium">System Role</span>
                </div>
                <p class="text-amber-700 text-sm mt-1">This is a system role. Some fields may be restricted from editing to maintain system integrity.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Basic Information -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Role Name</label>
                            <input 
                                id="name"
                                v-model="form.name" 
                                @input="!isSystemRole && generateSlug()"
                                type="text" 
                                required
                                :disabled="isSystemRole"
                                class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 disabled:opacity-50"
                                placeholder="e.g., Content Manager">
                            <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-semibold text-slate-700 mb-2">Slug</label>
                            <input 
                                id="slug"
                                v-model="form.slug" 
                                type="text" 
                                required
                                :disabled="isSystemRole"
                                class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 disabled:opacity-50"
                                placeholder="e.g., content-manager">
                            <div v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                            <textarea 
                                id="description"
                                v-model="form.description" 
                                rows="3"
                                class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                placeholder="Describe the role and its purpose..."></textarea>
                            <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</div>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-semibold text-slate-700 mb-2">Priority (1-100)</label>
                            <input 
                                id="priority"
                                v-model.number="form.priority" 
                                type="number" 
                                min="1" 
                                max="100"
                                required
                                class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                            <div v-if="form.errors.priority" class="mt-2 text-sm text-red-600">{{ form.errors.priority }}</div>
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input 
                                    v-model="form.is_active" 
                                    type="checkbox" 
                                    :disabled="isSystemRole"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-400 disabled:opacity-50">
                                <span class="text-sm font-semibold text-slate-700">Role is Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800 mb-6">Permissions</h2>
                    
                    <div class="space-y-6">
                        <div v-for="(categoryPermissions, category) in permissions" :key="category" class="border border-slate-200 rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-slate-800 capitalize">{{ category }}</h3>
                                <button 
                                    type="button"
                                    @click="toggleCategory(category)"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ categoryPermissions.every(p => selectedPermissions.has(p.id)) ? 'Deselect All' : 'Select All' }}
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <label 
                                    v-for="permission in categoryPermissions" 
                                    :key="permission.id"
                                    class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-50 transition-colors duration-150 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        :checked="selectedPermissions.has(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-400">
                                    <div>
                                        <div class="text-sm font-medium text-slate-800">{{ permission.name }}</div>
                                        <div v-if="permission.description" class="text-xs text-slate-500">{{ permission.description }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div v-if="form.errors.permissions" class="mt-4 text-sm text-red-600">{{ form.errors.permissions }}</div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.roles.show', role.id)" class="px-6 py-3 border border-slate-300 text-slate-700 rounded-2xl hover:bg-slate-50 transition-colors duration-200">
                        Cancel
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 disabled:opacity-50 disabled:transform-none">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Role</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>