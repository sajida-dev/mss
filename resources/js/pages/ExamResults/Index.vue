<template>
    <AppLayout :title="'Results Management'">

        <Head title="Results Management" />
        <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2 mb-6">
                    <Users class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                    Exam Results

                </h1>
                <Button v-can="'create-exam-results'" variant="default" class="h-10"
                    @click="router.visit(route('exam-results.create'))">
                    <Plus class="w-4 h-4" />
                    Add Exam Result
                </Button>
            </div>

            <!-- Mobile Filter Icon with Tooltip and Label -->
            <div class="flex lg:hidden justify-between items-center mb-4 gap-3">
                <Button v-can="'create-exam-results'" variant="default" class="h-10"
                    @click="router.visit(route('exam-results.create'))">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Exam Result
                </Button>
                <button @click="open"
                    class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition"
                    title="Show filters for fee records">
                    <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                    <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                </button>
            </div>
            <!-- Desktop Filter -->
            <div
                class="hidden lg:flex bg-white  my-5  gap-5 dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-3">
                <ExamResultFilterFields v-model:selectedClass="selectedClass" v-model:selectedSection="selectedSection"
                    v-model:selectedTerm="selectedTerm" v-model:selectedExam="selectedExam"
                    v-model:selectedAcademicYear="selectedAcademicYear" :classes="classes" :sections="sections"
                    :terms="terms" :academic-years="props.academicYears" :exams="props.exams" />
            </div>

            <!-- Mobile Bottom Sheet Filter -->
            <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
                v-model:open="showFilterSheet" title="Filter Results">
                <div class="space-y-4 px-4 py-2">
                    <ExamResultFilterFields v-model:selectedClass="selectedClass"
                        v-model:selectedSection="selectedSection" v-model:selectedTerm="selectedTerm"
                        v-model:selectedExam="selectedExam" v-model:selectedAcademicYear="selectedAcademicYear"
                        :classes="classes" :sections="sections" :terms="terms" :academic-years="props.academicYears"
                        :exams="props.exams" />
                </div>
            </vue-bottom-sheet>


            <div class="flex flex-col lg:flex-row gap-6" v-if="results.length > 0">
                <!-- Student List Panel -->
                <div class="w-full lg:w-1/3 space-y-6">
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
                                            }}</p>
                                    </div>
                                    <div class="text-sm dark:text-gray-200">
                                        {{ res.term_has_results ? res.percentage + '%' : 'N/A' }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Student Result Details -->
                <div v-if="selectedStudent"
                    class="bg-white dark:bg-neutral-900 w-full rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ selectedStudent.student.name }} — Detailed Results for {{ terms[selectedTerm] }}
                    </h2>

                    <div>
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Subject</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Obtained</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Total</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Percentage</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Status</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Remarks</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Marked By</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                                    <tr v-for="item in selectedStudent.student.results" :key="item.subject_id"
                                        class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.subject_name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.obtained_marks }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.total_marks }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.percentage }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.status }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.remarks
                                            }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.marked_by
                                            }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="selectedStudent.term_has_results" class="mt-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Total:</strong> {{
                                selectedStudent.total_obtained_marks }} / {{ selectedStudent.total_possible_marks }}</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Percentage:</strong> {{
                                selectedStudent.percentage }}%</p>
                        </div>
                        <div v-else class="mt-4 text-yellow-700 dark:text-yellow-300">
                            <p>No results calculated yet for this term.</p>
                        </div>
                    </div>

                </div>

                <div v-else class="text-center justify-center items-center text-gray-500 dark:text-gray-400 mt-6">
                    <p>Select a student to view their detailed results.</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div
                    class="w-20 h-20 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <Building2 class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3">Select a Class, Term, Academic
                    Year and Exam to filter students to view their result </h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">Details view of student result including
                    term results and complete academic record.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Label } from '@/components/ui/label';
import { Building2, FilterIcon, Plus, Users } from 'lucide-vue-next';
import axios from 'axios';
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import ExamResultFilterFields from '@/components/ui/ExamResultFilterFields.vue';
import Button from '@/components/ui/button/Button.vue';


interface Student {
    id: number;
    name: string;
    registration_number: string;
    section?: { name: string };
    results: ResultItem[];
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
    marked_by: { name: string }
}

interface Result {
    student: Student;
    results: ResultItem[];
    total_possible_marks?: number;
    total_obtained_marks?: number;
    percentage?: number;
    term_has_results: boolean;
    message?: string;
}

interface Exam {
    id: number;
    title: string;
}
const myBottomSheet = ref<InstanceType<typeof VueBottomSheet>>()

const open = () => {
    myBottomSheet?.value?.open();
}
interface Props {
    classes: { id: number; name: string }[];
    sections: { id: number; name: string }[];
    exams: Exam[];
    results: Result[];
    academicYears: { id: number; year: string }[];
    selectedClass?: string;
    selectedSection?: string;
    selectedExam?: string;
    selectedTerm?: string;
    selectedAcademicYear?: string;
    terms: Record<string, string>;
}

const props = defineProps<Props>();

const selectedClass = ref(props.selectedClass || '');
const selectedSection = ref(props.selectedSection || '');
const selectedExam = ref(props.selectedExam || '');
const selectedTerm = ref(props.selectedTerm || '');
const selectedAcademicYear = ref(props.selectedAcademicYear || '');

const sections = ref(props.sections);
const classes = props.classes;
const exams = props.exams;
const terms = props.terms;
const results = ref<Result[]>(props.results);
const selectedStudent = ref<Result | null>(null);

const showFilterSheet = ref(false);

function selectStudent(res: Result) {
    selectedStudent.value = res;
}

watch(() => props.results, (newResults) => {
    results.value = newResults;
    if (newResults.length > 0) {
        selectedStudent.value = newResults[0];
    } else {
        selectedStudent.value = null;
    }
}, { immediate: true });

console.log('selectedStudent', selectedStudent)
watch([selectedClass, selectedSection, selectedExam, selectedTerm, selectedAcademicYear], ([c, s, e, t, ay]) => {
    router.visit(route('exam-results.index'), {
        data: {
            class_id: c,
            section_id: s,
            exam_id: e,
            term: t,
            academic_year_id: ay,
        },
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

// Reload sections when class changes
watch(selectedClass, (newClass) => {
    if (newClass) {
        axios.get(`/api/classes/${newClass}/sections`)
            .then(resp => {
                sections.value = resp.data;
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
</script>

]
<style scoped>
/* Add styling for your bottom sheet if needed */
</style>
