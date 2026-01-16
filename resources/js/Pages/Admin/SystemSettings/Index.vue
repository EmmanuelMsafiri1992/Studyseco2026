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

                    <!-- Cloud Video Storage Settings -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Cloud Video Storage</h2>
                                <p class="text-sm text-gray-600 mt-1">Configure cloud storage for faster video streaming</p>
                            </div>
                            <div class="flex items-center">
                                <span :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                                    formData.cloud_storage_provider !== 'local' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                ]">
                                    <span :class="[
                                        'w-2 h-2 rounded-full mr-2',
                                        formData.cloud_storage_provider !== 'local' ? 'bg-green-500' : 'bg-gray-400'
                                    ]"></span>
                                    {{ storageProviderLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Storage Provider Selection -->
                            <div class="p-4 bg-slate-50 rounded-2xl">
                                <label class="block text-sm font-medium text-gray-900 mb-3">Storage Provider</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label :class="[
                                        'relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all',
                                        formData.cloud_storage_provider === 'local' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'
                                    ]">
                                        <input type="radio" v-model="formData.cloud_storage_provider" value="local" class="sr-only">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                            <div>
                                                <p class="font-medium text-gray-900">Local Storage</p>
                                                <p class="text-xs text-gray-500">Store on server</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label :class="[
                                        'relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all',
                                        formData.cloud_storage_provider === 's3' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'
                                    ]">
                                        <input type="radio" v-model="formData.cloud_storage_provider" value="s3" class="sr-only">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-orange-500 mr-3" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                            <div>
                                                <p class="font-medium text-gray-900">Amazon S3</p>
                                                <p class="text-xs text-gray-500">AWS Cloud Storage</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label :class="[
                                        'relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all',
                                        formData.cloud_storage_provider === 'gcs' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                                    ]">
                                        <input type="radio" v-model="formData.cloud_storage_provider" value="gcs" class="sr-only">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-blue-500 mr-3" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12.19 2.38a9.34 9.34 0 00-9.34 9.34v.1A9.34 9.34 0 1012.19 2.38zm0 17.22a7.88 7.88 0 117.88-7.88 7.88 7.88 0 01-7.88 7.88z"/>
                                                <path d="M12.19 7.41a4.31 4.31 0 104.31 4.31 4.31 4.31 0 00-4.31-4.31zm0 7.16a2.85 2.85 0 112.85-2.85 2.85 2.85 0 01-2.85 2.85z"/>
                                            </svg>
                                            <div>
                                                <p class="font-medium text-gray-900">Google Cloud</p>
                                                <p class="text-xs text-gray-500">GCS Storage</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- S3 Configuration (shown when S3 selected) -->
                            <div v-if="formData.cloud_storage_provider === 's3'" class="space-y-6">
                                <!-- Enable S3 Toggle -->
                                <div class="flex items-center justify-between p-4 bg-orange-50 rounded-2xl">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Enable S3 Storage</label>
                                        <p class="text-xs text-gray-500 mt-1">Activate Amazon S3 for video uploads</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="formData.s3_enabled"
                                            class="sr-only peer"
                                        >
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                    </label>
                                </div>

                            <!-- S3 Configuration Fields -->
                            <div :class="{ 'opacity-50 pointer-events-none': !formData.s3_enabled }">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Access Key ID -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            AWS Access Key ID
                                        </label>
                                        <input
                                            type="text"
                                            v-model="formData.s3_access_key"
                                            placeholder="AKIAIOSFODNN7EXAMPLE"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 font-mono"
                                        />
                                        <p class="text-xs text-gray-500 mt-1">Your AWS IAM user access key</p>
                                    </div>

                                    <!-- Secret Access Key -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            AWS Secret Access Key
                                        </label>
                                        <div class="relative">
                                            <input
                                                :type="showSecretKey ? 'text' : 'password'"
                                                v-model="formData.s3_secret_key"
                                                placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 font-mono pr-10"
                                            />
                                            <button
                                                type="button"
                                                @click="showSecretKey = !showSecretKey"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            >
                                                <svg v-if="showSecretKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                </svg>
                                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Your AWS IAM user secret key</p>
                                    </div>

                                    <!-- Bucket Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            S3 Bucket Name
                                        </label>
                                        <input
                                            type="text"
                                            v-model="formData.s3_bucket"
                                            placeholder="my-video-bucket"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        />
                                        <p class="text-xs text-gray-500 mt-1">The name of your S3 bucket</p>
                                    </div>

                                    <!-- AWS Region -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            AWS Region
                                        </label>
                                        <select
                                            v-model="formData.s3_region"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        >
                                            <option value="us-east-1">US East (N. Virginia) - us-east-1</option>
                                            <option value="us-east-2">US East (Ohio) - us-east-2</option>
                                            <option value="us-west-1">US West (N. California) - us-west-1</option>
                                            <option value="us-west-2">US West (Oregon) - us-west-2</option>
                                            <option value="eu-west-1">EU (Ireland) - eu-west-1</option>
                                            <option value="eu-west-2">EU (London) - eu-west-2</option>
                                            <option value="eu-central-1">EU (Frankfurt) - eu-central-1</option>
                                            <option value="ap-south-1">Asia Pacific (Mumbai) - ap-south-1</option>
                                            <option value="ap-southeast-1">Asia Pacific (Singapore) - ap-southeast-1</option>
                                            <option value="ap-southeast-2">Asia Pacific (Sydney) - ap-southeast-2</option>
                                            <option value="ap-northeast-1">Asia Pacific (Tokyo) - ap-northeast-1</option>
                                            <option value="sa-east-1">South America (Sao Paulo) - sa-east-1</option>
                                            <option value="af-south-1">Africa (Cape Town) - af-south-1</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Select your S3 bucket region</p>
                                    </div>

                                    <!-- CloudFront URL (optional) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            CloudFront URL (Optional)
                                        </label>
                                        <input
                                            type="url"
                                            v-model="formData.s3_url"
                                            placeholder="https://d1234abcd.cloudfront.net"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        />
                                        <p class="text-xs text-gray-500 mt-1">For faster video delivery via CDN</p>
                                    </div>

                                    <!-- Custom Endpoint (optional) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Custom S3 Endpoint (Optional)
                                        </label>
                                        <input
                                            type="url"
                                            v-model="formData.s3_endpoint"
                                            placeholder="https://s3-compatible.example.com"
                                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                        />
                                        <p class="text-xs text-gray-500 mt-1">For S3-compatible services (MinIO, DigitalOcean Spaces, etc.)</p>
                                    </div>
                                </div>

                                <!-- Test Connection Button -->
                                <div class="mt-6 p-4 bg-slate-50 rounded-2xl">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Test S3 Connection</p>
                                            <p class="text-xs text-gray-500 mt-1">Verify your S3 credentials and bucket access</p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="testS3Connection"
                                            :disabled="testingS3 || !formData.s3_access_key || !formData.s3_secret_key || !formData.s3_bucket"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-100 text-indigo-700 rounded-xl font-medium text-sm hover:bg-indigo-200 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <svg v-if="testingS3" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                            </svg>
                                            {{ testingS3 ? 'Testing...' : 'Test Connection' }}
                                        </button>
                                    </div>
                                    <div v-if="s3TestResult" :class="[
                                        'mt-3 p-3 rounded-xl text-sm',
                                        s3TestResult.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                    ]">
                                        <div class="flex items-center">
                                            <svg v-if="s3TestResult.success" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ s3TestResult.message }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            </div>

                            <!-- GCS Configuration (shown when GCS selected) -->
                            <div v-if="formData.cloud_storage_provider === 'gcs'" class="space-y-6">
                                <!-- Enable GCS Toggle -->
                                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-2xl">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Enable Google Cloud Storage</label>
                                        <p class="text-xs text-gray-500 mt-1">Activate GCS for video uploads</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="formData.gcs_enabled"
                                            class="sr-only peer"
                                        >
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </div>

                                <!-- GCS Configuration Fields -->
                                <div :class="{ 'opacity-50 pointer-events-none': !formData.gcs_enabled }">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Project ID -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Google Cloud Project ID
                                            </label>
                                            <input
                                                type="text"
                                                v-model="formData.gcs_project_id"
                                                placeholder="my-project-id"
                                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-200"
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Your GCP project ID</p>
                                        </div>

                                        <!-- Bucket Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                GCS Bucket Name
                                            </label>
                                            <input
                                                type="text"
                                                v-model="formData.gcs_bucket"
                                                placeholder="my-video-bucket"
                                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-200"
                                            />
                                            <p class="text-xs text-gray-500 mt-1">The name of your GCS bucket</p>
                                        </div>

                                        <!-- Path Prefix -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Path Prefix (Optional)
                                            </label>
                                            <input
                                                type="text"
                                                v-model="formData.gcs_path_prefix"
                                                placeholder="videos/"
                                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-200"
                                            />
                                            <p class="text-xs text-gray-500 mt-1">Optional path prefix for files</p>
                                        </div>

                                        <!-- CDN URL -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Cloud CDN URL (Optional)
                                            </label>
                                            <input
                                                type="url"
                                                v-model="formData.gcs_url"
                                                placeholder="https://cdn.example.com"
                                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-200"
                                            />
                                            <p class="text-xs text-gray-500 mt-1">For faster delivery via Cloud CDN</p>
                                        </div>

                                        <!-- Service Account Key JSON -->
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Service Account Key (JSON)
                                            </label>
                                            <textarea
                                                v-model="formData.gcs_key_file"
                                                placeholder='Paste your service account JSON key here...'
                                                rows="6"
                                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-200 font-mono text-xs"
                                            ></textarea>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Download from: GCP Console > IAM & Admin > Service Accounts > Keys > Add Key > JSON
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Test GCS Connection Button -->
                                    <div class="mt-6 p-4 bg-slate-50 rounded-2xl">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Test GCS Connection</p>
                                                <p class="text-xs text-gray-500 mt-1">Verify your GCS credentials and bucket access</p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="testGcsConnection"
                                                :disabled="testingGcs || !formData.gcs_project_id || !formData.gcs_bucket || !formData.gcs_key_file"
                                                class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-xl font-medium text-sm hover:bg-blue-200 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <svg v-if="testingGcs" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                {{ testingGcs ? 'Testing...' : 'Test Connection' }}
                                            </button>
                                        </div>
                                        <div v-if="gcsTestResult" :class="[
                                            'mt-3 p-3 rounded-xl text-sm',
                                            gcsTestResult.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                        ]">
                                            <div class="flex items-center">
                                                <svg v-if="gcsTestResult.success" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ gcsTestResult.message }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div v-if="formData.cloud_storage_provider !== 'local'" class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-blue-700">
                                        <p class="font-medium mb-1">Benefits of Cloud Storage:</p>
                                        <ul class="list-disc list-inside space-y-1 text-blue-600">
                                            <li>Faster video streaming with global CDN integration</li>
                                            <li>Unlimited storage capacity</li>
                                            <li>Reduced server load and bandwidth costs</li>
                                            <li>Better reliability and uptime</li>
                                            <li>Automatic scaling for high traffic</li>
                                        </ul>
                                    </div>
                                </div>
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
const showSecretKey = ref(false);
const testingS3 = ref(false);
const s3TestResult = ref(null);
const testingGcs = ref(false);
const gcsTestResult = ref(null);

// Computed property for storage provider label
const storageProviderLabel = computed(() => {
    switch (formData.value.cloud_storage_provider) {
        case 's3':
            return formData.value.s3_enabled ? 'S3 Active' : 'S3 Selected';
        case 'gcs':
            return formData.value.gcs_enabled ? 'GCS Active' : 'GCS Selected';
        default:
            return 'Local Storage';
    }
});

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

const testS3Connection = async () => {
    testingS3.value = true;
    s3TestResult.value = null;

    try {
        const response = await fetch(route('admin.s3.test'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({
                access_key: formData.value.s3_access_key,
                secret_key: formData.value.s3_secret_key,
                bucket: formData.value.s3_bucket,
                region: formData.value.s3_region,
                endpoint: formData.value.s3_endpoint,
            }),
        });

        const data = await response.json();
        s3TestResult.value = {
            success: data.success,
            message: data.message,
        };
    } catch (error) {
        s3TestResult.value = {
            success: false,
            message: 'Failed to test connection. Please check your network.',
        };
    } finally {
        testingS3.value = false;
    }
};

const testGcsConnection = async () => {
    testingGcs.value = true;
    gcsTestResult.value = null;

    try {
        const response = await fetch(route('admin.gcs.test'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({
                project_id: formData.value.gcs_project_id,
                bucket: formData.value.gcs_bucket,
                key_file: formData.value.gcs_key_file,
            }),
        });

        const data = await response.json();
        gcsTestResult.value = {
            success: data.success,
            message: data.message,
        };
    } catch (error) {
        gcsTestResult.value = {
            success: false,
            message: 'Failed to test connection. Please check your network.',
        };
    } finally {
        testingGcs.value = false;
    }
};
</script>