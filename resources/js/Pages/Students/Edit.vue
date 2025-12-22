<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps({
    auth: Object,
    student: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

const form = useForm({
    name: props.student.name,
    email: props.student.email,
    phone: props.student.phone || '',
    is_active: props.student.is_active,
});

const submit = () => {
    form.put(route('students.update', props.student.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit Student: ${student.name}`" />
    
    <DashboardLayout :title="`Edit Student: ${student.name}`" subtitle="Update student information">
        <div class="space-y-6">
            <!-- Edit Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name Field -->
                        <div>
                            <InputLabel for="name" value="Full Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Email Field -->
                        <div>
                            <InputLabel for="email" value="Email Address" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                                autocomplete="email"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Phone Field -->
                        <div>
                            <InputLabel for="phone" value="Phone Number" />
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="tel"
                                placeholder="+265 xxx xxx xxx"
                            />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <!-- Status Field -->
                        <div>
                            <InputLabel for="is_active" value="Account Status" />
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input
                                        id="is_active"
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">
                                        Active Account
                                    </span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.is_active" />
                        </div>
                    </div>

                    <!-- Student Information Display -->
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Student Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-slate-600">Student ID:</span>
                                <p class="font-medium text-slate-800">{{ student.id }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-slate-600">Registration Date:</span>
                                <p class="font-medium text-slate-800">
                                    {{ new Date(student.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                            <div v-if="student.last_login_at">
                                <span class="text-sm text-slate-600">Last Login:</span>
                                <p class="font-medium text-slate-800">
                                    {{ new Date(student.last_login_at).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
                        <Link
                            :href="route('students.show', student.id)"
                            class="text-slate-600 hover:text-slate-800 transition-colors duration-200"
                        >
                            ← Back to Student
                        </Link>

                        <div class="flex gap-4">
                            <SecondaryButton
                                :href="route('students.index')"
                                tag="Link"
                            >
                                Cancel
                            </SecondaryButton>

                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Update Student
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 border border-red-200 rounded-3xl p-6">
                <h3 class="text-lg font-semibold text-red-800 mb-2">Danger Zone</h3>
                <p class="text-red-600 mb-4">
                    Permanently delete this student account and all associated data. This action cannot be undone.
                </p>
                <button
                    @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
                >
                    Delete Student Account
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

<script>
export default {
    methods: {
        confirmDelete() {
            if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
                this.$inertia.delete(this.route('students.destroy', this.student.id));
            }
        }
    }
}
</script>