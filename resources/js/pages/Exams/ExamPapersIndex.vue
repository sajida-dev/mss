<template>
    <ManageLayout>

        <Head title="Exam Papers" />

        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-5">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Exam Papers</h1>
                <Button v-can="'create-exam-papers'" variant="default" size="lg" @click="openCreateModal">
                    Add Exam Paper
                </Button>
            </div>

            <div v-for="group in groupedExamPapers" :key="group.exam.id" class="mb-10">
                <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 mb-4">
                    {{ group.exam.title }}
                </h2>

                <BaseDataTable :headers="headers" :items="group.papers" :loading="form.processing"
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700">
                    <template #item-paper_name="row">{{ row.paper.title }}</template>
                    <template #item-subject_name="row">{{ row.subject.name }}</template>
                    <template #item-exam_date="row">{{ row.exam_date }}</template>
                    <template #item-time_range="row">
                        {{ row.start_time ?? '—' }} – {{ row.end_time ?? '—' }}
                    </template>
                    <template #item-marks="row">{{ row.passing_marks }} / {{ row.total_marks }}</template>
                    <template #item-actions="row">
                        <button v-can="'update-exam-papers'"
                            class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                            @click="openEditModal(row)" title="Edit" aria-label="Edit Exam Paper">
                            <Icon name="edit" class="w-5 h-5" />
                        </button>
                        <button v-can="'delete-exam-papers'" :disabled="!row.can_be_deleted"
                            class="inline-flex items-center justify-center rounded-full p-2" :class="row.can_be_deleted
                                ? 'text-red-500 focus:ring-2 focus:ring-red-400'
                                : 'text-red-300 cursor-not-allowed'" :title="row.can_be_deleted
                                    ? 'Delete Exam Paper'
                                    : `${row.exam_results_count} result(s) linked. Cannot delete.`"
                            @click="row.can_be_deleted && handleDelete(row)" aria-label="Delete Exam Paper">
                            <Icon name="trash" class="w-5 h-5" />
                        </button>
                    </template>
                </BaseDataTable>
            </div>

        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="modalOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isEdit ? 'Edit Exam Paper' : 'Add Exam Paper' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Exam -->
                    <SelectInput id="exam_id" v-model="form.exam_id" label="Exam" required
                        :options="exams.map(ex => ({ label: ex.title, value: ex.id }))" placeholder="Select Exam"
                        :error="form.errors.exam_id" />

                    <!-- Paper -->
                    <SelectInput id="paper_id" v-model="form.paper_id" label="Paper" required
                        :options="papers.map(p => ({ label: p.title, value: p.id }))" placeholder="Select Paper"
                        :error="form.errors.paper_id" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Exam Date -->
                        <TextInput id="exam_date" v-model="form.exam_date" label="Exam Date" type="date" required
                            :error="form.errors.exam_date" />

                        <!-- Start Time -->
                        <TextInput id="start_time" v-model="form.start_time" label="Start Time" type="time" required
                            :error="form.errors.start_time" />

                        <!-- End Time -->
                        <TextInput id="end_time" v-model="form.end_time" label="End Time" type="time" required
                            :error="form.errors.end_time" />

                        <!-- Total Marks -->
                        <TextInput id="total_marks" v-model.number="form.total_marks" label="Total Marks" type="number"
                            required :error="form.errors.total_marks" />

                        <!-- Passing Marks -->
                        <TextInput id="passing_marks" v-model.number="form.passing_marks" label="Passing Marks" required
                            type="number" :error="form.errors.passing_marks" />
                    </div>

                    <DialogFooter class="mt-4">
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ isEdit ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Exam Paper?</DialogTitle>
                </DialogHeader>
                <div class="mb-4">Are you sure you want to delete this exam paper? This cannot be undone.</div>
                <DialogFooter>
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ManageLayout>
</template>
<script setup lang="ts">
import { ref, watch, defineProps } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';
import ManageLayout from './ManageLayout.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import TextInput from '@/components/form/TextInput.vue';
import { computed } from 'vue';

interface SelectOption { id: number; title: string; }

interface ExamPaper {
    id: number;
    exam_id: number;
    paper_id: number;
    exam_date: string;
    start_time?: string;
    end_time?: string;
    total_marks: number;
    passing_marks: number;
    exam: { id: number; title: string };
    paper: { id: number; title: string };
    subject: { id: number; name: string };
}

const props = defineProps<{
    examPapers: ExamPaper[];
    exams: SelectOption[];
    papers: SelectOption[];
}>();

const examPapers = ref([...props.examPapers]);
const exams = ref([...props.exams]);
const papers = ref([...props.papers]);

watch(() => props.examPapers, (val) => examPapers.value = [...val]);
watch(() => props.exams, (val) => exams.value = [...val]);
watch(() => props.papers, (val) => papers.value = [...val]);

const modalOpen = ref(false);
const isEdit = ref(false);
const editingItem = ref<ExamPaper | null>(null);
const showDeleteDialog = ref(false);
const itemToDelete = ref<ExamPaper | null>(null);

// useForm definition
const form = useForm({
    exam_id: undefined as number | undefined,
    paper_id: undefined as number | undefined,
    exam_date: '',
    start_time: '',
    end_time: '',
    total_marks: 100,
    passing_marks: 40,
});

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Paper', value: 'paper_name', slotName: 'item-paper_name' },
    { text: 'Subject', value: 'subject_name', slotName: 'item-subject_name' },
    { text: 'Date', value: 'exam_date' },
    { text: 'Time', value: 'time_range', slotName: 'item-time_range' },
    { text: 'Marks (Passing/Total)', value: 'marks', slotName: 'item-marks' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];
const groupedExamPapers = computed(() => {
    const grouped: Record<number, { exam: ExamPaper['exam']; papers: ExamPaper[] }> = {};

    examPapers.value.forEach(paper => {
        const examId = paper.exam.id;

        if (!grouped[examId]) {
            grouped[examId] = {
                exam: paper.exam,
                papers: [],
            };
        }

        grouped[examId].papers.push(paper);
    });

    return Object.values(grouped); // returns array of { exam, papers }
});


function openCreateModal() {
    isEdit.value = false;
    editingItem.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEditModal(row: ExamPaper) {
    isEdit.value = true;
    editingItem.value = row;

    form.reset();
    form.exam_id = row.exam_id ?? row.exam?.id ?? null;
    form.paper_id = row.paper_id ?? row.paper?.id ?? null;
    form.exam_date = row.exam_date;
    form.start_time = row.start_time ?? '';
    form.end_time = row.end_time ?? '';
    form.total_marks = row.total_marks;
    form.passing_marks = row.passing_marks;

    form.clearErrors();
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
    form.reset();
    form.clearErrors();
}

function handleSubmit() {
    if (isEdit.value && editingItem.value?.id) {
        form.put(`/exam-papers/${editingItem.value.id}`, {
            onSuccess: () => {
                toast.success('Exam paper updated!');
                closeModal();
                router.reload({ only: ['examPapers'] });
            },
            onError: () => toast.error('Validation failed.'),
        });
    } else {
        form.post('/exam-papers', {
            onSuccess: () => {
                toast.success('Exam paper created!');
                closeModal();
                router.reload({ only: ['examPapers'] });
            },
            onError: () => toast.error('Validation failed.'),
        });
    }
}

function handleDelete(row: { value: ExamPaper }) {
    itemToDelete.value = row.value;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!itemToDelete.value) return;
    console.log('itemToDelete.value', itemToDelete.value);
    form.processing = true;

    router.delete(`/exam-papers/${itemToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Exam paper deleted!');
            showDeleteDialog.value = false;
            router.reload({ only: ['examPapers'] });
        },
        onError: () => toast.error('Deletion failed!'),
        onFinish: () => form.processing = false,
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
}
</script>
