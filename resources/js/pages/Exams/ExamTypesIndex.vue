<template>
    <ManageLayout>

        <Head title="Exam Types" />
        <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Exam Types</h1>
                <Button v-can="'create-exam-types'" variant="default" size="lg" @click="openCreateModal">
                    Add Exam Type
                </Button>
            </div>

            <BaseDataTable :headers="headers" :items="examTypes" :loading="loading"
                class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700">
                <template #item-is_final_term="row">
                    <span :class="row.is_final_term ? 'text-green-800' : 'text-gray-500'">
                        {{ row.is_final_term ? 'Yes' : 'No' }}
                    </span>
                </template>

                <template #item-actions="row">
                    <button v-can="'update-exam-types'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="openEditModal(row)" aria-label="Edit Exam Type" title="Edit">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-exam-types'" :disabled="row.exams_count > 0"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-300 cursor-not-allowed"
                        :title="row.exams_count > 0 ? `${row.exams_count} exams exist for this type.` : 'Delete exam type'">
                        <Icon name="trash" class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="modalOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isEdit ? 'Edit Exam Type' : 'Add Exam Type' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="handleSubmit">
                    <div class="mb-4 flex flex-col gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" type="text" required placeholder="e.g., 1st Term" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="mb-4 flex flex-col gap-2">
                        <Label for="code">Code</Label>
                        <Input id="code" v-model="form.code" type="text" required placeholder="e.g., 1st_term" />
                        <InputError :message="form.errors.code" />
                    </div>

                    <div class="mb-4 flex items-center gap-2">
                        <input id="is_final_term" type="checkbox" v-model="form.is_final_term" />
                        <Label for="is_final_term">Is Final Term?</Label>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button :disabled="loading">{{ isEdit ? 'Update' : 'Create' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Exam Type?</DialogTitle>
                </DialogHeader>
                <div class="mb-4">Are you sure you want to delete this exam type?</div>
                <DialogFooter>
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ManageLayout>
</template>

<script setup lang="ts">
import { ref, watch, reactive, defineProps } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';
import ManageLayout from './ManageLayout.vue';

interface ExamType {
    id: number;
    name: string;
    code: string;
    is_final_term: boolean;
}

const props = defineProps<{ examTypes?: ExamType[] }>();
const examTypes = ref<ExamType[]>(props.examTypes ?? []);

watch(() => props.examTypes, (val) => {
    if (val) {
        examTypes.value = [...val];
    }
});

const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingItem = ref<ExamType | null>(null);
const showDeleteDialog = ref(false);
const itemToDelete = ref<ExamType | null>(null);

const form = reactive({
    id: null as number | null,
    name: '',
    code: '',
    is_final_term: false,
    errors: {} as Record<string, string>,
});

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Code', value: 'code' },
    { text: 'Final Term', value: 'is_final_term', sortable: false, slotName: 'item-is_final_term' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

function openCreateModal() {
    isEdit.value = false;
    editingItem.value = null;
    resetForm();
    modalOpen.value = true;
}

function openEditModal(item: ExamType) {
    isEdit.value = true;
    editingItem.value = item;
    form.id = item.id;
    form.name = item.name;
    form.code = item.code;
    form.is_final_term = Boolean(item.is_final_term);
    form.errors = {};
    modalOpen.value = true;
}

function resetForm() {
    form.id = null;
    form.name = '';
    form.code = '';
    form.is_final_term = false;
    form.errors = {};
}

function closeModal() {
    modalOpen.value = false;
    editingItem.value = null;
    resetForm();
}

function handleSubmit() {
    loading.value = true;
    form.errors = {};

    const payload = {
        name: form.name,
        code: form.code,
        is_final_term: form.is_final_term,
    };

    if (isEdit.value && form.id !== null) {
        // Update
        router.put(`/exam-types/${form.id}`, payload, {
            onSuccess: () => {
                toast.success('Exam Type updated!');
                closeModal();
                router.reload({ only: ['examTypes'] });
            },
            onError: (errors) => {
                form.errors = errors;
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } else {
        // Create
        router.post('/exam-types', payload, {
            onSuccess: () => {
                toast.success('Exam Type created!');
                closeModal();
                router.reload({ only: ['examTypes'] });
            },
            onError: (errors) => {
                form.errors = errors;
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    }
}

function handleDelete(item: ExamType) {
    itemToDelete.value = item;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!itemToDelete.value) return;

    loading.value = true;
    router.delete(`/exam-types/${itemToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Exam type deleted!');
            showDeleteDialog.value = false;
            itemToDelete.value = null;
            router.reload({ only: ['examTypes'] });
        },
        onError: () => {
            toast.error('Failed to delete exam type.');
        },
        onFinish: () => {
            loading.value = false;
            itemToDelete.value = null;
        },
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
    itemToDelete.value = null;
}
</script>
