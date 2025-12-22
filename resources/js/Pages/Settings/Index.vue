<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
});
const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const form = ref({
    theme: 'light',
    notifications: true,
    school_name: 'StudySeco',
    logo: null,
    favicon: null,
});

const logoPreview = ref(null);
const faviconPreview = ref(null);

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleFaviconUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.favicon = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            faviconPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const saveSettings = () => {
    // Here you would implement the save functionality
    console.log('Saving settings:', form.value);
    // Example: form.post('/settings', { ... })
};
</script>

<template>
    <Head title="Settings" />
    
    <DashboardLayout title="System Settings" subtitle="Customize your experience">
        <!-- Settings Navigation Cards -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Frontend Content Management -->
            <Link href="/admin/site-content" v-if="user.role === 'admin'" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Frontend Content</h3>
                        <p class="text-sm text-slate-500">Manage website content</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Manage homepage content, student stories, and dynamic site content.</p>
            </Link>

            <!-- Payment Settings -->
            <Link href="/admin/payment-settings" v-if="user.role === 'admin'" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-green-500 to-teal-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Payment Settings</h3>
                        <p class="text-sm text-slate-500">Configure payments</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Manage payment methods, pricing, and access duration settings.</p>
            </Link>

            <!-- Enrollment Management -->
            <Link href="/admin/enrollments" v-if="user.role === 'admin'" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0-7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Enrollments</h3>
                        <p class="text-sm text-slate-500">Manage student enrollments</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Review, approve, and manage student enrollment applications.</p>
            </Link>

            <!-- User Management -->
            <Link href="/students" v-if="user.role === 'admin'" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6-12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">User Management</h3>
                        <p class="text-sm text-slate-500">Manage users and roles</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Manage students, teachers, and administrative users.</p>
            </Link>

            <!-- Subject Management -->
            <Link href="/subjects" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Subject Management</h3>
                        <p class="text-sm text-slate-500">Manage academic subjects</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Create and manage subjects, topics, and lesson content.</p>
            </Link>

            <!-- Advanced System Settings -->
            <Link href="/admin/system-settings" v-if="user.role === 'admin'" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">🔧 System Settings</h3>
                        <p class="text-sm text-slate-500">Advanced configuration</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">⭐ Email/phone verification toggles, favicon upload, and comprehensive system settings.</p>
            </Link>

            <!-- Reports & Analytics -->
            <Link href="/reports" class="group bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Reports & Analytics</h3>
                        <p class="text-sm text-slate-500">View system analytics</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">View enrollment statistics, payment reports, and system analytics.</p>
            </Link>
        </div>

        <!-- System Branding Settings -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8" v-if="user.role === 'admin'">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">🏢 System Branding & Identity</h2>
            <form @submit.prevent="saveSettings">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- School Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">School/System Name</label>
                        <input 
                            v-model="form.school_name" 
                            type="text" 
                            placeholder="StudySeco"
                            class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        >
                        <p class="text-xs text-slate-500 mt-1">This appears in the sidebar and throughout the system</p>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">System Logo</label>
                        <div class="flex items-center space-x-4">
                            <!-- Current/Preview Logo -->
                            <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center overflow-hidden">
                                <img v-if="logoPreview" :src="logoPreview" alt="Logo Preview" class="w-full h-full object-cover">
                                <svg v-else class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                                </svg>
                            </div>
                            
                            <!-- Upload Button -->
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    @change="handleLogoUpload"
                                    accept="image/*"
                                    class="hidden" 
                                    id="logo-upload"
                                >
                                <label for="logo-upload" class="cursor-pointer bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-xl text-sm font-medium text-slate-700 transition-colors">
                                    Choose Logo File
                                </label>
                                <p class="text-xs text-slate-500 mt-1">PNG, JPG up to 2MB. Recommended: 64x64px</p>
                            </div>
                        </div>
                    </div>

                    <!-- Favicon Upload -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Website Favicon</label>
                        <div class="flex items-center space-x-4">
                            <!-- Current/Preview Favicon -->
                            <div class="w-16 h-16 bg-slate-100 border-2 border-slate-200 rounded-xl flex items-center justify-center overflow-hidden">
                                <img v-if="faviconPreview" :src="faviconPreview" alt="Favicon Preview" class="w-8 h-8 object-cover">
                                <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Upload Button -->
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    @change="handleFaviconUpload"
                                    accept="image/x-icon,image/png,image/jpeg,image/svg+xml"
                                    class="hidden" 
                                    id="favicon-upload"
                                >
                                <label for="favicon-upload" class="cursor-pointer bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-xl text-sm font-medium text-slate-700 transition-colors">
                                    Choose Favicon File
                                </label>
                                <p class="text-xs text-slate-500 mt-1">ICO, PNG, JPG, SVG up to 1MB. Recommended: 32x32px</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        💾 Save Branding Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Personal Settings -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">👤 Personal Settings</h2>
            <form @submit.prevent="saveSettings">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Theme Preference</label>
                        <select v-model="form.theme" class="w-full bg-slate-100/70 px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="light">🌞 Light Mode</option>
                            <option value="dark">🌙 Dark Mode</option>
                            <option value="auto">🔄 Auto (Follow System)</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <input 
                            v-model="form.notifications" 
                            type="checkbox" 
                            id="notifications"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="notifications" class="text-sm font-medium text-slate-700">
                            🔔 Enable Push Notifications
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-2xl font-semibold hover:from-green-600 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        ✅ Save Personal Settings
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>