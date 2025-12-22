<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    fees: Array
});

// Mock data fallback
const fees = ref(props.fees?.length > 0 ? props.fees : [
    { id: 1, studentId: 'S1001', name: 'Michael Chen', amountDue: 500, status: 'paid', dueDate: '2023-09-01' },
    { id: 2, studentId: 'S1002', name: 'Emma Rodriguez', amountDue: 600, status: 'overdue', dueDate: '2023-08-15' },
    { id: 3, studentId: 'S1003', name: 'James Thompson', amountDue: 450, status: 'pending', dueDate: '2023-09-15' },
]);

const searchQuery = ref('');
const selectedStatus = ref('all');

const filteredFees = computed(() => {
    return fees.value.filter(fee => {
        const matchesSearch = fee.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              fee.studentId.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value === 'all' || fee.status === selectedStatus.value;
        return matchesSearch && matchesStatus;
    });
});

const getStatusColor = (status) => {
    const colors = {
        paid: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        overdue: 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Fees Management" />
    
    <DashboardLayout title="Fees Management" subtitle="Manage student fees and payments">
        <div class="max-w-7xl mx-auto">
            <!-- Search and Filter -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by student name or ID..." 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    <div>
                        <select 
                            v-model="selectedStatus"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fees List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Student Fees</h2>
                </div>
                
                <div v-if="filteredFees.length === 0" class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">💰</div>
                    <p class="text-gray-500 text-lg">No fees found.</p>
                    <p class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria.</p>
                </div>
                
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="fee in filteredFees" :key="fee.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ fee.name }}</div>
                                        <div class="text-sm text-gray-500">{{ fee.studentId }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${{ fee.amountDue }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ fee.dueDate }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusColor(fee.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize">
                                        {{ fee.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-800 mr-3">View</button>
                                    <button class="text-green-600 hover:text-green-800 mr-3">Pay</button>
                                    <button class="text-gray-600 hover:text-gray-800">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>