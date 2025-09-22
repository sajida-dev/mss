<template>
    <AppLayout :title="'Results Management'">

        <Head title="Results Management" />
        <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-2 mx-2">
                <h1
                    class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2 mb-6">
                    <Users class="h-5 w-5 lg:h-6 lg:w-6 text-gray-500 dark:text-gray-400" />
                    Exam Results
                </h1>
                <Button v-can="'create-exam-results'" variant="default" class="h-10  hidden lg:flex"
                    @click="router.visit(route('exam-results.create'))">
                    <Plus class="w-4 h-4" />
                    Add Exam Result
                </Button>

                <!-- Filters -->
                <div class="flex lg:hidden justify-between items-center mb-4 mx-2 gap-3">
                    <button @click="openFilterSheet"
                        class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition">
                        <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                        <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                    </button>
                </div>
            </div>

            <div
                class="hidden lg:flex bg-white my-3 gap-5 dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-3">
                <ExamResultFilterFields v-model:selectedClass="selectedClass" v-model:selectedSection="selectedSection"
                    v-model:selectedTerm="selectedTerm" v-model:selectedExam="selectedExam"
                    v-model:selectedAcademicYear="selectedAcademicYear" :classes="classes" :sections="sections"
                    :terms="terms" :academic-years="props.academicYears" :exams="props.exams" />
            </div>

            <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
                ref="showFilterSheet" title="Filter Results">
                <div class="sheet-content dark:bg-neutral-900">
                    <h2 class="text-lg font-semibold mb-4">Teacher Filters</h2>
                    <div class="space-y-4 px-4 py-2">
                        <ExamResultFilterFields v-model:selectedClass="selectedClass"
                            v-model:selectedSection="selectedSection" v-model:selectedTerm="selectedTerm"
                            v-model:selectedExam="selectedExam" v-model:selectedAcademicYear="selectedAcademicYear"
                            :classes="classes" :sections="sections" :terms="terms" :academic-years="props.academicYears"
                            :exams="props.exams" />
                    </div>
                </div>
            </vue-bottom-sheet>

            <!-- Results View -->
            <div v-if="results.length > 0">
                <!-- Desktop: Side-by-side -->
                <div class="hidden lg:flex flex-row gap-6">
                    <!-- Student List -->
                    <div class="w-1/3 space-y-6">
                        <StudentList :results="results" :selectedStudent="selectedStudent" @select="selectStudent" />
                    </div>

                    <!-- Student Detail -->
                    <StudentDetail v-if="selectedStudent" :student="selectedStudent" :terms="terms" />
                </div>

                <!-- Mobile: Conditional views -->
                <div class="lg:hidden">
                    <div v-if="!selectedStudent">
                        <StudentList :results="results" @select="selectStudent" />
                    </div>

                    <div v-else>
                        <div class="mb-4 flex justify-between items-center">
                            <Button variant="outline" @click="selectedStudent = null" class="text-sm">
                                ← Back to Students List
                            </Button>
                            <Button v-can="'create-exam-results'" variant="default" class="h-10"
                                @click="router.visit(route('exam-results.create'))">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Result
                            </Button>
                        </div>
                        <StudentDetail :student="selectedStudent" :terms="terms" />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div
                    class="w-20 h-20 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <Building2 class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3">
                    Select a Class, Term, Academic Year and Exam
                </h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    Use the filters to load student exam results.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Building2, FilterIcon, Plus, Users } from 'lucide-vue-next';
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import ExamResultFilterFields from '@/components/ui/ExamResultFilterFields.vue';
import Button from '@/components/ui/button/Button.vue';
import axios from 'axios';
import StudentDetail from '@/components/results/StudentDetail.vue';
import StudentList from '@/components/results/StudentList.vue';

interface Props {
    classes: { id: number; name: string }[];
    sections: { id: number; name: string }[];
    exams: { id: number; title: string }[];
    studentsGrouped: StudentGrouped[];
    academicYears: { id: number; name: string }[];
    selectedClass?: string;
    selectedSection?: string;
    selectedExam?: string;
    selectedTerm?: string;
    selectedAcademicYear?: string;
    terms: Record<string, string>;
}
interface StudentGrouped {
    student: {
        id: number;
        name: string;
        registration_number: string;
        class_name: string;
    };
    grouped_terms: {
        year_name: string;
        terms: Record<
            string,
            {
                term_name: string;
                exam_type_id: number;
                items: ResultItem[];
                term_result: {
                    total_marks: number;
                    obtained_marks: number;
                    overall_percentage: number;
                    subjects_passed: number;
                    subjects_failed: number;
                    grade: string;
                    remarks: string;
                    term_status: string;
                } | null;
            }
        >;
        all_terms_completed: boolean;
        academic_result: {
            overall_percentage: number;
            cumulative_gpa: number;
            final_grade: string;
            promotion_status: string;
        } | null;
    };
}


interface ResultItem {
    subject_id: number;
    subject_name: string;
    obtained_marks: number;
    total_marks: number;
    percentage: number;
    status: string;
    promotion_status: string;
    remarks: string;
    marked_by_name: string;
}

const props = defineProps<Props>();

const selectedClass = ref(props.selectedClass || '');
const selectedSection = ref(props.selectedSection || '');
const selectedExam = ref(props.selectedExam || '');
const selectedTerm = ref(props.selectedTerm || '');
const selectedAcademicYear = ref(props.selectedAcademicYear || '');
const sections = ref(props.sections);
const results = ref<StudentGrouped[]>(props.studentsGrouped);
const selectedStudent = ref<StudentGrouped | null>(null);
const filterSheet = ref<InstanceType<typeof VueBottomSheet>>();

function openFilterSheet() {
    filterSheet.value?.open();
}

function closeFilterSheet() {
    filterSheet.value?.close();
}
function selectStudent(res: StudentGrouped) {
    selectedStudent.value = res;
}


watch(() => props.studentsGrouped, (newResults) => {
    results.value = newResults;
    if (newResults.length > 0) {
        selectedStudent.value = newResults[0];
    }
}, { immediate: true });

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

watch(selectedClass, (newClass) => {
    if (newClass) {
        axios.get(`/api/classes/${newClass}/sections`)
            .then(resp => {
                sections.value = resp.data;
                if (!sections.value.find(sec => sec.id.toString() === selectedSection.value.toString())) {
                    selectedSection.value = '';
                }
            }).catch(() => {
                sections.value = [];
                selectedSection.value = '';
            });
    } else {
        sections.value = [];
        selectedSection.value = '';
    }
});
</script>
