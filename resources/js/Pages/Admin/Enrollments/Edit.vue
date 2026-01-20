<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    enrollment: Object,
    subjects: Array,
    subjectNames: Array
});

const form = useForm({
    selected_subjects: props.enrollment.selected_subjects || [],
    access_expires_at: props.enrollment.access_expires_at ? props.enrollment.access_expires_at.split('T')[0] : '',
    status: props.enrollment.status,
    admin_notes: props.enrollment.admin_notes || ''
});

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
        gradeSubjects.forEach(s => {
            const index = form.selected_subjects.indexOf(s.id);
            if (index > -1) form.selected_subjects.splice(index, 1);
        });
    } else {
        gradeSubjects.forEach(s => {
            if (!isSubjectSelected(s.id)) {
                form.selected_subjects.push(s.id);
            }
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const submit = () => {
    form.put(route('admin.enrollments.update', props.enrollment.id), {
        onSuccess: () => {
            // Will redirect automatically
        },
        onError: (errors) => {
            console.error('Update errors:', errors);
        }
    });
};
</script>

<template>
    <Head :title="`Edit Enrollment - ${enrollment.name}`" />

    <DashboardLayout
        title="Edit Enrollment"
        :subtitle="`Editing enrollment for ${enrollment.name}`">

        <div class="max-w-4xl mx-auto">
            <!-- Student Info Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Student Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Name</label>
                        <p class="text-slate-800 font-medium">{{ enrollment.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Email</label>
                        <p class="text-slate-800">{{ enrollment.email }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Phone</label>
                        <p class="text-slate-800">{{ enrollment.phone }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Country</label>
                        <p class="text-slate-800">{{ enrollment.country }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Enrollment Number</label>
                        <p class="text-slate-800 font-mono">{{ enrollment.enrollment_number }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Enrolled On</label>
                        <p class="text-slate-800">{{ formatDate(enrollment.created_at) }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Subject Selection Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">
                        Enrolled Subjects
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

                <!-- Enrollment Settings Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Enrollment Settings</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Access Expires At *</label>
                            <input
                                v-model="form.access_expires_at"
                                type="date"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            />
                            <div v-if="form.errors.access_expires_at" class="text-red-500 text-xs mt-1">{{ form.errors.access_expires_at }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status *</label>
                            <select
                                v-model="form.status"
                                required
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
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
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Link :href="route('admin.enrollments.show', enrollment.id)"
                          class="px-6 py-3 text-sm font-medium text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || form.selected_subjects.length === 0"
                        class="px-8 py-3 text-sm font-medium text-white bg-indigo-500 rounded-2xl hover:bg-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
