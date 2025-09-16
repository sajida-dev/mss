<template>
    <AppLayout :title="'Results Management'">

        <Head title="Results Management" />

        <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2 mb-6">
                <Users class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                Exam Results
            </h1>
            <div
                class="bg-white grid grid-cols-1  my-5 lg:grid-cols-4 gap-5 dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-3">
                <!-- <h2 class="text-lg font-semibold mb-4">Filters</h2> -->
                <!-- Class -->
                <div class="mb-4">
                    <Label for="class_id" class="block text-sm font-medium dark:text-gray-200">Class</Label>
                    <select id="class_id" v-model="selectedClass"
                        class="mt-1 block w-full px-3 py-2 border rounded-md bg-white dark:bg-neutral-800 dark:border-neutral-600 text-gray-900 dark:text-gray-100">
                        <option value="">Select Class</option>
                        <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                    </select>
                </div>

                <!-- Section -->
                <div class="mb-4">
                    <Label for="section_id" class="block text-sm font-medium dark:text-gray-200">Section</Label>
                    <select id="section_id" v-model="selectedSection" :disabled="!selectedClass"
                        class="mt-1 block w-full px-3 py-2 border rounded-md bg-white dark:bg-neutral-800 dark:border-neutral-600 text-gray-900 dark:text-gray-100">
                        <option value="">All Sections</option>
                        <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}
                        </option>
                    </select>
                </div>

                <!-- Term -->
                <div class="mb-4">
                    <Label for="term" class="block text-sm font-medium dark:text-gray-200">Term</Label>
                    <select id="term" v-model="selectedTerm"
                        class="mt-1 block w-full px-3 py-2 border rounded-md bg-white dark:bg-neutral-800 dark:border-neutral-600 text-gray-900 dark:text-gray-100">
                        <option value="">Select Term</option>
                        <option v-for="(label, value) in terms" :key="value" :value="value">{{ label }}</option>
                    </select>
                </div>

                <!-- Exam filter -->
                <div class="mb-4">
                    <Label for="exam_paper" class="block text-sm font-medium dark:text-gray-200">Exam</Label>
                    <select id="exam_paper" v-model="selectedExam"
                        class="mt-1 block w-full px-3 py-2 border rounded-md bg-white dark:bg-neutral-800 dark:border-neutral-600 text-gray-900 dark:text-gray-100">
                        <option value="">All Exams</option>
                        <option v-for="ep in examPapersList" :key="ep.id" :value="ep.id">
                            {{ ep.subject.name }} ‒ {{ ep.exam.title }}
                        </option>
                    </select>
                </div>

            </div>
            <div class=" flex flex-col lg:flex-row gap-6" v-if="results.length > 0">

                <!-- Left panel: Filters & Student List -->
                <div class="w-full lg:w-1/3 space-y-6">
                    <!-- Filters Section -->

                    <!-- Student list -->
                    <div
                        class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-y-auto">
                        <div class="p-4 border-b border-gray-200 dark:border-neutral-700">
                            <h2 class="text-lg font-semibold dark:text-gray-100">Students</h2>
                        </div>
                        <ul>
                            <li v-for="res in results" :key="res.student.id" @click="selectStudent(res)"
                                :class="['cursor-pointer px-4 py-2 border-b', selectedStudent && selectedStudent.student.id === res.student.id ? 'bg-gray-100 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-neutral-800']">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium dark:text-gray-100">{{ res.student.name }}</p>
                                        <p class="text-xs dark:text-gray-400">Reg#: {{ res.student.registration_number
                                        }}
                                        </p>
                                    </div>
                                    <div class="text-sm dark:text-gray-200">
                                        {{ res.percentage }}%
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="selectedStudent"
                    class="bg-white dark:bg-neutral-900 w-full rounded-xl border border-gray-200 dark:border-neutral-700 p-6 ">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ selectedStudent.student.name }} — Detailed Results for {{ terms[selectedTerm] }}
                    </h2>

                    <div v-if="selectedStudent.term_has_results">
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Subject</th>

                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Obtained</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Percentage</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Remarks</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Marked By</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                                    <tr v-for="item in selectedStudent.results" :key="item.subject_id"
                                        class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.subject_name }}</td>

                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.obtained_marks }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.total_marks }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.percentage }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.status }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.remarks }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.marked_by }}</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <strong>Total:</strong> {{ selectedStudent.total_obtained_marks }} / {{
                                    selectedStudent.total_possible_marks }}
                            </p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <strong>Percentage:</strong> {{ selectedStudent.percentage }}%
                            </p>
                        </div>
                    </div>
                    <div v-else class="mt-4 text-yellow-700 dark:text-yellow-300">
                        <p>No results calculated yet for this term.</p>
                    </div>
                </div>

                <div v-else class="text-center justify-center items-center text-gray-500 dark:text-gray-400 mt-6">
                    <p>Select a student to view their detailed results.</p>
                </div>

            </div>
            <!-- Empty State when no class selected -->
            <div v-else class="text-center py-12">
                <div
                    class="w-20 h-20 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <Building2 class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3">Select a Class and Term</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    Choose a class from the dropdown above to view and manage subject assignments
                </p>
            </div>

        </div>


    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Label } from '@/components/ui/label';
import { Building2, Users, Calendar } from 'lucide-vue-next';
import axios from 'axios';

interface Student {
    id: number;
    name: string;
    registration_number: string;
    section?: { name: string };
}

interface ResultItem {
    subject_id: number;
    subject_name: string;
    percentage: number;
    obtained_marks: number;
    total_marks: number;
    status: string;
    promotion_status: string;
    remarks: string;
    marked_by: string | null;
}

interface Result {
    student: Student;
    results: ResultItem[];
    total_possible_marks: number;
    total_obtained_marks: number;
    percentage: number;
    term_has_results: boolean;
}

interface ExamPaper {
    id: number;
    exam: { title: string };
    subject: { name: string };
}

interface Props {
    classes: { id: number; name: string }[];
    sections: { id: number; name: string }[];
    examPapersList: ExamPaper[];
    results: Result[];
    // initial filter values from controller
    selectedClass?: string;
    selectedSection?: string;
    selectedExam?: string;
    selectedTerm?: string;
    terms: Record<string, string>;
}

const props = defineProps<Props>();

// Reactive filter state
const selectedClass = ref(props.selectedClass || '');
const selectedSection = ref(props.selectedSection || '');
const selectedExam = ref(props.selectedExam || '');
const selectedTerm = ref(props.selectedTerm || '');

// List of sections to choose from (filtered by class)
const sections = ref<Array<{ id: number; name: string }>>(props.sections);

const classes = props.classes;
const examPapersList = props.examPapersList;

const terms = props.terms;

// Results returned from backend
const results = ref<Result[]>(props.results);


// Currently selected student
const selectedStudent = ref<Result | null>(null);

// Computed display names
const selectedClassName = computed(() => {
    const cls = classes.find(c => c.id.toString() === selectedClass.value.toString());
    return cls ? cls.name : '';
});

// Exam name
const selectedExamName = computed(() => {
    const ep = examPapersList.find(e => e.id.toString() === selectedExam.value.toString());
    return ep ? `${ep.subject.name} ‒ ${ep.exam.title}` : '';
});

// Watch filters and fire Inertia visit
watch([selectedClass, selectedSection, selectedExam, selectedTerm], ([c, s, e, t]) => {
    router.visit(route('exam-results.index'), {
        data: {
            class_id: c,
            section_id: s,
            exam_paper_id: e,
            term: t,
        },
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { immediate: true });

// Watch class change to reload sections list
watch(selectedClass, (newClass) => {
    if (newClass) {
        // fetch sections via AJAX or Inertia endpoint, example using axios
        axios.get(`/api/classes/${newClass}/sections`)
            .then(resp => {
                sections.value = resp.data;
                // optionally reset selectedSection
                if (!sections.value.find(sec => sec.id.toString() === selectedSection.value.toString())) {
                    selectedSection.value = '';
                }
            })
            .catch(() => {
                sections.value = [];
                selectedSection.value = '';
            });
    } else {
        sections.value = [];
        selectedSection.value = '';
    }
});

function selectStudent(res: Result) {
    selectedStudent.value = res;
}

watch(() => props.results, (newResults) => {
    results.value = newResults;
    if (newResults && newResults.length > 0) {
        selectedStudent.value = newResults[0];
    } else {
        selectedStudent.value = null;
    }
}, { immediate: true });
</script>
