<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    certificates: Object,
    filters: Object
});

const typeFilter = ref(props.filters.certificate_type || '');

const filterCertificates = () => {
    router.get(route('student.certificates.index'), {
        certificate_type: typeFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getTypeColor = (type) => {
    const colors = {
        completion: 'bg-blue-100 text-blue-800 border-blue-200',
        achievement: 'bg-green-100 text-green-800 border-green-200',
        excellence: 'bg-purple-100 text-purple-800 border-purple-200',
    };
    return colors[type] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getTypeLabel = (type) => {
    const labels = {
        completion: 'Certificate of Completion',
        achievement: 'Certificate of Achievement',
        excellence: 'Certificate of Excellence',
    };
    return labels[type] || type;
};

const getTypeIcon = (type) => {
    const icons = {
        completion: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        achievement: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        excellence: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
    };
    return icons[type] || 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
};
</script>

<template>
    <Head title="My Certificates" />

    <DashboardLayout title="My Certificates" subtitle="View your achievements">
        <!-- Filter -->
        <div class="mb-6">
            <div class="inline-flex items-center gap-2">
                <span class="text-sm text-slate-600">Filter:</span>
                <select v-model="typeFilter" @change="filterCertificates"
                        class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Types</option>
                    <option value="completion">Completion</option>
                    <option value="achievement">Achievement</option>
                    <option value="excellence">Excellence</option>
                </select>
            </div>
        </div>

        <!-- Certificates Grid -->
        <div v-if="certificates.data && certificates.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="certificate in certificates.data" :key="certificate.id"
                 :class="['bg-white rounded-2xl shadow-sm border-2 p-6 hover:shadow-lg transition-all', getTypeColor(certificate.certificate_type)]">
                <!-- Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-white shadow-md">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(certificate.certificate_type)"/>
                    </svg>
                </div>

                <!-- Type Badge -->
                <div class="text-center mb-3">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-white shadow-sm">
                        {{ getTypeLabel(certificate.certificate_type).replace('Certificate of ', '') }}
                    </span>
                </div>

                <!-- Title -->
                <h3 class="text-lg font-bold text-slate-900 text-center mb-2">
                    {{ certificate.title }}
                </h3>

                <!-- Subject -->
                <p class="text-sm text-slate-600 text-center mb-3">
                    {{ certificate.subject?.name || 'General Achievement' }}
                </p>

                <!-- Issued Info -->
                <div class="text-center text-xs text-slate-500 mb-4">
                    <p>Issued by {{ certificate.teacher?.name }}</p>
                    <p>{{ formatDate(certificate.issued_at) }}</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <a :href="route('student.certificates.download', certificate.id)"
                       class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-center text-sm font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download
                    </a>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
            <div class="max-w-md mx-auto">
                <svg class="w-20 h-20 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">No certificates yet</h3>
                <p class="text-slate-600 mb-6">
                    Keep up the great work! Your teachers will issue certificates as you achieve milestones and complete courses.
                </p>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <h4 class="font-semibold text-indigo-900 mb-2">How to earn certificates:</h4>
                    <ul class="text-sm text-indigo-700 text-left space-y-1">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Complete course requirements and assignments</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Excel in quizzes and assessments</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Demonstrate consistent progress and dedication</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="certificates.data && certificates.data.length > 0" class="mt-6">
            <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border border-slate-200 px-6 py-4">
                <div class="text-sm text-slate-700">
                    Showing {{ certificates.from || 0 }} to {{ certificates.to || 0 }} of {{ certificates.total || 0 }} certificates
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
    </DashboardLayout>
</template>
