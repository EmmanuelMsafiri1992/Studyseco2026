<template>
    <Head title="System Settings" />
    
    <DashboardLayout
        title="System Settings"
        subtitle="Manage your application's global settings">
        <div class="max-w-7xl mx-auto">
                <!-- Loading State -->
                <div v-if="isLoading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                </div>

                <div v-else>
                <!-- Success/Error Messages -->
                <div v-if="props.flash?.success" class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ props.flash.success }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="props.flash?.error" class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ props.flash.error }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="updateSettings" class="space-y-6">
                    <!-- General Settings -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">General Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="settings.general">
                            <div v-for="setting in settings.general" :key="setting.key">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ setting.name }}
                                </label>
                                <div v-if="setting.type === 'textarea'">
                                    <textarea 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        rows="3"
                                    ></textarea>
                                </div>
                                <div v-else-if="setting.type === 'color'">
                                    <input 
                                        type="color" 
                                        v-model="formData[setting.key]" 
                                        class="w-full h-12 rounded-2xl border-2 border-slate-200 focus:border-indigo-400 transition-all duration-200"
                                    />
                                </div>
                                <div v-else>
                                    <input 
                                        :type="setting.type" 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Contact Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="settings.contact">
                            <div v-for="setting in settings.contact" :key="setting.key">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ setting.name }}
                                </label>
                                <div v-if="setting.type === 'textarea'">
                                    <textarea 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        rows="3"
                                    ></textarea>
                                </div>
                                <div v-else>
                                    <input 
                                        :type="setting.type" 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Appearance</h2>
                        <div class="space-y-6" v-if="settings.appearance">
                            <!-- Logo Upload Section -->
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Logo Upload</h3>
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img v-if="currentLogo" :src="currentLogo" alt="Current logo" class="h-16 w-auto max-w-[200px] object-contain">
                                        <div v-else class="h-16 w-32 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            ref="logoInput"
                                            type="file"
                                            @change="handleLogoUpload"
                                            accept=".png,.jpg,.jpeg,.svg"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">Upload .png, .jpg, .jpeg, or .svg files (max 2MB)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Favicon Upload Section -->
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Favicon Upload</h3>
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img v-if="currentFavicon" :src="currentFavicon" alt="Current favicon" class="w-8 h-8">
                                        <div v-else class="w-8 h-8 bg-gray-200 rounded-sm flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            ref="faviconInput"
                                            type="file"
                                            @change="handleFaviconUpload"
                                            accept=".ico,.png,.jpg,.jpeg"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">Upload .ico, .png, .jpg, or .jpeg files (max 2MB)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Other Appearance Settings -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-for="setting in settings.appearance" :key="setting.key">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ setting.name }}
                                    </label>
                                    <div v-if="setting.type === 'color'">
                                        <input 
                                            type="color" 
                                            v-model="formData[setting.key]" 
                                            class="w-full h-12 rounded-2xl border-2 border-slate-200 focus:border-indigo-400 transition-all duration-200"
                                        />
                                    </div>
                                    <div v-else-if="setting.key !== 'favicon_url' && setting.key !== 'logo_url'">
                                        <input
                                            :type="setting.type"
                                            v-model="formData[setting.key]"
                                            :placeholder="setting.description"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Settings -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Footer Settings</h2>
                        <div class="space-y-6" v-if="settings.footer">
                            <div v-for="setting in settings.footer" :key="setting.key">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ setting.name }}
                                </label>
                                <div v-if="setting.type === 'textarea'">
                                    <textarea 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        rows="3"
                                    ></textarea>
                                </div>
                                <div v-else-if="setting.type === 'json'">
                                    <textarea 
                                        v-model="jsonFields[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        rows="4"
                                    ></textarea>
                                    <p class="text-xs text-gray-500 mt-1">JSON format: [{"name": "Link Name", "url": "/path"}]</p>
                                </div>
                                <div v-else>
                                    <input 
                                        :type="setting.type" 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Settings -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Verification Settings</h2>
                        <p class="text-sm text-gray-600 mb-6">Control email and phone verification requirements for student enrollment</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="settings.verification">
                            <div v-for="setting in settings.verification" :key="setting.key">
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="formData[setting.key]"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm font-medium text-gray-700">{{ setting.name }}</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-2 ml-6">{{ setting.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Settings -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Academic Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="settings.academic">
                            <div v-for="setting in settings.academic" :key="setting.key">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ setting.name }}
                                </label>
                                <div v-if="setting.type === 'json'">
                                    <textarea 
                                        v-model="jsonFields[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        rows="3"
                                    ></textarea>
                                    <p class="text-xs text-gray-500 mt-1">JSON format: ["Form 1", "Form 2", ...]</p>
                                </div>
                                <div v-else>
                                    <input 
                                        :type="setting.type" 
                                        v-model="formData[setting.key]" 
                                        :placeholder="setting.description"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Currency Conversion Settings -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Currency Conversion</h2>
                        <p class="text-sm text-gray-600 mb-6">Configure exchange rates and supported currencies for international payments</p>
                        
                        <div class="space-y-6">
                            <!-- Base Currency -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Base Currency
                                </label>
                                <select 
                                    v-model="formData.base_currency"
                                    class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                >
                                    <option value="MWK">MWK - Malawian Kwacha</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="ZAR">ZAR - South African Rand</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">The primary currency for all transactions</p>
                            </div>

                            <!-- Exchange Rates -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Exchange Rates (per 1 {{ formData.base_currency || 'MWK' }})</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="currency in supportedCurrencies" :key="currency.code">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ currency.code }} - {{ currency.name }}
                                        </label>
                                        <input 
                                            type="number" 
                                            step="0.000001"
                                            v-model="formData[`exchange_rate_${currency.code.toLowerCase()}`]" 
                                            :placeholder="`1 ${formData.base_currency || 'MWK'} = ? ${currency.code}`"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Currency Display Settings -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Currency Symbol Position
                                    </label>
                                    <select 
                                        v-model="formData.currency_symbol_position"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    >
                                        <option value="before">Before amount (MK 100)</option>
                                        <option value="after">After amount (100 MK)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Decimal Places
                                    </label>
                                    <select 
                                        v-model="formData.currency_decimal_places"
                                        class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    >
                                        <option value="0">0 (100)</option>
                                        <option value="2">2 (100.00)</option>
                                        <option value="3">3 (100.000)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Auto Update Settings -->
                            <div>
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="formData.auto_update_exchange_rates"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm font-medium text-gray-700">Auto-update exchange rates daily</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-2 ml-6">Automatically fetch latest exchange rates from external API</p>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            :disabled="processing"
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50"
                        >
                            <span v-if="processing">Saving...</span>
                            <span v-else>Save Settings</span>
                        </button>
                    </div>
                </form>
                </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    },
    flash: Object
});

const processing = ref(false);
const formData = ref({});
const jsonFields = ref({});
const logoInput = ref(null);
const selectedLogo = ref(null);
const currentLogo = ref(null);
const faviconInput = ref(null);
const selectedFavicon = ref(null);
const currentFavicon = ref(null);
const isLoading = ref(true);

// Supported currencies for conversion
const supportedCurrencies = ref([
    { code: 'USD', name: 'US Dollar' },
    { code: 'EUR', name: 'Euro' },
    { code: 'GBP', name: 'British Pound' },
    { code: 'ZAR', name: 'South African Rand' },
    { code: 'KES', name: 'Kenyan Shilling' },
    { code: 'TZS', name: 'Tanzanian Shilling' },
    { code: 'ZMW', name: 'Zambian Kwacha' }
]);

// Computed property to safely access settings
const settings = computed(() => props.settings || {});

onMounted(() => {
    // Initialize form data from settings
    if (props.settings && Object.keys(props.settings).length > 0) {
        Object.keys(props.settings).forEach(group => {
            if (Array.isArray(props.settings[group])) {
                props.settings[group].forEach(setting => {
                    if (setting.type === 'json') {
                        jsonFields.value[setting.key] = JSON.stringify(setting.value, null, 2);
                    } else {
                        formData.value[setting.key] = setting.value;
                    }

                    // Set current logo
                    if (setting.key === 'logo_url' && setting.value) {
                        currentLogo.value = setting.value;
                    }

                    // Set current favicon
                    if (setting.key === 'favicon_url' && setting.value) {
                        currentFavicon.value = setting.value;
                    }
                });
            }
        });
    }

    // Set loading to false after a short delay to ensure render
    setTimeout(() => {
        isLoading.value = false;
    }, 100);
});

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedLogo.value = file;
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            currentLogo.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleFaviconUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        selectedFavicon.value = file;
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            currentFavicon.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const updateSettings = () => {
    processing.value = true;
    
    // Prepare settings data
    const settingsArray = [];
    
    // Add regular form data
    Object.keys(formData.value).forEach(key => {
        settingsArray.push({
            key: key,
            value: formData.value[key]
        });
    });
    
    // Add JSON fields
    Object.keys(jsonFields.value).forEach(key => {
        try {
            const parsedValue = JSON.parse(jsonFields.value[key]);
            settingsArray.push({
                key: key,
                value: parsedValue
            });
        } catch (e) {
            // If JSON is invalid, keep as string
            settingsArray.push({
                key: key,
                value: jsonFields.value[key]
            });
        }
    });

    // Prepare data for submission
    const submitData = {
        settings: settingsArray
    };

    // If logo or favicon is selected, use FormData for file upload
    if (selectedLogo.value || selectedFavicon.value) {
        const formData = new FormData();
        formData.append('settings', JSON.stringify(settingsArray));

        if (selectedLogo.value) {
            formData.append('logo', selectedLogo.value);
        }

        if (selectedFavicon.value) {
            formData.append('favicon', selectedFavicon.value);
        }

        router.post(route('admin.system-settings.update'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onFinish: () => {
                processing.value = false;
                selectedLogo.value = null;
                selectedFavicon.value = null;
            },
            onSuccess: () => {
                // Show success message
            }
        });
    } else {
        // Regular form submission without file
        router.post(route('admin.system-settings.update'), submitData, {
            onFinish: () => {
                processing.value = false;
            },
            onSuccess: () => {
                // Show success message
            }
        });
    }
};
</script>