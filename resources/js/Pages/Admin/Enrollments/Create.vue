<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    subjects: Array,
    students: Array,
    paymentMethods: Array
});

const form = useForm({
    user_id: null,
    name: '',
    email: '',
    phone: '',
    address: '',
    country: 'Malawi',
    selected_subjects: [],
    enrollment_type: 'paid',
    access_duration_months: 4,
    admin_notes: ''
});

const isNewStudent = ref(true);
const searchQuery = ref('');

// Group subjects by grade level
const groupedSubjects = computed(() => {
    const groups = {};
    props.subjects.forEach(subject => {
        const grade = subject.grade_level || 'Other';
        if (!groups[grade]) {
            groups[grade] = [];
        }
        groups[grade].push(subject);
    });
    return groups;
});

// Filter students based on search query
const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(student =>
        student.name.toLowerCase().includes(query) ||
        student.email.toLowerCase().includes(query)
    );
});

// When selecting an existing student, populate form fields
const selectStudent = (student) => {
    form.user_id = student.id;
    form.name = student.name;
    form.email = student.email;
    form.phone = student.phone || '';
    isNewStudent.value = false;
};

// Toggle between new and existing student
const toggleStudentType = (isNew) => {
    isNewStudent.value = isNew;
    if (isNew) {
        form.user_id = null;
        form.name = '';
        form.email = '';
        form.phone = '';
    }
};

// Toggle subject selection
const toggleSubject = (subjectId) => {
    const index = form.selected_subjects.indexOf(subjectId);
    if (index > -1) {
        form.selected_subjects.splice(index, 1);
    } else {
        form.selected_subjects.push(subjectId);
    }
};

// Check if subject is selected
const isSubjectSelected = (subjectId) => {
    return form.selected_subjects.includes(subjectId);
};

// Select all subjects in a grade level
const selectAllInGrade = (grade) => {
    const gradeSubjects = groupedSubjects.value[grade];
    const allSelected = gradeSubjects.every(s => isSubjectSelected(s.id));

    if (allSelected) {
        // Deselect all
        gradeSubjects.forEach(s => {
            const index = form.selected_subjects.indexOf(s.id);
            if (index > -1) form.selected_subjects.splice(index, 1);
        });
    } else {
        // Select all
        gradeSubjects.forEach(s => {
            if (!isSubjectSelected(s.id)) {
                form.selected_subjects.push(s.id);
            }
        });
    }
};

// Calculate pricing
const pricing = computed(() => {
    const country = form.country.toLowerCase();
    let pricePerSubject = 50000;
    let currency = 'MWK';

    if (country === 'south africa' || country === 'za') {
        pricePerSubject = 350;
        currency = 'ZAR';
    } else if (!['malawi', 'mw'].includes(country)) {
        pricePerSubject = 25;
        currency = 'USD';
    }

    if (form.enrollment_type === 'trial' || form.enrollment_type === 'free') {
        pricePerSubject = 0;
    }

    return {
        pricePerSubject,
        currency,
        total: pricePerSubject * form.selected_subjects.length
    };
});

const formatCurrency = (amount, currency) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0
    }).format(amount);
};

const submit = () => {
    form.post(route('admin.enrollments.store'), {
        onSuccess: () => {
            // Will redirect automatically
        },
        onError: (errors) => {
            console.error('Enrollment errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Enroll Student" />

    <DashboardLayout
        title="Enroll Student"
        subtitle="Create a new enrollment for a student">

        <div class="max-w-4xl mx-auto">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Student Selection Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Student Information</h2>

                    <!-- Toggle between new and existing -->
                    <div class="flex space-x-4 mb-6">
                        <button
                            type="button"
                            @click="toggleStudentType(true)"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                isNewStudent
                                    ? 'bg-indigo-500 text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            ]">
                            New Student
                        </button>
                        <button
                            type="button"
                            @click="toggleStudentType(false)"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                !isNewStudent
                                    ? 'bg-indigo-500 text-white'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            ]">
                            Existing Student
                        </button>
                    </div>

                    <!-- Existing Student Search -->
                    <div v-if="!isNewStudent" class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Search Student</label>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name or email..."
                            class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                        />

                        <div v-if="filteredStudents.length > 0" class="mt-3 max-h-48 overflow-y-auto rounded-xl border border-slate-200">
                            <button
                                v-for="student in filteredStudents"
                                :key="student.id"
                                type="button"
                                @click="selectStudent(student)"
                                :class="[
                                    'w-full px-4 py-3 text-left hover:bg-indigo-50 transition-colors border-b border-slate-100 last:border-b-0',
                                    form.user_id === student.id ? 'bg-indigo-50' : ''
                                ]">
                                <div class="font-medium text-slate-800">{{ student.name }}</div>
                                <div class="text-sm text-slate-500">{{ student.email }}</div>
                            </button>
                        </div>
                    </div>

                    <!-- Student Details Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                :readonly="!isNewStudent && form.user_id"
                                :class="[
                                    'w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200',
                                    !isNewStudent && form.user_id ? 'bg-slate-200 cursor-not-allowed' : ''
                                ]"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                :readonly="!isNewStudent && form.user_id"
                                :class="[
                                    'w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200',
                                    !isNewStudent && form.user_id ? 'bg-slate-200 cursor-not-allowed' : ''
                                ]"
                            />
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Phone *</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            />
                            <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Country *</label>
                            <select
                                v-model="form.country"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option value="Malawi">Malawi</option>
                                <option value="South Africa">South Africa</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Address *</label>
                            <input
                                v-model="form.address"
                                type="text"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            />
                            <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                        </div>
                    </div>
                </div>

                <!-- Subject Selection Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">
                        Select Subjects
                        <span class="text-sm font-normal text-slate-500">({{ form.selected_subjects.length }} selected)</span>
                    </h2>
                    <div v-if="form.errors.selected_subjects" class="text-red-500 text-sm mb-4">{{ form.errors.selected_subjects }}</div>

                    <div class="space-y-6">
                        <div v-for="(subjects, grade) in groupedSubjects" :key="grade">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-md font-medium text-slate-700">{{ grade }}</h3>
                                <button
                                    type="button"
                                    @click="selectAllInGrade(grade)"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ subjects.every(s => isSubjectSelected(s.id)) ? 'Deselect All' : 'Select All' }}
                                </button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                <button
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    type="button"
                                    @click="toggleSubject(subject.id)"
                                    :class="[
                                        'p-3 rounded-xl text-left transition-all duration-200 border-2',
                                        isSubjectSelected(subject.id)
                                            ? 'border-indigo-500 bg-indigo-50 text-indigo-800'
                                            : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-300'
                                    ]">
                                    <div class="font-medium text-sm">{{ subject.name }}</div>
                                    <div v-if="subject.code" class="text-xs text-slate-500 mt-1">{{ subject.code }}</div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Details Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Enrollment Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Enrollment Type *</label>
                            <select
                                v-model="form.enrollment_type"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option value="paid">Paid Enrollment</option>
                                <option value="free">Free (Admin Grant)</option>
                                <option value="trial">7-Day Trial</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Access Duration (Months) *</label>
                            <select
                                v-model="form.access_duration_months"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option :value="1">1 Month</option>
                                <option :value="2">2 Months</option>
                                <option :value="3">3 Months</option>
                                <option :value="4">4 Months</option>
                                <option :value="6">6 Months</option>
                                <option :value="12">12 Months (1 Year)</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Admin Notes</label>
                            <textarea
                                v-model="form.admin_notes"
                                rows="3"
                                placeholder="Optional notes about this enrollment..."
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Pricing Summary -->
                    <div class="mt-6 p-4 bg-slate-50 rounded-2xl">
                        <h3 class="text-sm font-medium text-slate-700 mb-3">Pricing Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Subjects Selected:</span>
                                <span class="font-medium">{{ form.selected_subjects.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Price per Subject:</span>
                                <span class="font-medium">{{ formatCurrency(pricing.pricePerSubject, pricing.currency) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200">
                                <span class="text-slate-800 font-medium">Total Amount:</span>
                                <span class="font-bold text-lg text-indigo-600">{{ formatCurrency(pricing.total, pricing.currency) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Link :href="route('admin.enrollments.index')"
                          class="px-6 py-3 text-sm font-medium text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || form.selected_subjects.length === 0"
                        class="px-8 py-3 text-sm font-medium text-white bg-indigo-500 rounded-2xl hover:bg-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ form.processing ? 'Enrolling...' : 'Enroll Student' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
