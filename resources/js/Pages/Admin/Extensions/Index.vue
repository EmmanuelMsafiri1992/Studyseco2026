<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    extensions: Object,
    stats: Object,
    filters: Object,
});

const selectedExtensions = ref([]);
const showBulkActions = computed(() => selectedExtensions.value.length > 0);
const showGrantModal = ref(false);
const showPricingModal = ref(false);

// Forms
const bulkForm = useForm({
    action: '',
    payment_ids: [],
    rejection_reason: '',
});

const grantForm = useForm({
    user_id: '',
    months: 1,
    reason: '',
});

const pricingForm = useForm({
    region: 'malawi',
    pricing: {
        1: 25000,
        2: 50000,
        3: 75000,
        4: 100000,
        6: 137500,
        12: 250000
    }
});

const searchForm = useForm({
    search: props.filters?.search || '',
    status: props.filters?.status || 'all',
});

// Methods
const formatCurrency = (amount, currency) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: currency === 'MWK' ? 0 : 2
    }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-yellow-100 text-yellow-800',
        verified: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const toggleExtension = (extensionId) => {
    const index = selectedExtensions.value.indexOf(extensionId);
    if (index > -1) {
        selectedExtensions.value.splice(index, 1);
    } else {
        selectedExtensions.value.push(extensionId);
    }
};

const selectAllExtensions = () => {
    if (selectedExtensions.value.length === props.extensions.data.length) {
        selectedExtensions.value = [];
    } else {
        selectedExtensions.value = props.extensions.data.map(ext => ext.id);
    }
};

const performBulkAction = (action) => {
    bulkForm.action = action;
    bulkForm.payment_ids = selectedExtensions.value;
    
    if (action === 'approve') {
        if (confirm(`Are you sure you want to approve ${selectedExtensions.value.length} extension(s)?`)) {
            bulkForm.post(route('admin.extensions.bulk-action'), {
                onSuccess: () => {
                    selectedExtensions.value = [];
                }
            });
        }
    } else {
        // Show rejection form
        const reason = prompt('Enter rejection reason:');
        if (reason) {
            bulkForm.rejection_reason = reason;
            bulkForm.post(route('admin.extensions.bulk-action'), {
                onSuccess: () => {
                    selectedExtensions.value = [];
                }
            });
        }
    }
};

const submitSearch = () => {
    router.get(route('admin.extensions.index'), searchForm.data(), {
        preserveState: true,
        replace: true,
    });
};

const grantExtension = () => {
    grantForm.post(route('admin.extensions.grant'), {
        onSuccess: () => {
            showGrantModal.value = false;
            grantForm.reset();
        }
    });
};

const updatePricing = () => {
    pricingForm.post(route('admin.extensions.update-pricing'), {
        onSuccess: () => {
            showPricingModal.value = false;
        }
    });
};

const approveExtension = (payment) => {
    if (confirm(`Approve extension for ${payment.enrollment.name}?`)) {
        router.post(route('admin.extensions.approve', payment.id));
    }
};

const rejectExtension = (payment) => {
    const reason = prompt('Enter rejection reason:');
    if (reason) {
        router.post(route('admin.extensions.reject', payment.id), {
            rejection_reason: reason
        });
    }
};
</script>

<template>
    <Head title="Extension Management" />
    
    <DashboardLayout title="Extension Management" subtitle="Manage student access extensions">
        <div class="space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Extensions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Verified</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.verified }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Rejected</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.rejected }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <!-- Search and Filter -->
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                        <div class="flex space-x-2">
                            <input
                                v-model="searchForm.search"
                                type="text"
                                placeholder="Search by name or enrollment number..."
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @keyup.enter="submitSearch"
                            />
                            <select
                                v-model="searchForm.status"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @change="submitSearch"
                            >
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <button 
                                @click="submitSearch"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                            >
                                Search
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button 
                            @click="showGrantModal = true"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            Grant Extension
                        </button>
                        <button 
                            @click="showPricingModal = true"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                        >
                            Update Pricing
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="showBulkActions" class="mt-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-indigo-800">
                            {{ selectedExtensions.length }} extension(s) selected
                        </span>
                        <div class="space-x-2">
                            <button 
                                @click="performBulkAction('approve')"
                                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                            >
                                Approve Selected
                            </button>
                            <button 
                                @click="performBulkAction('reject')"
                                class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700"
                            >
                                Reject Selected
                            </button>
                            <button 
                                @click="selectedExtensions = []"
                                class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extensions Table -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input 
                                        type="checkbox"
                                        @change="selectAllExtensions"
                                        :checked="selectedExtensions.length === extensions.data.length && extensions.data.length > 0"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="extension in extensions.data" :key="extension.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input 
                                        type="checkbox"
                                        :value="extension.id"
                                        @change="toggleExtension(extension.id)"
                                        :checked="selectedExtensions.includes(extension.id)"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-indigo-600">
                                                    {{ extension.enrollment.name.charAt(0) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ extension.enrollment.name }}</div>
                                            <div class="text-sm text-gray-500">{{ extension.enrollment.enrollment_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ extension.extension_months }} month{{ extension.extension_months > 1 ? 's' : '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(extension.amount, extension.currency) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ extension.payment_method?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusBadge(extension.status)]">
                                        {{ extension.status.charAt(0).toUpperCase() + extension.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(extension.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link 
                                        :href="route('admin.extensions.show', extension.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        View
                                    </Link>
                                    <button 
                                        v-if="extension.status === 'pending'"
                                        @click="approveExtension(extension)"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        Approve
                                    </button>
                                    <button 
                                        v-if="extension.status === 'pending'"
                                        @click="rejectExtension(extension)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="extensions.links && extensions.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link 
                                v-if="extensions.prev_page_url" 
                                :href="extensions.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link 
                                v-if="extensions.next_page_url" 
                                :href="extensions.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ extensions.from || 0 }} to {{ extensions.to || 0 }} of {{ extensions.total }} results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link 
                                        v-for="link in extensions.links" 
                                        :key="link.label"
                                        :href="link.url || '#'"
                                        :class="[
                                            'relative inline-flex items-center px-2 py-2 border text-sm font-medium',
                                            link.active 
                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            link.url ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'
                                        ]"
                                        v-html="link.label"
                                    />
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- Grant Extension Modal -->
            <div v-if="showGrantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Grant Extension</h3>
                        <form @submit.prevent="grantExtension">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student ID</label>
                                <input 
                                    v-model="grantForm.user_id"
                                    type="number"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Enter user ID"
                                />
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Extension Duration (months)</label>
                                <input 
                                    v-model="grantForm.months"
                                    type="number"
                                    min="1"
                                    max="24"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                                <textarea 
                                    v-model="grantForm.reason"
                                    required
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Reason for granting extension..."
                                ></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button 
                                    type="button"
                                    @click="showGrantModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="grantForm.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Grant Extension
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Update Pricing Modal -->
            <div v-if="showPricingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-10 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Extension Pricing</h3>
                        <form @submit.prevent="updatePricing">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                                <select 
                                    v-model="pricingForm.region"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="malawi">Malawi</option>
                                    <option value="south_africa">South Africa</option>
                                    <option value="zambia">Zambia</option>
                                    <option value="botswana">Botswana</option>
                                    <option value="zimbabwe">Zimbabwe</option>
                                    <option value="international">International</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div v-for="(price, months) in pricingForm.pricing" :key="months">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ months }} Month{{ months > 1 ? 's' : '' }}
                                    </label>
                                    <input 
                                        v-model="pricingForm.pricing[months]"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button 
                                    type="button"
                                    @click="showPricingModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="pricingForm.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Update Pricing
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>