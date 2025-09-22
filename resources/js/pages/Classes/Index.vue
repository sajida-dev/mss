<template>

    <Head title="Classes" />
    <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Classes</h1>
            <Button v-can="'create-classes'" variant="default" size="lg" class="mr-2" @click="openCreateModal">Add
                Class</Button>
        </div>
        <BaseDataTable :headers="headers" :items="classes" :loading="loading"
            class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700">
            <template #item-actions="row">
                <button v-can="'update-classes'"
                    class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                    @click="openEditModal(row)" aria-label="Edit Class" title="Edit Class">
                    <Icon name="edit" class="w-5 h-5" />
                </button>
                <button v-can="'delete-classes'"
                    class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                    @click="handleDelete(row)" aria-label="Delete Class" title="Delete Class">
                    <Icon name="trash" class="w-5 h-5" />
                </button>
            </template>
        </BaseDataTable>
    </div>
    <!-- Create/Edit Modal -->
    <Dialog v-model:open="modalOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ isEdit ? 'Edit Class' : 'Add Class' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="handleSubmit">
                <div class="mb-4 flex flex-col gap-2">
                    <Label for="name">Class Name</Label>
                    <Input id="name" v-model="form.name" type="text" required placeholder="Class name" />
                    <InputError :message="form.errors.name" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                    <Button :disabled="loading">{{ isEdit ? 'Update' : 'Create' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Class?</DialogTitle>
            </DialogHeader>
            <div class="mb-4">Are you sure you want to delete this class? This action cannot be undone.</div>
            <DialogFooter>
                <Button variant="outline" @click="cancelDelete">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, defineProps } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';

const props = defineProps<{ classes: Array<{ id: number; name: string }> }>();
const classes = ref(props.classes ? [...props.classes] : []);
watch(() => props.classes, (val) => {
    classes.value = val ? [...val] : [];
});
const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingClass = ref<{ id: number; name: string } | null>(null);
const showDeleteDialog = ref(false);
const classToDelete = ref<{ id: number; name: string } | null>(null);
const form = ref({ name: '', errors: { name: '' } });

const breadcrumbItems = [
    { title: 'Classes', href: '/classes' },
];

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Class Name', value: 'name' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

function openCreateModal() {
    isEdit.value = false;
    form.value = {
        name: '',
        errors: { name: '' }
    };
    modalOpen.value = true;
}

function openEditModal(cls: { id: number; name: string }) {
    isEdit.value = true;
    editingClass.value = cls;
    form.value = {
        name: cls.name,
        errors: { name: '' }
    };
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
    form.value = {
        name: '',
        errors: { name: '' }
    };
    editingClass.value = null;
}

async function handleSubmit() {
    loading.value = true;
    form.value.errors = { name: '' };

    try {
        if (isEdit.value && editingClass.value) {
            // Update existing class
            router.put(`/classes/${editingClass.value.id}`, {
                name: form.value.name
            }, {
                onSuccess: () => {
                    toast.success('Class updated!');
                    closeModal();
                    router.reload({ only: ['classes'] });
                },
                onError: (errors) => {
                    if (errors.name) {
                        form.value.errors.name = errors.name;
                    } else if (typeof errors === 'string') {
                        form.value.errors.name = errors;
                    } else {
                        form.value.errors.name = 'Update failed.';
                    }
                    console.error('Update errors:', errors);
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        } else {
            // Create new class
            const payload: any = { name: form.value.name };

            console.log('Creating class with payload:', payload);

            router.post('/classes', payload, {
                onSuccess: () => {
                    toast.success('Class created!');
                    closeModal();
                    router.reload({ only: ['classes'] });
                },
                onError: (errors) => {
                    if (errors.name) {
                        form.value.errors.name = errors.name;
                    } else if (typeof errors === 'string') {
                        form.value.errors.name = errors;
                    } else {
                        form.value.errors.name = 'Creation failed.';
                    }
                    console.error('Creation errors:', errors);
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        }
    } catch (e) {
        console.error('Error in handleSubmit:', e);
        toast.error('An error occurred: ' + (e as Error).message);
        loading.value = false;
    }
}

function handleDelete(cls: { id: number; name: string }) {
    classToDelete.value = cls;
    showDeleteDialog.value = true;
}

async function confirmDelete() {
    if (!classToDelete.value) return;
    loading.value = true;

    router.delete(`/classes/${classToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Class deleted!');
            showDeleteDialog.value = false;
            classToDelete.value = null;
            router.reload({ only: ['classes'] });
        },
        onError: () => {
            toast.error('Failed to delete class.');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
    classToDelete.value = null;
}
</script>