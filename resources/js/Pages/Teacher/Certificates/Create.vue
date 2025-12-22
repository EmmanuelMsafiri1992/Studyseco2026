<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    subjects: Array
});

const selectedSubject = ref('');
const students = ref([]);
const loadingStudents = ref(false);

const form = useForm({
    student_id: '',
    subject_id: '',
    title: '',
    description: '',
    certificate_type: 'completion',
});

const loadStudents = async () => {
    if (!selectedSubject.value) {
        students.value = [];
        return;
    }

    loadingStudents.value = true;
    try {
        const response = await fetch(route('teacher.subjects.students', selectedSubject.value));
        const data = await response.json();
        students.value = data;

        // Auto-select if only one student
        if (students.value.length === 1) {
            form.student_id = students.value[0].id;
        }
    } catch (error) {
        console.error('Error loading students:', error);
    } finally {
        loadingStudents.value = false;
    }
};

const updateSubject = () => {
    form.subject_id = selectedSubject.value;
    form.student_id = '';
    loadStudents();
};

const generateTitle = () => {
    const typeLabels = {
        completion: 'Certificate of Completion',
        achievement: 'Certificate of Achievement',
        excellence: 'Certificate of Excellence',
    };

    const selectedSubjectObj = props.subjects.find(s => s.id == form.subject_id);
    const subjectName = selectedSubjectObj?.name || '';

    if (subjectName) {
        form.title = `${typeLabels[form.certificate_type]} - ${subjectName}`;
    } else {
        form.title = typeLabels[form.certificate_type];
    }
};

const submit = () => {
    form.post(route('teacher.certificates.store'));
};
</script>

<template>
    <Head title="Issue Certificate" />

    <DashboardLayout title="Issue Certificate" subtitle="Recognize student achievement">
        <!-- Back Button -->
        <div class="mb-4">
            <Link :href="route('teacher.certificates.index')"
                  class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Certificates
            </Link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Certificate Type -->
                <div>
                    <InputLabel for="certificate_type" value="Certificate Type *" />
                    <select id="certificate_type" v-model="form.certificate_type" @change="generateTitle"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                        <option value="completion">Certificate of Completion</option>
                        <option value="achievement">Certificate of Achievement</option>
                        <option value="excellence">Certificate of Excellence</option>
                    </select>
                    <p class="mt-1 text-sm text-slate-500">Select the type of recognition</p>
                </div>

                <!-- Subject -->
                <div>
                    <InputLabel for="subject" value="Subject (Optional)" />
                    <select id="subject" v-model="selectedSubject" @change="updateSubject"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">General Certificate (No specific subject)</option>
                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                            {{ subject.name }}
                        </option>
                    </select>
                    <p class="mt-1 text-sm text-slate-500">Choose a subject or leave as general</p>
                </div>

                <!-- Student -->
                <div>
                    <InputLabel for="student_id" value="Student *" />
                    <select id="student_id" v-model="form.student_id"
                            :disabled="!selectedSubject || loadingStudents"
                            class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{'bg-slate-100': !selectedSubject || loadingStudents}"
                            required>
                        <option value="">{{ loadingStudents ? 'Loading students...' : 'Select a student' }}</option>
                        <option v-for="student in students" :key="student.id" :value="student.id">
                            {{ student.name }} ({{ student.email }})
                        </option>
                    </select>
                    <p class="mt-1 text-sm text-slate-500">
                        <span v-if="!selectedSubject">Select a subject first to load students</span>
                        <span v-else-if="students.length === 0 && !loadingStudents">No students enrolled in this subject</span>
                        <span v-else>Select the student to receive this certificate</span>
                    </p>
                </div>

                <!-- Title -->
                <div>
                    <InputLabel for="title" value="Certificate Title *" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="e.g., Certificate of Completion - Mathematics"
                    />
                    <button type="button" @click="generateTitle"
                            class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                        Auto-generate title
                    </button>
                </div>

                <!-- Description -->
                <div>
                    <InputLabel for="description" value="Description (Optional)" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="A custom message for this certificate (leave blank for default message)"
                    ></textarea>
                    <p class="mt-1 text-sm text-slate-500">
                        Add a personalized message or leave blank to use the default certificate text
                    </p>
                </div>

                <!-- Preview Box -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-indigo-900 mb-2">Certificate Preview</h4>
                    <div class="text-sm text-indigo-700 space-y-1">
                        <p><strong>Type:</strong> {{ form.certificate_type.charAt(0).toUpperCase() + form.certificate_type.slice(1) }}</p>
                        <p><strong>Title:</strong> {{ form.title || 'Not set' }}</p>
                        <p><strong>Student:</strong> {{ students.find(s => s.id == form.student_id)?.name || 'Not selected' }}</p>
                        <p><strong>Subject:</strong> {{ subjects.find(s => s.id == form.subject_id)?.name || 'General' }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                    <Link :href="route('teacher.certificates.index')"
                          class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                        Cancel
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Issuing Certificate...' : 'Issue Certificate' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
