<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Student Admissions" />
        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Student Admissions</h1>

            <!-- Standalone Search Input -->
            <div class="mb-6">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input v-model="filtersForm.search" type="text"
                        placeholder="Search admissions by name, registration number..."
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
            <div class="flex lg:hidden justify-end mb-4">
                <button @click="openFilterSheet"
                    class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition"
                    title="Show filters for admissions records">
                    <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                    <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                </button>
            </div>
            <!-- Filter UI (hidden on mobile, shown in bottom sheet) -->
            <!-- <div class=" flex-wrap gap-4 mb-4 items-end hidden lg:flex"> -->
            <div class="grid-cols-2 sm:grid-cols-1 lg:grid-cols-5 gap-4 mb-6 items-end hidden lg:grid">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                    <select v-model="filtersForm.status"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="applicant">Applicant</option>
                        <option value="admitted">Admitted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Class</label>
                    <select v-model="filtersForm.class_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Year</label>
                    <select v-model="filtersForm.year"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <!-- School filter only for superadmins -->
                <div v-if="auth.user.roles?.some(r => r.name === 'superadmin')">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                    <select v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="s in userSchools" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <Button v-can="'create-admissions'" variant="default" size="lg" class="mr-2" @click="goToCreate">Add
                    Student</Button>
            </div>
            <BaseDataTable :headers="headers" :items="items" :loading="loading" :server-options="serverOptions"
                :server-items-length="total" :expandable="true" :expand-row-keys="expandedRow ? [expandedRow] : []"
                @update:server-options="(opts: Record<string, any>) => Object.assign(serverOptions, opts)"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #item-profile_photo="row">
                    <img :src="row.profile_photo_url" alt="Profile Photo"
                        class="w-10 h-10 rounded-full object-cover border" />
                </template>

                <template #item-actions="row">
                    <button v-can="'update-admissions'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="editStudent(row.id)" aria-label="Edit Student" title="Edit Student">
                        <Edit class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-admissions'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400 mr-1"
                        @click="askDeleteStudent(row.id)" aria-label="Delete Student" title="Delete Student">
                        <Trash class="w-5 h-5" />
                    </button>
                    <button v-can="'print-vouchers'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-green-600 focus:outline-none focus:ring-2 focus:ring-green-400"
                        @click="printVoucher(row.id)" aria-label="Print Voucher" title="Print Voucher">
                        <Printer class="w-5 h-5" />
                    </button>
                    <button v-if="row.status === 'applicant'" v-can="'reject-admissions'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 ml-1"
                        @click="askRejectStudent(row.id)" aria-label="Reject Student" title="Reject Student">
                        <Ban class="w-5 h-5" />
                    </button>
                </template>
                <template #item-expand="row">
                    <button @click="toggleRowExpansion(row)"
                        class="text-gray-500 hover:text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary rounded p-1 transition">
                        <svg v-if="expandedRow === row.id" class="w-4 h-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7">
                            </path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                </template>

                <template #expand="row">
                    <div v-if="expandedRow === row.id"
                        class="p-6 bg-white dark:bg-neutral-800 rounded-xl shadow border border-gray-200 dark:border-neutral-700 transition-all duration-300">

                        <!-- Top Header -->
                        <div class="flex flex-col md:flex-row items-center justify-between gap-6 border-b pb-4 mb-6">
                            <div class="flex items-center gap-4">
                                <img :src="row.profile_photo_url" alt="Profile Photo"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-purple-500" />
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">{{ row.name }}</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 flex items-center gap-1">
                                        <ClipboardList class="w-4 h-4" /> Registration #: {{ row.registration_number }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 flex items-center gap-1">
                                        <GraduationCap class="w-4 h-4" /> Class: {{ row.class }} - Shift: {{
                                            row.class_shift }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 flex items-center gap-1">
                                        <School class="w-4 h-4" /> School: {{ row.school_name }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4 md:mt-0">
                                <Button v-can="'update-admissions'" @click="editStudent(row.id)" variant="default"
                                    class="text-sm">
                                    <UserPlus class="w-4 h-4 mr-2" /> Edit
                                </Button>
                                <Button v-can="'mark-as-paid'" variant="default" v-if="row.status === 'applicant'"
                                    class="bg-green-700 hover:bg-green-800 text-white text-sm"
                                    @click="openVoucherModal(row.id)">
                                    <CreditCard class="w-4 h-4 mr-2" /> Upload Voucher
                                </Button>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <div class="mb-6">
                            <h3 class="flex items-center text-lg font-semibold text-blue-700 dark:text-blue-300 mb-3">
                                <BookOpen class="w-5 h-5" /> Personal Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <FileText class="w-4 h-4" /> Nationality: {{ row.nationality }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClipboardList class="w-4 h-4" /> B-Form #: {{ row.b_form_number }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CalendarCheck class="w-4 h-4" /> Admission Date: {{ row.admission_date }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CalendarCheck class="w-4 h-4" /> Date of Birth: {{ row.date_of_birth }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Gender: {{ row.gender }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Award class="w-4 h-4" /> Status: {{ row.status }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Building2 class="w-4 h-4" /> Previous School: {{ row.previous_school || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <BookOpenCheck class="w-4 h-4" /> Religion: {{ row.religion }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Users class="w-4 h-4" /> Inclusive: {{ row.inclusive }}
                                </div>
                                <div class="flex items-center gap-2" v-if="row.other_inclusive_type">
                                    <ClipboardList class="w-4 h-4" /> Other: {{ row.other_inclusive_type }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Orphan: {{ row.is_orphan ? 'Yes' : 'No' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Bricklin: {{ row.is_bricklin ? 'Yes' : 'No' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> QSC: {{ row.is_qsc ? 'Yes' : 'No' }}
                                </div>
                            </div>
                        </div>

                        <!-- Family Info -->
                        <div class="mb-6">
                            <h3 class="flex items-center text-lg font-semibold text-green-700 dark:text-green-300 mb-3">
                                <Users class="w-5 h-5" /> Family Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Father's Name: {{ row.father_name }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Guardian Name: {{ row.guardian_name || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <FileText class="w-4 h-4" /> Father CNIC: {{ row.father_cnic }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <FileText class="w-4 h-4" /> Mother CNIC: {{ row.mother_cnic || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Phone class="w-4 h-4" /> Phone: {{ row.phone_no || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Phone class="w-4 h-4" /> Mobile: {{ row.mobile_no }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Users class="w-4 h-4" /> Father Profession: {{ row.father_profession }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Users class="w-4 h-4" /> Mother Profession: {{ row.mother_profession }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <BookOpen class="w-4 h-4" /> Father Education: {{ row.father_education }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <BookOpen class="w-4 h-4" /> Mother Education: {{ row.mother_education }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <LayoutGrid class="w-4 h-4" /> Job Type: {{ row.job_type || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Users class="w-4 h-4" /> No. of Children: {{ row.no_of_children || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CreditCard class="w-4 h-4" /> Father Income: {{ row.father_income }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CreditCard class="w-4 h-4" /> Mother Income: {{ row.mother_income || 'N/A' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CreditCard class="w-4 h-4" /> Household Income: {{ row.household_income }}
                                </div>

                            </div>
                            <div class="flex items-center gap-2">
                                <Building2 class="w-4 h-4" /> Address: {{ row.permanent_address }}
                            </div>
                        </div>

                        <!-- Voucher Section -->
                        <div class="border-t pt-4 mt-4">
                            <h3 class="flex items-center gap-2 text-purple-700 dark:text-purple-300 font-semibold mb-2">
                                <Receipt class="w-5 h-5" /> Voucher Status
                            </h3>
                            <div v-if="row.status !== 'admitted'">
                                <div v-if="!row.fee || row.fee.status !== 'paid'">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Voucher not uploaded.</p>
                                </div>
                                <div v-else>
                                    <span class="text-green-800 dark:text-green-700 font-semibold">Admitted</span>
                                    <div class="mt-2">
                                        <label class="font-semibold">Paid Voucher Image:</label>
                                        <img :src="`/storage/${row.fee.paid_voucher_image}`" alt="Paid Voucher"
                                            class="w-40 h-auto border rounded mt-1" />
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <span class="text-green-800 dark:text-green-700 font-semibold">Admitted</span>
                                <div v-if="row.fee && row.fee.paid_voucher_image" class="mt-2">
                                    <label class="font-semibold">Paid Voucher Image:</label>
                                    <img :src="`/storage/${row.fee.paid_voucher_image}`" alt="Paid Voucher"
                                        class="w-40 h-auto border rounded mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
                        <UploadVoucherModal v-if="showVoucherModal && selectedStudentId === row.id" :id="row.id"
                            :submitUrl="'admissions.approve'" @close="closeVoucherModal"
                            @uploaded="onVoucherUploaded" />
                    </div>
                </template>

            </BaseDataTable>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete Student"
            message="Are you sure you want to delete this student? This action cannot be undone."
            :confirm-text="'Delete'" :cancel-text="'Cancel'" @confirm="deleteStudent">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteStudent">Delete</Button>
                </div>
            </template>
        </AlertDialog>

        <AlertDialog v-model="showRejectDialog" title="Reject Student"
            message="Are you sure you want to reject this student? This action cannot be undone."
            :confirm-text="'Reject'" :cancel-text="'Cancel'" @confirm="rejectStudent">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showRejectDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="rejectStudent">Delete</Button>
                </div>
            </template>
        </AlertDialog>
        <!-- Touch-friendly Bottom Sheet for Mobile Filters -->
        <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
            ref="filterSheet" class="dark:bg-neutral-900">
            <div class="sheet-content dark:bg-neutral-900">
                <h2 class="text-lg font-semibold mb-4">Admission Filters</h2>

                <!-- Search Input in Bottom Sheet -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input v-model="filtersForm.search" type="text"
                            placeholder="Search admissions by name, registration number..."
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
                        <label for="status-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                        <select id="status-mobile" v-model="filtersForm.status"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="applicant">Applicant</option>
                            <option value="admitted">Admitted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="class-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Class</label>
                        <select id="class-mobile" v-model="filtersForm.class_id"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="year-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Year</label>
                        <select id="year-mobile" v-model="filtersForm.year"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="school-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                        <select id="school-mobile" v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option v-for="s in userSchools" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </vue-bottom-sheet>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { usePage, router, Head, useForm } from '@inertiajs/vue3';
import { useAdmissionsStore } from '@/stores/admissions';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import AppLayout from '@/layouts/AppLayout.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';
import UploadVoucherModal from '@/components/UploadVoucherModal.vue';
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { Award, Ban, BookOpen, BookOpenCheck, Building2, CalendarCheck, ClipboardList, CreditCard, Edit, FileText, FilterIcon, GraduationCap, LayoutGrid, Phone, Printer, Receipt, School, Trash, UserPlus, Users } from 'lucide-vue-next';

const defaultProfileImage = '/storage/default-profile.png';

interface User {
    id: number;
    name: string;
    email: string;
    isSuperAdmin?: boolean;
    schools?: { id: number; name: string }[];
    roles?: { id: number; name: string }[];
    [key: string]: any;
}

interface StudentsPagination {
    data: any[];
    total: number;
    [key: string]: any;
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Student Admissions',
        href: '/admissions',
    },
];

const page = usePage();
const admissionsStore = useAdmissionsStore();
const students = ref<StudentsPagination>(page.props.students as StudentsPagination);
const auth = computed<{ user: User }>(() => page.props.auth || { user: {} as User });
const schoolStore = useSchoolStore();
const { classes, selectedSchool } = storeToRefs(schoolStore);
const years = Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - i);
const initialFilters: Record<string, any> = page.props.filters || {};


const filtersForm = useForm({
    status: initialFilters.status || '',
    class_id: initialFilters.class_id || '',
    year: initialFilters.year || '',
    search: initialFilters.search || '',
    school_id: selectedSchool.value?.id || '',
});

const userSchools = computed(() => auth.value.user?.schools || []);
const isSingleSchoolUser = computed(() => userSchools.value.length === 1 && !auth.value.user?.isSuperAdmin);

onMounted(() => {
    if (isSingleSchoolUser.value) {
        filtersForm.school_id = userSchools.value[0].id;
    } else if (selectedSchool.value?.id) {
        filtersForm.school_id = selectedSchool.value.id;
    }
});

const filterSheet = ref<InstanceType<typeof VueBottomSheet>>();
function openFilterSheet() { filterSheet.value?.open(); }
function closeFilterSheet() { filterSheet.value?.close(); }

// Debounced filter watchers
let debounceTimer: number;
type FilterField = 'status' | 'class_id' | 'year' | 'search' | 'school_id';
const watchedFields: FilterField[] = ['status', 'class_id', 'year', 'search', 'school_id'];
watchedFields.forEach((field: FilterField) => {
    watch(() => filtersForm[field], () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchData();
        }, 500);
    });
});

const loading = ref(false);
const total = ref<number>(students.value && typeof students.value.total === 'number' ? students.value.total : 0);
const serverOptions = ref({
    page: 1,
    rowsPerPage: 10,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

function fetchData() {
    loading.value = true;
    filtersForm.get(route('admissions.index'), {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            console.log('Fetch data success:', page.props.students);
            students.value = page.props.students as StudentsPagination;
            total.value = students.value && typeof students.value.total === 'number' ? students.value.total : 0;
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        }
    });
}

watch(serverOptions, fetchData, { deep: true });
onMounted(fetchData);

const headers = [
    { text: 'Photo', value: 'profile_photo', sortable: false },
    { text: 'Name', value: 'name' },
    { text: 'Reg #', value: 'registration_number' },
    { text: 'Class', value: 'class' },
    { text: 'Status', value: 'status' },
    { text: 'Admission Date', value: 'admission_date' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

const items = computed(() => {
    const data = Array.isArray(students.value?.data) ? students.value.data : [];
    return data.map((student: any) => {
        console.log('Processing student:', student.id, 'fee:', student.fee);
        return {
            ...student,
            class: student.class ? student.class.name : '',
            profile_photo_url: student.profile_photo_url || defaultProfileImage,
            fee: student.fee || null, // Expecting fee data from backend
        };
    });
});

const expandedRow = ref<number | null>(null);
function toggleRowExpansion(row: any) {
    expandedRow.value = expandedRow.value === row.id ? null : row.id;
}

const showDeleteDialog = ref(false);
const showRejectDialog = ref(false);
const studentToDelete = ref<number | null>(null);
const studentToReject = ref<number | null>(null);

const goToCreate = () => {
    router.get(route('admissions.create'));
};

const editStudent = (id: number) => {
    router.get(route('admissions.edit', id));
};

const askDeleteStudent = (id: number) => {
    studentToDelete.value = id;
    showDeleteDialog.value = true;
};
const askRejectStudent = (id: number) => {
    studentToReject.value = id;
    showRejectDialog.value = true;
};
const deleteStudent = () => {
    if (!studentToDelete.value) return;
    router.delete(route('admissions.destroy', studentToDelete.value), {
        onSuccess: () => {
            toast.success('Student deleted successfully.');
            if (students.value && Array.isArray(students.value.data)) {
                students.value.data = students.value.data.filter((s: any) => s.id !== studentToDelete.value);
                total.value = Math.max(0, total.value - 1);
            }
            studentToDelete.value = null;
        },
        onError: () => toast.error('Failed to delete student!')
    });
    showDeleteDialog.value = false;
};

function printVoucher(studentId: number) {
    router.get(route('fees.voucher', { student: studentId }));
}

const voucherFiles = ref<Record<number, File | null>>({});
function handleVoucherFileChange(e: Event, studentId: number) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        voucherFiles.value = { ...voucherFiles.value, [studentId]: file };
    }
}
function approveStudent(studentId: number) {
    const file = voucherFiles.value[studentId];
    if (!file) {
        toast.error('Please upload the paid voucher image.');
        return;
    }
    const formData = new FormData();
    formData.append('paid_voucher_image', file);
    router.post(route('admissions.approve', studentId), formData, {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Student approved successfully.');
            fetchData();
            voucherFiles.value[studentId] = null;
        },
        onError: () => {
            toast.error('Failed to approve student.');
        }
    });
}

function rejectStudent() {
    if (!studentToReject.value) return;

    router.post(route('admissions.reject', studentToReject.value), {}, {
        onSuccess: () => {
            toast.success('Student rejected successfully.');
            // Update local state to reflect rejection
            if (students.value && Array.isArray(students.value.data)) {
                const idx = students.value.data.findIndex((s: any) => s.id === studentToReject.value);
                if (idx !== -1) {
                    students.value.data[idx].status = 'rejected';
                }
            }
        },
        onError: () => {
            toast.error('Failed to reject student.');
        }
    });
}

// Show toast from Inertia props if present
const toastProp = page.props.toast as { message?: string; type?: string };
if (toastProp && toastProp.message && toastProp.type) {
    toast(toastProp.message, { type: toastProp.type as 'success' | 'error' });
}

watch(
    () => page.props.students,
    (newVal) => {
        students.value = newVal as StudentsPagination;
        total.value = (newVal as StudentsPagination)?.total ?? 0;
    }
);

watch(
    () => page.props.toast,
    (newVal) => {
        const toastVal = newVal as { message?: string; type?: string };
        if (toastVal && toastVal.message && toastVal.type) {
            toast(toastVal.message, { type: toastVal.type as 'success' | 'error' });
        }
    }
);

const showVoucherModal = ref(false);
const selectedStudentId = ref<number | null>(null);

function openVoucherModal(studentId: number) {
    selectedStudentId.value = studentId;
    showVoucherModal.value = true;
}
function closeVoucherModal() {
    showVoucherModal.value = false;
    selectedStudentId.value = null;
}
function onVoucherUploaded() {
    console.log('Voucher uploaded callback triggered');
    fetchData();
    closeVoucherModal();
}

function onSearchInput() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchData();
    }, 500);
}

function clearSearch() {
    filtersForm.search = '';
    fetchData();
}
</script>

<style scoped>
/* Dynamic theme color support: set --color-primary and --color-primary-dark in your global CSS or via a theme switcher. */
.bg-primary {
    background: var(--color-primary, #7c3aed);
}

.bg-primary-dark {
    background: var(--color-primary-dark, #6d28d9);
}

.text-primary {
    color: var(--color-primary, #7c3aed);
}

.text-primary-dark {
    color: var(--color-primary-dark, #6d28d9);
}

.btn {
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    background: var(--color-primary, #7c3aed);
    color: #fff;
    transition: background 0.2s;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: var(--color-primary-dark, #6d28d9);
}

.btn-danger {
    background: #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-primary {
    background: var(--color-primary, #7c3aed);
}

.btn-primary:hover {
    background: var(--color-primary-dark, #6d28d9);
}

.sheet-content {
    padding: 20px;
}
</style>