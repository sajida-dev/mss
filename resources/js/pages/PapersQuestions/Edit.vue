<template>
    <AppLayout>

        <Head title="Edit Paper" />

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-8">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit Paper</h1>
                    <Button variant="outline" @click="goBack">Back to Papers</Button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Paper Information -->
                    <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Paper Information</h3>

                        <!-- First Row: Title, Class, Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    Paper Title <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.title" type="text" placeholder="Enter paper title"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                    :class="{ 'border-red-500': errors.title }" />
                                <InputError :message="errors.title" />
                            </div>

                            <!-- Class Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    Class <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.class_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                    :class="{ 'border-red-500': errors.class_id }">
                                    <option value="">Select Class</option>
                                    <option v-for="classItem in props.classes" :key="classItem.id"
                                        :value="classItem.id">
                                        {{ classItem.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.class_id" />
                            </div>

                            <!-- Section Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    Section (Optional)
                                </label>
                                <select v-model="form.section_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                    :class="{ 'border-red-500': errors.section_id }">
                                    <option value="">Select Section</option>
                                    <option v-for="section in props.sections" :key="section.id" :value="section.id">
                                        {{ section.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.section_id" />
                            </div>
                        </div>

                        <!-- Second Row: Teacher, Published -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Teacher Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    Teacher <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.teacher_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                    :class="{ 'border-red-500': errors.teacher_id }">
                                    <option value="">Select Teacher</option>
                                    <option v-for="teacher in props.teachers" :key="teacher.id" :value="teacher.id">
                                        {{ teacher.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.teacher_id" />
                            </div>

                            <!-- Published Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    Status
                                </label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input v-model="form.published" type="checkbox" value="true"
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Published</span>
                                    </label>
                                </div>
                                <InputError :message="errors.published" />
                            </div>
                        </div>
                    </div>

                    <!-- Questions Section -->
                    <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Questions</h3>
                            <Button type="button" variant="outline" @click="addQuestion">
                                Add Question
                            </Button>
                        </div>

                        <div v-if="form.questions.length === 0"
                            class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No questions added yet. Click "Add Question" to get started.
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="(question, index) in form.questions" :key="index"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-neutral-900">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Question {{ index + 1 }}
                                    </h4>
                                    <Button type="button" variant="outline" size="sm" @click="removeQuestion(index)"
                                        class="text-red-600 hover:text-red-700">
                                        Remove
                                    </Button>
                                </div>

                                <!-- Question Text -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                        Question Text <span class="text-red-500">*</span>
                                    </label>
                                    <textarea v-model="question.text" rows="3" placeholder="Enter your question..."
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                        :class="{ 'border-red-500': getError(`questions.${index}.text`) }"></textarea>
                                    <InputError :message="getError(`questions.${index}.text`)" />
                                </div>

                                <!-- Question Type -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            Question Type <span class="text-red-500">*</span>
                                        </label>
                                        <select v-model="question.type"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                            :class="{ 'border-red-500': getError(`questions.${index}.type`) }">
                                            <option value="">Select Type</option>
                                            <option value="multiple_choice">Multiple Choice</option>
                                            <option value="true_false">True/False</option>
                                            <option value="short_answer">Short Answer</option>
                                            <option value="essay">Essay</option>
                                        </select>
                                        <InputError :message="getError(`questions.${index}.type`)" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            Correct Answer (Optional)
                                        </label>
                                        <input v-model="question.answer" type="text" placeholder="Enter correct answer"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                            :class="{ 'border-red-500': getError(`questions.${index}.answer`) }" />
                                        <InputError :message="getError(`questions.${index}.answer`)" />
                                    </div>
                                </div>

                                <!-- Options for Multiple Choice -->
                                <div v-if="question.type === 'multiple_choice'" class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                        Options <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                                            class="flex gap-2">
                                            <input v-model="question.options[optionIndex]" type="text"
                                                :placeholder="`Option ${optionIndex + 1}`"
                                                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500" />
                                            <Button type="button" variant="outline" size="sm"
                                                @click="removeOption(index, optionIndex)"
                                                class="text-red-600 hover:text-red-700">
                                                Remove
                                            </Button>
                                        </div>
                                        <Button type="button" variant="outline" size="sm" @click="addOption(index)">
                                            Add Option
                                        </Button>
                                    </div>
                                    <InputError :message="getError(`questions.${index}.options`)" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" @click="goBack">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Paper</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';

interface ClassModel {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
}

interface Teacher {
    id: number;
    name: string;
}

interface Question {
    text: string;
    type: string;
    options: string[];
    answer: string;
}

interface Paper {
    id: number;
    title: string;
    class_id: number;
    section_id?: number;
    teacher_id: number;
    published: boolean;
    questions: Question[];
}

interface Props {
    paper: Paper;
    classes: ClassModel[];
    sections: Section[];
    teachers: Teacher[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.paper.title,
    class_id: props.paper.class_id,
    section_id: props.paper.section_id || '',
    teacher_id: props.paper.teacher_id,
    published: props.paper.published,
    questions: props.paper.questions.map(q => ({
        text: q.text,
        type: q.type,
        options: q.options || ['', '', '', ''],
        answer: q.answer || '',
    })),
});

// Helper function to safely get nested error messages
function getError(key: string): string | undefined {
    return (form.errors as any)[key];
}

const errors = computed(() => form.errors);

function goBack() {
    router.visit(route('papersquestions.index'));
}

function addQuestion() {
    form.questions.push({
        text: '',
        type: '',
        options: ['', '', '', ''],
        answer: '',
    });
}

function removeQuestion(index: number) {
    form.questions.splice(index, 1);
}

function addOption(questionIndex: number) {
    form.questions[questionIndex].options.push('');
}

function removeOption(questionIndex: number, optionIndex: number) {
    form.questions[questionIndex].options.splice(optionIndex, 1);
}

function submitForm() {
    form.put(route('papersquestions.update', props.paper.id), {
        onSuccess: () => {
            toast.success('Paper updated successfully!');
        },
        onError: (errors) => {
            // Show validation errors as toast messages for non-field errors
            if (errors.error) {
                toast.error(errors.error);
            }
        },
    });
}
</script>