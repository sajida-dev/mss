<template>
    <ManageLayout :key="route().current()">

        <Head title="Certificates" />

        <div class="max-w-6xl mx-auto w-full">
            <!-- Header + Create -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Issued Certificates</h2>

            </div>
            <!-- Filters -->
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-7 justify-center items-center gap-4">
                <SelectInput v-model="filters.academic_year_id" :options="academicYearOptions" label="Academic Year" />
                <SelectInput v-model="filters.type" :options="typeOptions" label="Type" />
                <SelectInput class="col-span-2" v-model="filters.achievement_id" :options="achievementOptions"
                    label="Achievement" />
                <SelectInput class="col-span-2" v-model="filters.student_id" :options="studentOptions"
                    label="Student" />

                <Button @click="openCreateModal">Add Certificate</Button>
            </div>



            <!-- Data Table -->
            <BaseDataTable :headers="headers" :items="certificates" :loading="loading">
                <template #item-actions="row">
                    <button class="text-yellow-500 mr-2" size="sm" @click="printCertificate(row.id, 'achievement')"
                        title="Print Acievement Certificate">
                        <Printer class="w-5 h-5" />
                    </button>
                    <button class="text-green-500 mr-2" size="sm" @click="printCertificate(row.id, 'leaving')"
                        title="Print Leaving Certificate">
                        <GraduationCap class="w-5 h-5" />
                    </button>
                    <button class="text-blue-500 mr-2" size="sm" @click="edit(row)" title="Edit Certificate">
                        <Edit class="w-5 h-5" />
                    </button>
                    <button class="text-red-500" size="sm" @click="handleDelete(row)" title="Delete Certificate">
                        <Trash class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>

            <!-- Create/Edit Modal -->
            <Dialog v-model:open="modalOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ isEdit ? 'Edit Certificate' : 'Add Certificate' }}</DialogTitle>
                    </DialogHeader>

                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <SelectInput v-model="form.achievement_id" :options="achievementOptions" label="Achievement"
                            :error="form.errors.achievement_id" class="col-span-2" />
                        <TextInput v-model="form.registration_number" label="Student Registration No"
                            :error="form.errors.registration_number" />

                        <SelectInput v-model="form.academic_year_id" :options="academicYearOptions"
                            label="Academic Year" :error="form.errors.academic_year_id" />
                        <SelectInput v-model="form.type" :options="typeOptions" label="Type"
                            :error="form.errors.type" />
                        <TextInput v-model="form.issued_at" label="Issued Date" type="date"
                            :error="form.errors.issued_at" />
                        <textarea name="details" id="details" v-model="form.details" rows="3"
                            placeholder="Optional details"
                            class="w-full col-span-2 px-3 py-2 rounded-md border bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                            :error="form.errors.details" />

                        <DialogFooter class="mt-4">
                            <Button variant="outline" @click="modalOpen = false">Cancel</Button>
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
                <DialogTitle>Delete Certificate?</DialogTitle>
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
import { useForm, usePage, router, Head } from '@inertiajs/vue3';
import ManageLayout from './ManageLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import { Button } from '@/components/ui/button';
import { toast } from 'vue3-toastify';
import { Edit, GraduationCap, Printer, Trash } from 'lucide-vue-next';

interface Certificate {
    id: number;
    achievement_id: number;
    school_id: undefined;
    student: Student;
    academic_year_id: number;
    type: string;
    issued_at: string;
    details: string | null;
}

interface Achievement {
    id: number;
    title: string;
}

interface Student {
    id: number;
    name: string;
    registration_number: string;
}

interface AcademicYear {
    id: number;
    name: string;
}

interface Filters {
    academic_year_id?: number | null,
    achievement_id?: number | null,
    type?: string | null,
    student_id?: number | null,
}

const props = defineProps<{
    certificates: Certificate[],
    academicYears: AcademicYear[],
    achievements: Achievement[],
    students: Student[],
    filters: Filters,
}>();

console.log('props : ', props)
const loading = ref(false);
const itemToDelete = ref<Certificate | null>(null);
const showDeleteDialog = ref(false);

function handleDelete(row: Certificate) {
    itemToDelete.value = row;
    showDeleteDialog.value = true;
}

function cancelDelete() {
    showDeleteDialog.value = false;
}
// Filters
const filters = ref({
    academic_year_id: props.filters.academic_year_id ?? undefined,
    achievement_id: props.filters.achievement_id ?? undefined,
    type: props.filters.type ?? undefined,
    student_id: props.filters.student_id ?? undefined,
});

watch(filters, () => {
    router.get(route('certificates.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
})

const academicYearOptions = (props.academicYears ?? []).map(y => ({ value: y.id, label: y.name }));
const achievementOptions = (props.achievements ?? []).map(a => ({ value: a.id, label: a.title }));
const studentOptions = props.students.map(s => ({ value: s.id, label: s.name }));
const typeOptions = [
    { value: 'academic', label: 'Academic' },
    { value: 'sports', label: 'Sports' },
    { value: 'other', label: 'Other' },
];

// Modal + Form
const modalOpen = ref(false);
const isEdit = ref(false);
const editingId = ref<number | null>(null);
const form = useForm<{
    registration_number: string,
    achievement_id: number | string,
    academic_year_id: number | string,
    type: string,
    issued_at: string,
    details: string,
}>({
    registration_number: '',
    achievement_id: '',
    academic_year_id: '',
    type: 'academic',
    issued_at: '',
    details: '',
});

function openCreateModal() {
    form.clearErrors();
    form.reset();
    isEdit.value = false;
    editingId.value = null;
    modalOpen.value = true;
}

function edit(cert: Certificate) {
    isEdit.value = true;
    editingId.value = cert.id;
    form.registration_number = cert.student.registration_number;
    form.achievement_id = cert.achievement_id;
    form.academic_year_id = cert.academic_year_id;
    form.type = cert.type;
    form.issued_at = cert.issued_at;
    form.details = cert.details || '';
    modalOpen.value = true;
}

function submit() {
    if (isEdit.value && editingId.value) {
        form.put(route('certificates.update', editingId.value), {
            onSuccess: () => {
                modalOpen.value = false
                toast.success('Certificate updated!')
                router.reload({ only: ['certificates'] });
            },
            onError: (e) => {
                toast.error('Failed to update certificate.' + e.message)
            },
        });
    } else {
        form.post(route('certificates.store'), {
            onSuccess: () => {
                modalOpen.value = false
                toast.success('Certificate created!')
                router.reload({ only: ['certificates'] });
            },
            onError: (e) => {
                toast.error('Failed to create certificate.' + e.message)
            },
        });
    }
}


function confirmDelete() {
    if (!itemToDelete.value) return;
    loading.value = true;
    router.delete(`/certificates/${itemToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Certificate deleted');
            showDeleteDialog.value = false;
            router.reload({ only: ['certificates'] });
        },
        onError: (e) => {
            toast.error('Failed to delete certificate.' + e.message)
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

function printCertificate(certificateId: number, type: 'achievement' | 'leaving') {
    const url = route('certificates.print', { certificate: certificateId, type: type });
    fetch(url)
        .then(response => {
            if (!response.ok) {
                console.log('response', response)
                throw new Error("Failed to load voucher.");
            }
            return response.text();
        })
        .then(htmlContent => {
            const printFrame = document.createElement("iframe");
            printFrame.style.position = "fixed";
            printFrame.style.right = "0";
            printFrame.style.bottom = "0";
            printFrame.style.width = "0";
            printFrame.style.height = "0";
            printFrame.style.border = "0";
            printFrame.style.visibility = "hidden";

            document.body.appendChild(printFrame);

            const doc = printFrame.contentWindow?.document;
            if (doc) {
                doc.open();
                doc.write(htmlContent);
                doc.close();

                printFrame.onload = () => {
                    printFrame.contentWindow?.focus();
                    printFrame.contentWindow?.print();

                    // Clean up after printing
                    setTimeout(() => {
                        document.body.removeChild(printFrame);
                    }, 1000);
                };
            }
        })
        .catch(error => {
            console.error("Printing failed:", error);
            alert("Failed to print voucher. Please try again.");
        });
}


// Table headers
const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Student', value: 'student_name' },
    { text: 'Achievement', value: 'achievement.title' },
    { text: 'Type', value: 'type' },
    { text: 'Issued At', value: 'issued_at' },
    { text: 'Academic Year', value: 'academic_year_name' },
    { text: 'Actions', value: 'actions', slotName: 'item-actions', sortable: false },
];
</script>
