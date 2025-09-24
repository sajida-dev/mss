<template>
    <ManageLayout>

        <Head title="Achievements" />

        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Achievement Types</h2>
                <Button @click="openCreateModal">Add Achievement</Button>
            </div>

            <BaseDataTable :headers="headers" :items="achievements" :loading="loading"
                class="bg-white dark:bg-neutral-900 rounded-xl">
                <template #item-actions="row">
                    <button class="text-blue-500 " @click="edit(row)" title="Edit Achievement">
                        <Edit class="w-5 h-5" />
                    </button>
                    <button class="text-red-500" @click="handleDelete(row)" title="Delete Achievement">
                        <Trash class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>

            <Dialog v-model:open="modalOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ isEdit ? 'Edit Achievement' : 'Add Achievement' }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submit">
                        <TextInput label="Title" v-model="form.title" required :error="form.errors.title" />
                        <TextInput label="Description" v-model="form.description" :error="form.errors.description" />
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="modalOpen = false">Cancel</Button>
                            <Button type="submit">{{ isEdit ? 'Update' : 'Create' }}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </ManageLayout>
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Achievement?</DialogTitle>
            </DialogHeader>
            <div class="mb-4">Are you sure you want to delete this achievement? This action cannot be undone.</div>
            <DialogFooter>
                <Button variant="outline" @click="cancelDelete">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ManageLayout from './ManageLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TextInput from '@/components/form/TextInput.vue';
import { Button } from '@/components/ui/button';
import { toast } from 'vue3-toastify';
import { Edit, Trash } from 'lucide-vue-next';
interface Achievement {
    id: number;
    title: string;
    description?: string;
}
const loading = ref(false);
const props = defineProps<{ achievements: Achievement[] }>();
const achievements = ref<Achievement[]>([...props.achievements]);
const itemToDelete = ref<Achievement | null>(null);
const showDeleteDialog = ref(false);

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Title', value: 'title' },
    { text: 'Description', value: 'description' },
    { text: 'Actions', value: 'actions', slotName: 'item-actions', sortable: false },
];
function handleDelete(row: Achievement) {
    itemToDelete.value = row;
    showDeleteDialog.value = true;
}

function cancelDelete() {
    showDeleteDialog.value = false;
}
const modalOpen = ref(false);
const isEdit = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    title: '',
    description: '',
});

function openCreateModal() {
    isEdit.value = false;
    editingId.value = null;
    form.reset();
    modalOpen.value = true;
}

function edit(achievement: any) {
    isEdit.value = true;
    editingId.value = achievement.id;
    form.title = achievement.title;
    form.description = achievement.description ?? '';
    modalOpen.value = true;
}

function submit() {
    loading.value = true;
    if (isEdit.value && editingId.value) {
        form.put(route('achievements.update', editingId.value), {
            onSuccess: () => {
                modalOpen.value = false
                toast.success('Achievement updated!')
                router.reload({ only: ['achievements'] });
                form.reset();
            },
            onError: (e) => {
                toast.error('Failed to update achievement.' + e.message)
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } else {
        form.post(route('achievements.store'), {
            onSuccess: () => {
                modalOpen.value = false
                toast.success('Achievement created!')
                router.reload({ only: ['achievements'] });
                form.reset();
            },
            onError: (e) => {
                toast.error('Failed to create achievement.' + e.message)
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    }
}


watch(() => props.achievements, (newValue) => {
    if (newValue) {
        achievements.value = [...newValue];
    }
});

function confirmDelete() {
    if (!itemToDelete.value) return;
    loading.value = true;
    router.delete(`/achievements/${itemToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Achievement deleted!')
            showDeleteDialog.value = false;
            router.reload({ only: ['achievements'] });
        },
        onError: (e) => {
            toast.error('Failed to delete achievement.' + e.message)
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}
</script>
