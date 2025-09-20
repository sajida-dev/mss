<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Papers & Questions" />
        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Papers & Questions</h1>

            <!-- Standalone Search Input -->
            <div class="mb-6">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input v-model="filtersForm.search" type="text" placeholder="Search papers by title..."
                        class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 text-sm"
                        @input="onSearchInput" />
                    <button v-if="filtersForm.search" @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Filter Icon with Tooltip and Label -->
            <div class="flex lg:hidden justify-between items-center mb-4 gap-3">
                <Button v-can="'create-papers'" variant="default" class="h-10" @click="goToCreate">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Paper
                </Button>
                <button @click="openFilterSheet"
                    class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition"
                    title="Show filters for paper records">
                    <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                    <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                </button>
            </div>
            <!-- Filter UI (hidden on mobile, shown in bottom sheet) -->
            <div class="grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6 items-end hidden lg:grid">
                <div class="flex flex-col">
                    <label for="class_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Class</label>
                    <select id="class_id" v-model="filtersForm.class_id" @change="fetchSections(filtersForm.class_id)"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="section_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Section</label>
                    <select id="section_id" v-model="filtersForm.section_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="teacher_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Teacher</label>
                    <select id="teacher_id" v-model="filtersForm.teacher_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="published"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                    <select id="published" v-model="filtersForm.published"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="1">Published</option>
                        <option value="0">Draft</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <Button v-can="'create-papers'" variant="default" class="h-10" @click="goToCreate">Add
                        Paper</Button>
                </div>
            </div>

            <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
                ref="myBottomSheet" class="dark:bg-neutral-900">
                <div class="sheet-content dark:bg-neutral-900">
                    <h2 class="text-lg font-semibold mb-4">Paper Filters</h2>

                    <!-- Search Input in Bottom Sheet -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input v-model="filtersForm.search" type="text" placeholder="Search papers by title..."
                                class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 text-sm"
                                @input="onSearchInput" />
                            <button v-if="filtersForm.search" @click="clearSearch"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col">
                            <label for="class_id-mobile"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Class</label>
                            <select id="class_id-mobile" v-model="filtersForm.class_id"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                                <option value="">All</option>
                                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="section_id-mobile"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Section</label>
                            <select id="section_id-mobile" v-model="filtersForm.section_id"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                                <option value="">All</option>
                                <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="teacher_id-mobile"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Teacher</label>
                            <select id="teacher_id-mobile" v-model="filtersForm.teacher_id"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                                <option value="">All</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="published-mobile"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                            <select id="published-mobile" v-model="filtersForm.published"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                                <option value="">All</option>
                                <option value="1">Published</option>
                                <option value="0">Draft</option>
                            </select>
                        </div>
                    </div>
                </div>
            </vue-bottom-sheet>

            <!-- Papers Listing Table -->
            <BaseDataTable :headers="headers" :items="items" :loading="loading" :server-options="serverOptions"
                :server-items-length="serverItemsLength"
                @update:server-options="(opts: Record<string, any>) => Object.assign(serverOptions, opts)"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all min-w-full"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #item-title="row">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ row.title }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ row.subject?.name || 'No Subject' }}
                    </div>
                </template>
                <template #item-class_section="row">
                    <div class="text-gray-900 dark:text-gray-100">
                        <div class="font-medium">{{ row.class?.name || '-' }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ row.section?.name || 'No Section' }}
                        </div>
                    </div>
                </template>
                <template #item-subject="row">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.subject?.name || '-' }}</span>
                </template>
                <template #item-teacher="row">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.teacher?.user?.name || '-' }}</span>
                </template>
                <template #item-duration="row">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.time_duration || 0 }} min</span>
                </template>
                <template #item-total_marks="row">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.total_marks || 0 }} marks</span>
                </template>
                <template #item-questions_count="row">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.questions_count || 0 }} questions</span>
                </template>
                <template #item-published="row">
                    <span :class="{
                        'inline-block rounded-full px-2 py-0.5 text-xs font-semibold': true,
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': row.published,
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': !row.published,
                    }">
                        {{ row.published ? 'Published' : 'Draft' }}
                    </span>
                </template>
                <template #item-actions="row">
                    <button v-can="'read-papers'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="viewPaper(row.id)" aria-label="View Paper" title="View Paper">
                        <Eye class="w-5 h-5" />
                    </button>
                    <!-- <button v-can="'print-papers'"
                    class="inline-flex items-center justify-center rounded-full p-2 text-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-400 mr-1"
                    @click="printPaper(row.id)" aria-label="Print Paper" title="Print Paper">
                    <Printer class="w-5 h-5" />
                </button> -->
                    <button v-can="'publish-papers'" :class="[
                        'inline-flex items-center justify-center rounded-full p-2 mr-1 focus:outline-none focus:ring-2',
                        row.published
                            ? 'text-orange-500 focus:ring-orange-400'
                            : 'text-green-500 focus:ring-green-400'
                    ]" @click="togglePublishStatus(row.id, row.published)"
                        :aria-label="row.published ? 'Unpublish Paper' : 'Publish Paper'"
                        :title="row.published ? 'Unpublish Paper' : 'Publish Paper'">
                        <EyeOff v-if="row.published" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                    </button>
                    <button v-can="'update-papers'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-green-500 focus:outline-none focus:ring-2 focus:ring-green-400 mr-1"
                        @click="editPaper(row.id)" aria-label="Edit Paper" title="Edit Paper">
                        <Edit class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-papers'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="askDeletePaper(row.id)" aria-label="Delete Paper" title="Delete Paper">
                        <Trash class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>

            <!-- Delete Confirmation Dialog -->
            <AlertDialog v-model="showDeleteDialog" title="Delete Paper"
                message="Are you sure you want to delete this paper? This action cannot be undone."
                :confirm-text="'Delete'" :cancel-text="'Cancel'" @confirm="deletePaper">
                <template #footer>
                    <div class="flex gap-2 justify-end">
                        <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                        <Button variant="destructive" @click="deletePaper">Delete</Button>
                    </div>
                </template>
            </AlertDialog>

            <!-- Custom Confirmation Dialog -->
            <Dialog v-model:open="showConfirmDialog">
                <DialogContent class="sm:max-w-md max-w-[95vw] mx-4">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2">
                            <Info class="w-5 h-5 text-blue-600" />
                            Confirm Action
                        </DialogTitle>
                    </DialogHeader>
                    <div class="mb-6">
                        <div
                            class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <Info class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                                    {{ confirmMessage }}
                                </p>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                    <DialogFooter class="flex flex-col sm:flex-row gap-3">
                        <Button variant="outline" @click="cancelAction" class="w-full sm:w-auto px-6 py-3 text-base">
                            <X class="w-4 h-4 mr-2" />
                            Cancel
                        </Button>
                        <Button variant="default" @click="confirmActionHandler"
                            class="w-full sm:w-auto px-6 py-3 text-base">
                            <CheckCircle class="w-4 h-4 mr-2" />
                            Confirm
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>


    </AppLayout>
</template>

<script setup lang="ts">
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { ref, watch, computed, Prop } from "vue";
import { useForm, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { BreadcrumbItem } from '@/types';
import AppLayout from "@/layouts/AppLayout.vue";
import { FilterIcon, Plus, Info, CheckCircle, X, Eye, EyeOff, Edit, Printer, Trash } from "lucide-vue-next";
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import Button from '@/components/ui/button/Button.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import axios from 'axios';

// Define props interface for the data coming from backend
interface Props {
    papers: {
        data: Array<{
            id: number;
            title: string;
            published: boolean;
            class?: {
                name: string;
            };
            section?: {
                name: string;
            };
            subject?: {
                name: string;
            };
            teacher?: {
                user: {
                    name: string;
                }
            };
            questions_count?: number;
            total_marks?: number;
            time_duration?: number;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    classes: Array<{ id: number; name: string }>;
    sections: Array<{ id: number; name: string }>;
    teachers: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        class_id?: string;
        section_id?: string;
        teacher_id?: string;
        published?: string;
    };
}

// Define props
const props = defineProps<Props>();
console.log('props', props)
const sections = ref<Props['sections']>([]);
const sectionCache = new Map();
const selectedClass = ref<string | number>('');
const selectedSection = ref<string | number>('');
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Papers & Questions',
        href: route('papersquestions.index'),
    },
];



async function fetchSections(classId: string | number) {
    if (sectionCache.has(classId)) {
        sections.value = sectionCache.get(classId);
        return;
    }

    try {
        const res = await axios.get(`/api/classes/${classId}/sections`);
        sectionCache.set(classId, res.data);
        sections.value = res.data;
    } catch (error) {
        console.error('Failed to load sections:', error);
        sections.value = [];
    }
}


watch(selectedClass, (newClassId) => {
    selectedSection.value = '';
    fetchSections(newClassId);
}, { immediate: true });


const myBottomSheet = ref<InstanceType<typeof VueBottomSheet>>()

const openFilterSheet = () => {
    myBottomSheet?.value?.open();
}

const closeFilterSheet = () => {
    myBottomSheet?.value?.close();
}

// Use Inertia form for filters
const filtersForm = useForm({
    search: props.filters?.search || '',
    class_id: props.filters?.class_id || '',
    section_id: props.filters?.section_id || '',
    teacher_id: props.filters?.teacher_id || '',
    published: props.filters?.published || '',
});

const loading = ref(false);
const serverOptions = ref({
    page: props.papers.current_page,
    rowsPerPage: props.papers.per_page,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

const serverItemsLength = computed(() => props.papers.total);

const headers = [
    { text: '#', value: 'id' },
    { text: 'Title', value: 'title' },
    { text: 'Class/Section', value: 'class_section' },
    { text: 'Subject', value: 'subject' },
    { text: 'Teacher', value: 'teacher' },
    { text: 'Duration', value: 'duration' },
    { text: 'Total Marks', value: 'total_marks' },
    { text: 'Questions', value: 'questions_count' },
    { text: 'Year', value: 'year' },
    { text: 'Status', value: 'published' },
    { text: 'Actions', value: 'actions', sortable: false },
];

const items = computed(() => {
    return props.papers.data.map((paper) => ({
        id: paper.id,
        title: paper.title,
        class: paper.class,
        section: paper.section,
        subject: paper.subject,
        teacher: paper.teacher,
        published: paper.published,
        questions_count: paper.questions_count,
        total_marks: paper.total_marks,
        time_duration: paper.time_duration,
    }));
});

// Delete dialog state
const showDeleteDialog = ref(false);
const paperToDelete = ref<number | null>(null);

// Custom confirmation modal state
const showConfirmDialog = ref(false);
const confirmMessage = ref('');
const confirmAction = ref<(() => void) | null>(null);

// Function to apply filters
function applyFilters() {
    filtersForm.get(route('papersquestions.index'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

// Watch individual form fields with debounce
let debounceTimer: number;
type FilterField = 'search' | 'class_id' | 'section_id' | 'teacher_id' | 'published';
const watchedFields: FilterField[] = ['search', 'class_id', 'section_id', 'teacher_id', 'published'];

watchedFields.forEach((field: FilterField) => {
    watch(() => filtersForm[field], () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            applyFilters();
        }, 500); // 500ms debounce
    });
});

function viewPaper(id: number) {
    router.visit(route('papersquestions.show', id));
}

function editPaper(id: number) {
    router.visit(route('papersquestions.edit', id));
}

function goToCreate() {
    router.visit(route('papersquestions.create'));
}

function onSearchInput(event: Event) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        applyFilters();
    }, 500); // 500ms debounce
}

function clearSearch() {
    filtersForm.search = '';
    applyFilters();
}

function askDeletePaper(id: number) {
    paperToDelete.value = id;
    showDeleteDialog.value = true;
}

function deletePaper() {
    if (!paperToDelete.value) return;

    router.delete(route('papersquestions.destroy', paperToDelete.value), {
        onSuccess: () => {
            toast.success('Paper deleted successfully');
            showDeleteDialog.value = false;
            paperToDelete.value = null;
        },
        onError: () => {
            toast.error('Failed to delete paper');
            showDeleteDialog.value = false;
            paperToDelete.value = null;
        },
    });
}

function printPaper(id: number) {
    router.visit(route('papersquestions.show', id));
}

function togglePublishStatus(id: number, currentStatus: boolean) {
    const action = currentStatus ? 'unpublish' : 'publish';
    const message = `Are you sure you want to ${action} this paper?`;

    showConfirmation(message, () => {
        router.patch(route('papersquestions.toggle-publish', id), {}, {
            onSuccess: () => {
                toast.success(`Paper ${action}ed successfully`);
                // Refresh the page to update the data
                router.visit(route('papersquestions.index'), {
                    only: ['papers'],
                    preserveState: true,
                    preserveScroll: true,
                    replace: true
                });
            },
            onError: () => {
                toast.error(`Failed to ${action} paper`);
            },
        });
    });
}

// Custom confirmation functions
function showConfirmation(message: string, action: () => void) {
    confirmMessage.value = message;
    confirmAction.value = action;
    showConfirmDialog.value = true;
}

function confirmActionHandler() {
    if (confirmAction.value) {
        confirmAction.value();
    }
    showConfirmDialog.value = false;
    confirmMessage.value = '';
    confirmAction.value = null;
}

function cancelAction() {
    showConfirmDialog.value = false;
    confirmMessage.value = '';
    confirmAction.value = null;
}
</script>

<style scoped>
.sheet-content {
    padding: 20px;
}
</style>