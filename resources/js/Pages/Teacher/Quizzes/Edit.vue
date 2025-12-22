<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';

const props = defineProps({
    quiz: Object,
    subjects: Array
});

const form = useForm({
    subject_id: props.quiz.subject_id,
    title: props.quiz.title,
    description: props.quiz.description,
    duration_minutes: props.quiz.duration_minutes,
    total_points: props.quiz.total_points,
    passing_score: props.quiz.passing_score,
    show_correct_answers: props.quiz.show_correct_answers,
    shuffle_questions: props.quiz.shuffle_questions,
    max_attempts: props.quiz.max_attempts,
    status: props.quiz.status,
    available_from: props.quiz.available_from ? props.quiz.available_from.slice(0, 16) : '',
    available_until: props.quiz.available_until ? props.quiz.available_until.slice(0, 16) : '',
});

const questionForm = useForm({
    question: '',
    type: 'multiple_choice',
    options: ['', '', '', ''],
    correct_answer: '',
    explanation: '',
    points: 10,
});

const editingQuestion = ref(null);
const showQuestionModal = ref(false);

const submit = () => {
    form.put(route('teacher.quizzes.update', props.quiz.id));
};

const openAddQuestion = () => {
    questionForm.reset();
    questionForm.type = 'multiple_choice';
    questionForm.options = ['', '', '', ''];
    questionForm.points = 10;
    editingQuestion.value = null;
    showQuestionModal.value = true;
};

const openEditQuestion = (question) => {
    editingQuestion.value = question;
    questionForm.question = question.question;
    questionForm.type = question.type;
    questionForm.options = question.options || ['', '', '', ''];
    questionForm.correct_answer = question.correct_answer;
    questionForm.explanation = question.explanation || '';
    questionForm.points = question.points;
    showQuestionModal.value = true;
};

const saveQuestion = () => {
    if (editingQuestion.value) {
        questionForm.put(route('teacher.quizzes.questions.update', [props.quiz.id, editingQuestion.value.id]), {
            preserveScroll: true,
            onSuccess: () => {
                showQuestionModal.value = false;
                questionForm.reset();
            }
        });
    } else {
        questionForm.post(route('teacher.quizzes.questions.add', props.quiz.id), {
            preserveScroll: true,
            onSuccess: () => {
                showQuestionModal.value = false;
                questionForm.reset();
            }
        });
    }
};

const deleteQuestion = (question) => {
    if (confirm('Delete this question?')) {
        router.delete(route('teacher.quizzes.questions.delete', [props.quiz.id, question.id]), {
            preserveScroll: true
        });
    }
};

const addOption = () => {
    questionForm.options.push('');
};

const removeOption = (index) => {
    questionForm.options.splice(index, 1);
};
</script>

<template>
    <Head title="Edit Quiz" />

    <DashboardLayout :title="`Edit Quiz: ${quiz.title}`" subtitle="Update quiz settings and manage questions">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Quiz Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h3 class="text-lg font-semibold text-slate-900 mb-6">Quiz Settings</h3>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="subject_id" value="Subject *" />
                            <select id="subject_id" v-model="form.subject_id"
                                    class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.subject_id" />
                        </div>

                        <div>
                            <InputLabel for="title" value="Quiz Title *" />
                            <TextInput id="title" type="text" class="mt-1 block w-full" v-model="form.title" required />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <textarea id="description" v-model="form.description" rows="2"
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <InputLabel for="duration_minutes" value="Duration (min) *" />
                            <TextInput id="duration_minutes" type="number" class="mt-1 block w-full" v-model="form.duration_minutes" min="1" required />
                        </div>
                        <div>
                            <InputLabel for="total_points" value="Total Points *" />
                            <TextInput id="total_points" type="number" class="mt-1 block w-full" v-model="form.total_points" min="1" required />
                        </div>
                        <div>
                            <InputLabel for="passing_score" value="Passing Score *" />
                            <TextInput id="passing_score" type="number" class="mt-1 block w-full" v-model="form.passing_score" min="0" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="available_from" value="Available From" />
                            <input id="available_from" type="datetime-local" v-model="form.available_from"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <InputLabel for="available_until" value="Available Until" />
                            <input id="available_until" type="datetime-local" v-model="form.available_until"
                                   class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <InputLabel for="max_attempts" value="Max Attempts *" />
                            <TextInput id="max_attempts" type="number" class="mt-1 block w-full" v-model="form.max_attempts" min="1" required />
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.show_correct_answers" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="ml-2 text-sm text-slate-700">Show correct answers after submission</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.shuffle_questions" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="ml-2 text-sm text-slate-700">Shuffle questions</span>
                            </label>
                        </div>

                        <div>
                            <InputLabel for="status" value="Status *" />
                            <select id="status" v-model="form.status"
                                    class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-200">
                        <PrimaryButton :disabled="form.processing">Update Quiz Settings</PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Questions -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-slate-900">Questions ({{ quiz.questions?.length || 0 }})</h3>
                    <button @click="openAddQuestion" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Add Question
                    </button>
                </div>

                <div v-if="quiz.questions && quiz.questions.length > 0" class="space-y-4">
                    <div v-for="(question, index) in quiz.questions" :key="question.id"
                         class="border border-slate-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-medium text-slate-500">Q{{ index + 1 }}</span>
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ question.type.replace('_', ' ') }}</span>
                                    <span class="text-sm text-slate-600">{{ question.points }} points</span>
                                </div>
                                <p class="text-slate-900 font-medium mb-2">{{ question.question }}</p>
                                <div v-if="question.type === 'multiple_choice' && question.options" class="ml-4 space-y-1">
                                    <div v-for="(option, i) in question.options" :key="i" class="text-sm text-slate-600">
                                        <span :class="option === question.correct_answer ? 'text-green-600 font-medium' : ''">
                                            {{ String.fromCharCode(65 + i) }}. {{ option }}
                                            <span v-if="option === question.correct_answer" class="text-xs">(Correct)</span>
                                        </span>
                                    </div>
                                </div>
                                <p v-else-if="question.type === 'true_false'" class="text-sm text-slate-600 ml-4">
                                    Correct answer: <span class="font-medium text-green-600">{{ question.correct_answer }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <button @click="openEditQuestion(question)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="deleteQuestion(question)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-slate-500">
                    No questions added yet. Click "Add Question" to get started.
                </div>
            </div>
        </div>

        <!-- Question Modal -->
        <div v-if="showQuestionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-900">{{ editingQuestion ? 'Edit' : 'Add' }} Question</h3>
                        <button @click="showQuestionModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="saveQuestion" class="p-6 space-y-6">
                    <div>
                        <InputLabel for="type" value="Question Type *" />
                        <select id="type" v-model="questionForm.type"
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="question" value="Question *" />
                        <textarea id="question" v-model="questionForm.question" rows="3" required
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <InputError class="mt-2" :message="questionForm.errors.question" />
                    </div>

                    <div v-if="questionForm.type === 'multiple_choice'">
                        <InputLabel value="Options *" />
                        <div class="space-y-2 mt-2">
                            <div v-for="(option, index) in questionForm.options" :key="index" class="flex items-center gap-2">
                                <input v-model="questionForm.options[index]" type="text" required
                                       class="flex-1 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                       :placeholder="`Option ${String.fromCharCode(65 + index)}`">
                                <button v-if="questionForm.options.length > 2" type="button" @click="removeOption(index)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="addOption" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                            + Add Option
                        </button>
                    </div>

                    <div>
                        <InputLabel for="correct_answer" value="Correct Answer *" />
                        <select v-if="questionForm.type === 'multiple_choice'" id="correct_answer" v-model="questionForm.correct_answer" required
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select correct option</option>
                            <option v-for="(option, index) in questionForm.options" :key="index" :value="option">
                                {{ String.fromCharCode(65 + index) }}. {{ option }}
                            </option>
                        </select>
                        <select v-else-if="questionForm.type === 'true_false'" id="correct_answer" v-model="questionForm.correct_answer" required
                                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="True">True</option>
                            <option value="False">False</option>
                        </select>
                        <input v-else id="correct_answer" v-model="questionForm.correct_answer" type="text" required
                               class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Enter the correct answer">
                        <InputError class="mt-2" :message="questionForm.errors.correct_answer" />
                    </div>

                    <div>
                        <InputLabel for="explanation" value="Explanation (Optional)" />
                        <textarea id="explanation" v-model="questionForm.explanation" rows="2"
                                  class="mt-1 block w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Explain why this is the correct answer..."></textarea>
                    </div>

                    <div>
                        <InputLabel for="points" value="Points *" />
                        <TextInput id="points" type="number" class="mt-1 block w-full" v-model="questionForm.points" min="1" required />
                        <InputError class="mt-2" :message="questionForm.errors.points" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                        <button type="button" @click="showQuestionModal = false"
                                class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200">
                            Cancel
                        </button>
                        <PrimaryButton :disabled="questionForm.processing">
                            {{ editingQuestion ? 'Update' : 'Add' }} Question
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
