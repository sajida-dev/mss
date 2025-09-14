<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Schools" />
        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-neutral-900 dark:text-neutral-100">Schools</h1>
            <BaseDataTable :headers="headers" :items="items" :loading="loading" :server-options="serverOptions"
                :server-items-length="total"
                @update:server-options="(opts: Record<string, any>) => Object.assign(serverOptions, opts)"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #toolbar>

                    <Button v-can="'create-schools'" variant="default" size="lg" class="mr-2" @click="addSchool">Add
                        School</Button>
                    <!-- <Button variant="secondary" size="lg" class="mr-2" @click="importSchools">Import</Button>
                    <Button variant="outline" size="lg" @click="exportSchools">Export</Button> -->
                </template>
                <template #item-name="row">
                    <div>
                        <span>{{ row.name }}</span>
                    </div>
                </template>
                <template #item-id="row">
                    <div class="flex items-center justify-center h-full">
                        <Avatar class="w-8 h-8">
                            <AvatarImage
                                :src="row.logo ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s'"
                                :alt="row.name" />
                        </Avatar>
                    </div>
                </template>
                <template #item-actions="row">
                    <button v-can="'read-schools'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-neutral-500 focus:outline-none focus:ring-2 focus:ring-neutral-400 mr-1"
                        @click="viewSchool(row.id)" aria-label="View School" title="View School">
                        <Eye class="w-5 h-5" />
                    </button>
                    <button v-can="'edit-schools'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="editSchool(row.id)" aria-label="Edit School" title="Edit School">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-schools'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="askDeleteSchool(row.id)" aria-label="Delete School" title="Delete School">
                        <Icon name="trash" class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete School"
            message="Are you sure you want to delete this school? This action cannot be undone."
            :confirm-text="'Delete'" :cancel-text="'Cancel'" @confirm="deleteSchool">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteSchool">Delete</Button>
                </div>
            </template>
        </AlertDialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { usePage, router, Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import { Eye } from 'lucide-vue-next';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import Icon from '@/components/Icon.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarImage from '@/components/ui/avatar/AvatarImage.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';

interface SchoolsPagination {
    data: any[];
    total: number;
    [key: string]: any;
}

const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
];

const { props } = usePage();
const schools = ref<SchoolsPagination | null>(props.schools as SchoolsPagination);
const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Address', value: 'address' },
    { text: 'Contact', value: 'contact' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

const items = computed(() => {
    const data = Array.isArray(schools.value?.data) ? schools.value.data : [];
    return data.map((school: any) => ({
        id: school.id != null ? school.id.toString() : '',
        name: school.name != null ? school.name.toString() : '',
        address: school.address != null ? school.address.toString() : '',
        contact: school.contact != null ? school.contact.toString() : '',
    }));
});

const total = ref<number>(props.schools ? (props.schools as SchoolsPagination).total : 0);
const loading = ref(false);
const serverOptions = ref<{ page: number; rowsPerPage: number; sortBy: string; sortType: string; search: string; filters: Record<string, any> }>({
    page: 1,
    rowsPerPage: 10,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

const showDeleteDialog = ref(false);
const schoolToDelete = ref<number | null>(null);
const expandedRow = ref<string | number | null>(null);
function toggleRowExpansion(row: any) {
    expandedRow.value = expandedRow.value === row.id ? null : row.id;
}

const schoolStore = useSchoolStore();
const { schools: globalSchools } = storeToRefs(schoolStore);

const fetchData = () => {
    loading.value = true;
    router.get(route('schools.index'), serverOptions.value, {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            schools.value = page.props.schools as SchoolsPagination;
            total.value = (page.props.schools as SchoolsPagination).total;
            loading.value = false;
        },
    });
};

watch(serverOptions, fetchData, { deep: true });
onMounted(fetchData);

const addSchool = () => router.get(route('schools.create'));
const importSchools = () => {/* open import dialog or trigger file input */ };
const exportSchools = () => {/* trigger export, e.g., window.open or router visit */ };

const editSchool = (id: number) => {
    router.get(route('schools.edit', id));
};

function viewSchool(id: number) {
    router.get(route('schools.show', id));
}

const askDeleteSchool = (id: number) => {
    schoolToDelete.value = id;
    showDeleteDialog.value = true;
};

const deleteSchool = () => {
    if (!schoolToDelete.value) return;
    useForm<{ id: number | string }>({ id: schoolToDelete.value }).delete(route('schools.destroy', schoolToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('School deleted successfully.');
            if (schools.value) {
                schools.value.data = schools.value.data.filter((school: any) => school.id != schoolToDelete.value);
                total.value = Math.max(0, total.value - 1);
            }
            // Remove from global Pinia schools array
            const idx = globalSchools.value.findIndex((s: any) => s.id == schoolToDelete.value);
            if (idx !== -1) {
                globalSchools.value.splice(idx, 1);
            }
            schoolToDelete.value = null;
        },
        onError: () => toast.error('Failed to delete school!')
    });
    showDeleteDialog.value = false;
};


onMounted(() => {
    total.value = props.schools ? (props.schools as SchoolsPagination).total : 0;
    if (props.success) toast.success(props.success);
    if (props.error) toast.error(props.error);
});

watch(
    () => props.schools,
    (newVal) => {
        schools.value = newVal as SchoolsPagination;
        total.value = newVal ? (newVal as SchoolsPagination).total : 0;
    }
);
</script>