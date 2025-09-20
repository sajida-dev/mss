<template>
    <AppLayout>

        <Head title="Academic Years" />
        <div class="max-w-6xl py-10 mx-auto w-full px-2 sm:px-4 md:px-0 mt-10">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Academic Years</h1>
                <Button variant="default" size="lg" @click="openCreateModal">Add Academic Year</Button>
            </div>

            <BaseDataTable :headers="headers" :items="academicYears.data" :loading="loading"
                :server-options="serverOptions" :server-items-length="academicYears.meta.total"
                @update:server-options="handleServerOptionsUpdate"
                class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700">
                <template #item-actions="row">
                    <button class="text-blue-500 mr-2" @click="openEditModal(row)" title="Edit Academic Year">
                        <Edit class="w-5 h-5" />
                    </button>
                    <button class="text-red-500" @click="handleDelete(row)" title="Delete Academic Year">
                        <Trash class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="modalOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isEdit ? 'Edit Academic Year' : 'Add Academic Year' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <TextInput id="name" v-model="form.name" label="Academic Year Name (e.g. 2025-2026)" required
                            :error="form.errors.name" class="col-span-2" />
                        <TextInput id="start_date" v-model="form.start_date" label="Start Date" type="date" required
                            :error="form.errors.start_date" />
                        <TextInput id="end_date" v-model="form.end_date" label="End Date" type="date" required
                            :error="form.errors.end_date" />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button type="submit" :disabled="loading">
                            {{ isEdit ? 'Update Academic Year' : 'Create Academic Year' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Academic Year?</DialogTitle>
                </DialogHeader>
                <div class="mb-4">Are you sure you want to delete this academic year? This action cannot be undone.
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { Edit, Trash } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import TextInput from '@/components/form/TextInput.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

interface AcademicYear {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    status: string;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    [key: string]: any;
}

interface PaginationResponse {
    data: AcademicYear[];
    meta: PaginationMeta;
}

// Define props
const props = defineProps<{ academicYears: PaginationResponse }>();

// Reactive states
const academicYears = ref<PaginationResponse>({ ...props.academicYears });
console.log('academicYears', academicYears.value);
const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingItem = ref<AcademicYear | null>(null);
const showDeleteDialog = ref(false);
const itemToDelete = ref<AcademicYear | null>(null);

// Server options for pagination/sorting/search
const serverOptions = ref({
    page: props.academicYears?.meta?.current_page ?? 1,
    rowsPerPage: props.academicYears?.meta?.per_page ?? 10,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});


// Table headers
const headers = [
    { text: '#', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Start Date', value: 'start_date' },
    { text: 'End Date', value: 'end_date' },
    { text: 'Status', value: 'status' },
    { text: 'Actions', value: 'actions', sortable: false },
];

// Form
const form = useForm({
    id: null as number | null,
    name: '',
    start_date: '',
    end_date: '',
    status: 'active',
});
function handleServerOptionsUpdate(opts: {
    page: number;
    rowsPerPage: number;
    sortBy?: string;
    sortType?: string;
    search?: string;
    filters?: Record<string, any>;
}) {
    Object.assign(serverOptions.value, opts);
}

// Modal handlers
function openCreateModal() {
    isEdit.value = false;
    editingItem.value = null;
    form.reset();
    modalOpen.value = true;
}

function openEditModal(item: AcademicYear) {
    isEdit.value = true;
    editingItem.value = item;

    form.id = item.id;
    form.name = item.name;
    form.start_date = item.start_date;
    form.end_date = item.end_date;
    form.status = item.status;

    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
}

// Submit
function handleSubmit() {
    loading.value = true;

    const successCallback = () => {
        toast.success(isEdit.value ? 'Academic Year updated!' : 'Academic Year created!');
        closeModal();
        fetchData(); // Refresh table
    };

    const errorCallback = () => {
        // Errors will be set in form.errors automatically
    };

    const finishCallback = () => {
        loading.value = false;
    };

    if (isEdit.value && form.id !== null) {
        form.put(`/admin/academic-years/${form.id}`, {
            preserveScroll: true,
            onSuccess: successCallback,
            onError: errorCallback,
            onFinish: finishCallback,
        });
    } else {
        form.post('/admin/academic-years', {
            preserveScroll: true,
            onSuccess: successCallback,
            onError: errorCallback,
            onFinish: finishCallback,
        });
    }
}

// Delete
function handleDelete(item: AcademicYear) {
    itemToDelete.value = item;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!itemToDelete.value) return;

    loading.value = true;

    router.delete(`/admin/academic-years/${itemToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Academic Year deleted!');
            showDeleteDialog.value = false;
            fetchData(); // Refresh table
        },
        onError: () => {
            toast.error('Failed to delete academic year.');
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
}

// Fetch server-side paginated data
function fetchData() {
    loading.value = true;
    router.get('/admin/academic-years', serverOptions.value, {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            academicYears.value = page.props.academicYears as PaginationResponse;
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
}

watch(serverOptions, fetchData, { deep: true });

</script>
