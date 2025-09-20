<script setup lang="ts">
import { watch, ref, computed, reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import { Plus } from 'lucide-vue-next';
interface Student {
    id: number;
    name: string;
    registration_number: string;
}

interface ExamPaper {
    id: number;
    exam: { id: number; title: string };
    paper: { id: number; title: string };
    subject: { id: number; name: string };
}

interface ExamResult {
    student_id: number;
    obtained_marks: string;
    total_marks: string;
    remarks: string;
    images?: File[];
}

interface ExamResultForm {
    school_id: string;
    class_id: string;
    exam_paper_id: string;
    results: ExamResult[];
    [key: string]: any;
}

interface Props {
    schools: { id: number; name: string }[];
    classes: { id: number; name: string }[];
    students: Student[];
    examPapers: ExamPaper[];
    selectedSchoolId?: number;
    selectedClassId?: number;
    selectedExamPaperId?: number;
    noExamExists: boolean;
    noExamPapers: boolean;
}
const props = defineProps<Props>();
const studentImagePreviews = ref<Record<number, string[]>>({});
const form = useForm<ExamResultForm>({
    school_id: props.selectedSchoolId ? String(props.selectedSchoolId) : '',
    class_id: props.selectedClassId ? String(props.selectedClassId) : '',
    exam_paper_id: props.selectedExamPaperId ? String(props.selectedExamPaperId) : '',
    results: props.students.map(student => ({
        student_id: student.id,
        obtained_marks: '',
        total_marks: '100',
        remarks: '',
    })),

});
const fileInputs = reactive<Record<number, HTMLInputElement | null>>({});

function triggerFileInput(index: number) {
    const input = fileInputs[index];
    if (input) input.click();
}

const errors = computed(() => form.errors as Record<string, string | undefined>);
function onStudentImagesChange(event: Event, index: number) {
    const target = event.target as HTMLInputElement;
    const files = target.files;

    if (files && files.length > 0) {
        const file = files[0]; // only allow 1 image
        form.results[index].images = [file];
        studentImagePreviews.value[index] = [URL.createObjectURL(file)];
    } else {
        form.results[index].images = [];
        studentImagePreviews.value[index] = [];
    }
}

function removeStudentImage(index: number) {
    form.results[index].images = [];
    studentImagePreviews.value[index] = [];
}

function onSchoolChange() {
    form.class_id = '';
    form.exam_paper_id = '';
    router.get(route('exam-results.create'), {
        school_id: form.school_id,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function onClassChange() {
    form.exam_paper_id = '';
    router.get(route('exam-results.create'), {
        school_id: form.school_id,
        class_id: form.class_id,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function onExamPaperChange() {
    router.get(route('exam-results.create'), {
        school_id: form.school_id,
        class_id: form.class_id,
        exam_paper_id: form.exam_paper_id,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function submitForm() {
    let valid = true;

    form.results.forEach(result => {
        if (!result.obtained_marks || !result.total_marks) {
            valid = false;
            toast.error('Please enter all required marks.');
        }
    });

    if (!valid) return;
    const formData = new FormData();

    formData.append('school_id', form.school_id);
    formData.append('class_id', form.class_id);
    formData.append('exam_paper_id', form.exam_paper_id);

    form.results.forEach((result, i) => {
        formData.append(`results[${i}][student_id]`, String(result.student_id));
        formData.append(`results[${i}][obtained_marks]`, result.obtained_marks);
        formData.append(`results[${i}][total_marks]`, result.total_marks);
        formData.append(`results[${i}][remarks]`, result.remarks);

        if (result.images?.length) {
            result.images.forEach((file, j) => {
                formData.append(`results[${i}][images][${j}]`, file);
            });
        }
    });

    router.post(route('exam-results.store'), formData, {
        forceFormData: true,
        onSuccess: () => toast.success('Results saved successfully!'),
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => {
                if (typeof message === 'string') toast.error(message);
            });
        },
    });
}
watch(() => props.students, (newStudents) => {
    form.results = newStudents.map(student => ({
        student_id: student.id,
        obtained_marks: '',
        total_marks: '100',
        remarks: '',
        images: [],
    }));
}, { immediate: true });

function getResultFieldError(index: number, field: keyof ExamResult): string | undefined {
    const key = `results.${index}.${field}` as keyof typeof form.errors;
    return form.errors[key] as string | undefined;
}



</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full px-4 py-10">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Enter Subject Exam Results</h1>
                    <Button variant="outline" @click="$inertia.visit(route('exam-results.index'))">Back</Button>
                </div>
                <div v-if="noExamExists"
                    class="text-center flex flex-col items-center gap-4 my-6 p-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                    <span> ⚠️ No exam has been created for this class yet. Please schedule an exam before entering
                        results.</span>
                    <Button v-can="'create-exams'" variant="default" size="sm" class="px-4 py-2 text-sm"
                        @click="router.visit(route('exams.index'))">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Exams
                    </Button>
                </div>

                <div v-else-if="noExamPapers"
                    class="text-center flex flex-col items-center gap-4 my-6 p-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                    <span> ⚠️ The selected exam does not have any subjects added yet. Please add exam papers before
                        entering results.</span>
                    <Button v-can="'create-exam-papers'" variant="default" size="sm" class="px-4 py-2 text-sm"
                        @click="router.visit(route('exam-papers.index'))">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Exam Paper
                    </Button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- School -->
                        <SelectInput id="school_id" v-model="form.school_id" label="School"
                            :options="props.schools.map(s => ({ label: s.name, value: String(s.id) }))"
                            placeholder="Select School" :error="errors.school_id" @change="onSchoolChange" />

                        <!-- Class -->
                        <SelectInput id="class_id" v-model="form.class_id" label="Class"
                            :options="props.classes.map(c => ({ label: c.name, value: String(c.id) }))"
                            placeholder="Select Class" :disabled="!form.school_id" :error="errors.class_id"
                            @change="onClassChange" />

                        <!-- Exam Paper -->
                        <SelectInput id="exam_paper_id" v-model="form.exam_paper_id" label="Exam Paper"
                            class="col-span-2"
                            :options="props.examPapers.map(e => ({ label: `${e.subject.name} - ${e.exam.title} `, value: String(e.id) }))"
                            placeholder="Select Exam Paper" :disabled="!form.class_id" :error="errors.exam_paper_id"
                            @change="onExamPaperChange" />
                    </div>

                    <!-- Students Table -->
                    <div v-if="props.students.length" class="overflow-x-auto mt-6">
                        <table class="w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-neutral-800 text-left">
                                    <th class="p-2 text-sm">#</th>
                                    <th class="p-2 text-sm">Student</th>
                                    <th class="p-2 text-sm flex">Obtained Marks <span class="text-red-500">*</span>
                                    </th>
                                    <th class="p-2 text-sm">Total Marks</th>
                                    <th class="p-2 text-sm">Remarks</th>
                                    <th class="p-2 text-sm">Paper Image<span class="text-red-500">*</span></th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(result, index) in form.results" :key="index"
                                    class="border-b dark:border-neutral-700">
                                    <td class="p-2 text-sm">{{ index + 1 }}</td>
                                    <td class="p-2 text-sm">
                                        {{ props.students[index].name }} (Reg# {{
                                            props.students[index].registration_number }})
                                    </td>

                                    <!-- Obtained Marks -->
                                    <td class="p-2">
                                        <TextInput v-model="result.obtained_marks" type="number" min="0" max="100"
                                            class="w-24" :error="getResultFieldError(index, 'obtained_marks')"
                                            placeholder="Marks" />
                                    </td>

                                    <!-- Total Marks -->
                                    <td class="p-2">
                                        <TextInput v-model="result.total_marks" type="number" min="0" class="w-24"
                                            disabled :error="getResultFieldError(index, 'total_marks')"
                                            placeholder="100" />

                                    </td>

                                    <!-- Remarks -->
                                    <td class="p-2">
                                        <TextInput v-model="result.remarks" type="text" class="w-full"
                                            :error="getResultFieldError(index, 'remarks')" placeholder="Remarks" />
                                    </td>
                                    <!-- Paper Image Upload -->
                                    <td class="p-2 text-sm">
                                        <div class="relative border-2 border-dashed rounded-md min-h-[100px] w-28 flex items-center justify-center cursor-pointer hover:border-blue-400 transition-all"
                                            @click="triggerFileInput(index)">
                                            <!-- Show image preview -->
                                            <template v-if="studentImagePreviews[index]?.length">
                                                <div class="w-24 h-24 overflow-hidden rounded border relative group">
                                                    <img :src="studentImagePreviews[index][0]"
                                                        class="w-full h-full object-cover" alt="Preview" />
                                                    <!-- Remove button -->
                                                    <button type="button"
                                                        class="absolute top-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-bl"
                                                        @click.stop="removeStudentImage(index)">
                                                        ✕
                                                    </button>
                                                </div>
                                            </template>

                                            <!-- Placeholder if no image -->
                                            <template v-else>
                                                <div class="text-gray-500 text-center text-xs px-2">
                                                    <p><strong>Upload</strong></p>
                                                    <p class="text-[10px] mt-1">Click to add image</p>
                                                </div>
                                            </template>

                                            <!-- Hidden file input -->
                                            <input type="file" accept="image/*" class="hidden"
                                                :ref="el => (fileInputs[index] = el as HTMLInputElement | null)"
                                                @change="(e) => onStudentImagesChange(e, index)" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else
                        class="text-center py-4 px-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                        <p class="text-yellow-700 dark:text-yellow-200 font-medium">⚠️ No Students Found.</p>
                        <p class="text-yellow-600 dark:text-yellow-300 text-sm">
                            Please select a class with admitted students.</p>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('exam-results.index'))">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing || !props.students.length">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else-if="!props.students.length">No Students Available</span>
                            <span v-else>Save Results</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
