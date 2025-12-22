<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    certificates: Object,
    subjects: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const subjectFilter = ref(props.filters.subject_id || '');
const typeFilter = ref(props.filters.certificate_type || '');

const filterCertificates = () => {
    router.get(route('teacher.certificates.index'), {
        search: search.value,
        subject_id: subjectFilter.value,
        certificate_type: typeFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteCertificate = (certificate) => {
    if (confirm(`Are you sure you want to delete this certificate for ${certificate.student?.name}?`)) {
        router.delete(route('teacher.certificates.destroy', certificate.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getTypeColor = (type) => {
    const colors = {
        completion: 'bg-blue-100 text-blue-800',
        achievement: 'bg-green-100 text-green-800',
        excellence: 'bg-purple-100 text-purple-800',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const getTypeLabel = (type) => {
    const labels = {
        completion: 'Completion',
        achievement: 'Achievement',
        excellence: 'Excellence',
    };
    return labels[type] || type;
};
</script>

<template>
    <Head title="Certificates" />

    <DashboardLayout title="Certificates" subtitle="Manage student certificates">
        <!-- Actions -->
        <div class="mb-6">
            <Link :href="route('teacher.certificates.create')"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Issue Certificate
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" v-model="search" @input="filterCertificates"
                           placeholder="Search student name..."
                           class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <select v-model="subjectFilter" @change="filterCertificates"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Subjects</option>
                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                            {{ subject.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <select v-model="typeFilter" @change="filterCertificates"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Types</option>
                        <option value="completion">Completion</option>
                        <option value="achievement">Achievement</option>
                        <option value="excellence">Excellence</option>
                    </select>
                </div>
                <div>
                    <button @click="search = ''; subjectFilter = ''; typeFilter = ''; filterCertificates()"
                            class="w-full px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors text-sm">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Certificates List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div v-if="certificates.data && certificates.data.length > 0" class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Student</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Title</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Type</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Subject</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Issued Date</th>
                            <th class="text-right py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="certificate in certificates.data" :key="certificate.id"
                            class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-700">
                                            {{ certificate.student?.name?.charAt(0) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ certificate.student?.name }}</p>
                                        <p class="text-xs text-slate-500">{{ certificate.student?.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm font-medium text-slate-900">{{ certificate.title }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span :class="['px-3 py-1 text-xs font-medium rounded-full', getTypeColor(certificate.certificate_type)]">
                                    {{ getTypeLabel(certificate.certificate_type) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm text-slate-600">
                                    {{ certificate.subject?.name || 'General' }}
                                </p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm text-slate-600">{{ formatDate(certificate.issued_at) }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="route('teacher.certificates.download', certificate.id)"
                                       class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                       title="Download">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    <button @click="deleteCertificate(certificate)"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">No certificates found</h3>
                <p class="text-slate-600 mb-4">Start issuing certificates to recognize student achievements.</p>
                <Link :href="route('teacher.certificates.create')"
                      class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Issue First Certificate
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="certificates.data && certificates.data.length > 0" class="px-6 py-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        Showing {{ certificates.from || 0 }} to {{ certificates.to || 0 }} of {{ certificates.total || 0 }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link v-if="certificates.prev_page_url" :href="certificates.prev_page_url"
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors text-sm">
                            Previous
                        </Link>
                        <Link v-if="certificates.next_page_url" :href="certificates.next_page_url"
                              class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 transition-colors text-sm">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
