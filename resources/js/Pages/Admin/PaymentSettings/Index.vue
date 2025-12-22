<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    paymentMethods: Array,
    accessDurations: Array,
    auth: Object,
});

// Payment Method Management
const showMethodModal = ref(false);
const showDurationModal = ref(false);
const editingMethod = ref(null);
const editingDuration = ref(null);

const methodForm = useForm({
    name: '',
    key: '',
    type: '',
    instructions: '',
    config: {},
    is_active: true
});

const durationForm = useForm({
    name: '',
    description: '',
    days: 30,
    price: 0,
    is_active: true
});

// Payment Method Functions
const openMethodModal = (method = null) => {
    editingMethod.value = method;
    if (method) {
        methodForm.name = method.name;
        methodForm.key = method.key;
        methodForm.type = method.type;
        methodForm.instructions = method.instructions || '';
        methodForm.config = { ...method.config };
        methodForm.is_active = method.is_active;
    } else {
        methodForm.reset();
        methodForm.is_active = true;
    }
    showMethodModal.value = true;
};

const saveMethod = () => {
    if (editingMethod.value) {
        methodForm.put(route('admin.payment-settings.payment-methods.update', editingMethod.value.id), {
            onSuccess: () => {
                showMethodModal.value = false;
                editingMethod.value = null;
            }
        });
    } else {
        methodForm.post(route('admin.payment-settings.payment-methods.store'), {
            onSuccess: () => {
                showMethodModal.value = false;
            }
        });
    }
};

const deleteMethod = (method) => {
    if (confirm(`Are you sure you want to delete "${method.name}"?`)) {
        useForm().delete(route('admin.payment-settings.payment-methods.destroy', method.id));
    }
};

// Access Duration Functions
const openDurationModal = (duration = null) => {
    editingDuration.value = duration;
    if (duration) {
        durationForm.name = duration.name;
        durationForm.description = duration.description || '';
        durationForm.days = duration.days;
        durationForm.price = parseFloat(duration.price);
        durationForm.is_active = duration.is_active;
    } else {
        durationForm.reset();
        durationForm.days = 30;
        durationForm.price = 0;
        durationForm.is_active = true;
    }
    showDurationModal.value = true;
};

const saveDuration = () => {
    if (editingDuration.value) {
        durationForm.put(route('admin.payment-settings.access-durations.update', editingDuration.value.id), {
            onSuccess: () => {
                showDurationModal.value = false;
                editingDuration.value = null;
            }
        });
    } else {
        durationForm.post(route('admin.payment-settings.access-durations.store'), {
            onSuccess: () => {
                showDurationModal.value = false;
            }
        });
    }
};

const deleteDuration = (duration) => {
    if (confirm(`Are you sure you want to delete "${duration.name}"?`)) {
        useForm().delete(route('admin.payment-settings.access-durations.destroy', duration.id));
    }
};

const onTypeChange = () => {
    methodForm.config = {};
};
</script>

<template>
    <DashboardLayout 
        title="Payment Settings" 
        subtitle="Configure payment methods and pricing options">
        
        <!-- Payment Methods Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Payment Methods</h2>
                    <p class="text-sm text-slate-500">Manage available payment options for students</p>
                </div>
                <button @click="openMethodModal()" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Payment Method
                    </span>
                </button>
            </div>

            <div v-if="paymentMethods.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No payment methods found</h3>
                <p class="mt-1 text-sm text-slate-500">Get started by adding your first payment method.</p>
            </div>

            <div v-else class="overflow-hidden border border-slate-200/50 rounded-2xl">
                <table class="min-w-full divide-y divide-slate-200/50">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200/30">
                        <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">💳</span>
                                    <span class="text-sm font-medium text-slate-900">{{ method.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-600">{{ method.key }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-900 capitalize">{{ method.type.replace('_', ' ') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="method.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200'" 
                                      class="inline-flex px-3 py-1 text-xs font-semibold rounded-full border">
                                    {{ method.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <button @click="openMethodModal(method)" 
                                        class="text-indigo-600 hover:text-indigo-800 transition-colors duration-150">Edit</button>
                                <button @click="deleteMethod(method)" 
                                        class="text-red-600 hover:text-red-800 transition-colors duration-150">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Access Duration Options Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Access Duration Options</h2>
                    <p class="text-sm text-slate-500">Set pricing and duration options for student access</p>
                </div>
                <button @click="openDurationModal()" 
                        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Duration Option
                    </span>
                </button>
            </div>

            <div v-if="accessDurations.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900">No duration options found</h3>
                <p class="mt-1 text-sm text-slate-500">Create pricing tiers and access duration options.</p>
            </div>

            <div v-else class="overflow-hidden border border-slate-200/50 rounded-2xl">
                <table class="min-w-full divide-y divide-slate-200/50">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Price (MWK)</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-slate-200/30">
                        <tr v-for="duration in accessDurations" :key="duration.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">⏱️</span>
                                    <span class="text-sm font-medium text-slate-900">{{ duration.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-600">{{ duration.days }} day{{ duration.days !== 1 ? 's' : '' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-slate-900">{{ parseFloat(duration.price).toLocaleString() }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="duration.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200'" 
                                      class="inline-flex px-3 py-1 text-xs font-semibold rounded-full border">
                                    {{ duration.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <button @click="openDurationModal(duration)" 
                                        class="text-indigo-600 hover:text-indigo-800 transition-colors duration-150">Edit</button>
                                <button @click="deleteDuration(duration)" 
                                        class="text-red-600 hover:text-red-800 transition-colors duration-150">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Method Modal -->
        <div v-if="showMethodModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 w-full max-w-2xl mx-4 shadow-2xl border border-slate-200/50">
                <h3 class="text-lg font-semibold mb-4 text-slate-800">{{ editingMethod ? 'Edit Payment Method' : 'Add New Payment Method' }}</h3>
                
                <form @submit.prevent="saveMethod" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input v-model="methodForm.name" type="text" required 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200"
                               placeholder="e.g., TNM Mpamba">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Key</label>
                        <input v-model="methodForm.key" type="text" required 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200"
                               placeholder="e.g., tnm_mpamba">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select v-model="methodForm.type" @change="onTypeChange" required 
                                class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200">
                            <option value="">Select type</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <!-- Mobile Money Config -->
                    <div v-if="methodForm.type === 'mobile_money'" class="space-y-3 p-4 bg-blue-50/80 backdrop-blur-sm rounded-2xl border border-blue-200/50">
                        <h4 class="font-medium text-blue-900">Mobile Money Configuration</h4>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input v-model="methodForm.config.phone_number" type="text" 
                                   class="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                   placeholder="e.g., +265123456789">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Merchant Code (Optional)</label>
                            <input v-model="methodForm.config.merchant_code" type="text" 
                                   class="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                   placeholder="e.g., 12345">
                        </div>
                    </div>

                    <!-- Bank Transfer Config -->
                    <div v-if="methodForm.type === 'bank_transfer'" class="space-y-3 p-4 bg-emerald-50/80 backdrop-blur-sm rounded-2xl border border-emerald-200/50">
                        <h4 class="font-medium text-emerald-900">Bank Transfer Configuration</h4>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Bank Name</label>
                            <input v-model="methodForm.config.bank_name" type="text" 
                                   class="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                   placeholder="e.g., National Bank of Malawi">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Account Name</label>
                            <input v-model="methodForm.config.account_name" type="text" 
                                   class="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                   placeholder="e.g., StudySeco School">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Account Number</label>
                            <input v-model="methodForm.config.account_number" type="text" 
                                   class="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                   placeholder="e.g., 1234567890">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Branch (Optional)</label>
                            <input v-model="methodForm.config.branch" type="text" 
                                   class="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                   placeholder="e.g., Blantyre Branch">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Instructions</label>
                        <textarea v-model="methodForm.instructions" rows="3" 
                                  class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200"
                                  placeholder="Payment instructions for students"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input v-model="methodForm.is_active" type="checkbox" id="method_active" class="mr-2 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="method_active" class="text-sm text-slate-700">Active</label>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="showMethodModal = false" 
                                class="px-6 py-3 border border-slate-300 rounded-2xl text-slate-700 hover:bg-slate-50 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="methodForm.processing"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-blue-600 hover:to-purple-700 disabled:opacity-50 transition-all duration-200">
                            {{ methodForm.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Access Duration Modal -->
        <div v-if="showDurationModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 w-full max-w-2xl mx-4 shadow-2xl border border-slate-200/50">
                <h3 class="text-lg font-semibold mb-4 text-slate-800">{{ editingDuration ? 'Edit Access Duration' : 'Add New Access Duration' }}</h3>
                
                <form @submit.prevent="saveDuration" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input v-model="durationForm.name" type="text" required 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200"
                               placeholder="e.g., 1 Month Access">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <input v-model="durationForm.description" type="text" 
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200"
                               placeholder="Optional description">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Duration (Days)</label>
                            <input v-model.number="durationForm.days" type="number" min="1" required 
                                   class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Price (MWK)</label>
                            <input v-model.number="durationForm.price" type="number" step="0.01" min="0" required 
                                   class="w-full bg-slate-50/70 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all duration-200">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input v-model="durationForm.is_active" type="checkbox" id="duration_active" class="mr-2 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="duration_active" class="text-sm text-slate-700">Active</label>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="showDurationModal = false" 
                                class="px-6 py-3 border border-slate-300 rounded-2xl text-slate-700 hover:bg-slate-50 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="durationForm.processing"
                                class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-semibold hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50 transition-all duration-200">
                            {{ durationForm.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>