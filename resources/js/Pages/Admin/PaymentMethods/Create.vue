<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const requirementsList = ref([]);

const form = useForm({
    name: '',
    code: '',
    type: 'mobile_money',
    region: 'malawi',
    currency: 'MWK',
    instructions: '',
    requirements: [],
    is_active: true,
    sort_order: 1
});

const addRequirement = () => {
    requirementsList.value.push('');
};

const removeRequirement = (index) => {
    requirementsList.value.splice(index, 1);
    updateRequirements();
};

const updateRequirements = () => {
    form.requirements = requirementsList.value.filter(req => req.trim() !== '');
};

const submit = () => {
    updateRequirements();
    form.post(route('admin.payment-methods.store'));
};

// Add initial requirement
addRequirement();
</script>

<template>
    <Head title="Add Payment Method" />

    <DashboardLayout 
        title="Add Payment Method" 
        subtitle="Create a new payment method for enrollments">

        <div class="max-w-4xl mx-auto">
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Payment Method Details</h2>
                    <p class="text-sm text-slate-500 mt-1">Fill in the information for the new payment method</p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                                Payment Method Name *
                            </label>
                            <input 
                                id="name"
                                v-model="form.name" 
                                type="text" 
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                placeholder="e.g. TNM Mpamba"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                                Code *
                            </label>
                            <input 
                                id="code"
                                v-model="form.code" 
                                type="text" 
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                placeholder="e.g. tnm_mpamba"
                            />
                            <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-slate-700 mb-2">
                                Type *
                            </label>
                            <select 
                                id="type"
                                v-model="form.type" 
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            >
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="remittance">Remittance</option>
                                <option value="digital_wallet">Digital Wallet</option>
                            </select>
                            <div v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</div>
                        </div>

                        <div>
                            <label for="region" class="block text-sm font-medium text-slate-700 mb-2">
                                Region *
                            </label>
                            <select 
                                id="region"
                                v-model="form.region" 
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            >
                                <option value="malawi">Malawi</option>
                                <option value="south_africa">South Africa</option>
                                <option value="international">International</option>
                            </select>
                            <div v-if="form.errors.region" class="mt-1 text-sm text-red-600">{{ form.errors.region }}</div>
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-slate-700 mb-2">
                                Currency *
                            </label>
                            <select 
                                id="currency"
                                v-model="form.currency" 
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            >
                                <option value="MWK">MWK - Malawi Kwacha</option>
                                <option value="ZAR">ZAR - South African Rand</option>
                                <option value="USD">USD - US Dollar</option>
                            </select>
                            <div v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</div>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-2">
                                Sort Order *
                            </label>
                            <input 
                                id="sort_order"
                                v-model="form.sort_order" 
                                type="number" 
                                min="1"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            />
                            <div v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</div>
                        </div>
                    </div>

                    <div>
                        <label for="instructions" class="block text-sm font-medium text-slate-700 mb-2">
                            Payment Instructions *
                        </label>
                        <textarea 
                            id="instructions"
                            v-model="form.instructions" 
                            rows="4"
                            required
                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            placeholder="Detailed instructions for users on how to make payments using this method..."
                        ></textarea>
                        <div v-if="form.errors.instructions" class="mt-1 text-sm text-red-600">{{ form.errors.instructions }}</div>
                    </div>

                    <!-- Requirements -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Requirements
                        </label>
                        <div class="space-y-3">
                            <div v-for="(requirement, index) in requirementsList" :key="index" class="flex items-center space-x-2">
                                <input 
                                    v-model="requirementsList[index]"
                                    type="text" 
                                    class="flex-1 bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                                    placeholder="e.g. reference_number, screenshot"
                                />
                                <button 
                                    type="button"
                                    @click="removeRequirement(index)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <button 
                                type="button"
                                @click="addRequirement"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                            >
                                + Add Requirement
                            </button>
                        </div>
                        <div v-if="form.errors.requirements" class="mt-1 text-sm text-red-600">{{ form.errors.requirements }}</div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input 
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox" 
                            class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500"
                        />
                        <label for="is_active" class="ml-2 text-sm font-medium text-slate-700">
                            Active (Available for enrollments)
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-slate-200/50">
                        <Link :href="route('admin.payment-methods.index')" 
                              class="px-6 py-3 text-sm font-medium text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-2xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Payment Method' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>