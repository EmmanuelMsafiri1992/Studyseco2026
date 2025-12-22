<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    paymentMethods: Object,
    stats: Object
});

const showDeleteModal = ref(false);
const methodToDelete = ref(null);

const deleteForm = useForm({});

const confirmDelete = (method) => {
    methodToDelete.value = method;
    showDeleteModal.value = true;
};

const deleteMethod = () => {
    if (methodToDelete.value) {
        deleteForm.delete(route('admin.payment-methods.destroy', methodToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                methodToDelete.value = null;
            }
        });
    }
};

const toggleStatus = (method) => {
    router.post(route('admin.payment-methods.toggle', method.id));
};

const getRegionBadge = (region) => {
    const badges = {
        'malawi': 'bg-green-100 text-green-800',
        'south_africa': 'bg-blue-100 text-blue-800',
        'international': 'bg-purple-100 text-purple-800'
    };
    return badges[region] || 'bg-gray-100 text-gray-800';
};

const getTypeBadge = (type) => {
    const badges = {
        'mobile_money': 'bg-yellow-100 text-yellow-800',
        'bank': 'bg-green-100 text-green-800',
        'remittance': 'bg-blue-100 text-blue-800',
        'digital_wallet': 'bg-purple-100 text-purple-800'
    };
    return badges[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Payment Methods" />

    <DashboardLayout 
        title="Payment Methods" 
        subtitle="Manage payment methods for enrollments"
        :stats="stats">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-indigo-500 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Total Methods</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.total }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-green-500 text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Active</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.active }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-green-600 text-white">
                        <span class="text-lg font-bold">MW</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">Malawi</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.malawi }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-blue-600 text-white">
                        <span class="text-lg font-bold">ZA</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">South Africa</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.south_africa }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex items-center">
                    <div class="p-3 rounded-2xl bg-purple-600 text-white">
                        <span class="text-lg font-bold">🌍</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-slate-600">International</h3>
                        <p class="text-2xl font-bold text-slate-900">{{ stats.international }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">All Payment Methods</h2>
            <Link :href="route('admin.payment-methods.create')" 
                  class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Add Payment Method</span>
                </div>
            </Link>
        </div>

        <!-- Payment Methods Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Method</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Region</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Currency</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/50">
                        <tr v-for="method in paymentMethods.data" :key="method.id" class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <span class="text-2xl">{{ method.icon }}</span>
                                    <div>
                                        <div class="font-medium text-slate-900">{{ method.name }}</div>
                                        <div class="text-sm text-slate-500">{{ method.code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getRegionBadge(method.region)" class="px-3 py-1 text-xs font-semibold rounded-full capitalize">
                                    {{ method.region.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getTypeBadge(method.type)" class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ method.type.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ method.currency }}</td>
                            <td class="px-6 py-4">
                                <button @click="toggleStatus(method)" 
                                        :class="method.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                        class="px-3 py-1 text-xs font-semibold rounded-full transition-colors hover:opacity-80">
                                    {{ method.is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('admin.payment-methods.show', method.id)" 
                                          class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        View
                                    </Link>
                                    <Link :href="route('admin.payment-methods.edit', method.id)" 
                                          class="text-amber-600 hover:text-amber-800 text-sm font-medium">
                                        Edit
                                    </Link>
                                    <button @click="confirmDelete(method)" 
                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="paymentMethods.links" class="px-6 py-4 border-t border-slate-200/50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">
                        Showing {{ paymentMethods.from }} to {{ paymentMethods.to }} of {{ paymentMethods.total }} results
                    </p>
                    <div class="flex items-center space-x-2">
                        <template v-for="link in paymentMethods.links" :key="link.label">
                            <Link v-if="link.url" 
                                  :href="link.url" 
                                  v-html="link.label"
                                  :class="link.active 
                                      ? 'px-3 py-2 text-sm bg-indigo-500 text-white rounded-lg' 
                                      : 'px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors duration-150'"
                                  class="transition-colors duration-150">
                            </Link>
                            <span v-else 
                                  v-html="link.label"
                                  class="px-3 py-2 text-sm text-slate-400 rounded-lg cursor-not-allowed">
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Payment Method</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete "{{ methodToDelete?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-4">
                    <button @click="showDeleteModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button @click="deleteMethod" 
                            :disabled="deleteForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>