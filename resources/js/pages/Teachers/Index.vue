<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Teachers" />
        <div class="max-w-full mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Teachers</h1>

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
                        placeholder="Search teachers by name, email, CNIC, contact..."
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
            <div class="flex lg:hidden justify-between items-center mb-4">
                <Button v-can="'create-teachers'" variant="default" class="h-10" @click="goToCreate">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Teacher
                </Button>
                <button @click="openFilterSheet"
                    class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition"
                    title="Show filters for teacher records">
                    <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                    <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                </button>
            </div>
            <!-- Filter UI (hidden on mobile, shown in bottom sheet) -->
            <div class="grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6 items-end hidden lg:grid">
                <div class="flex flex-col">
                    <label for="role"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Role</label>
                    <select id="role" v-model="filtersForm.role"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="teacher">Teacher</option>
                        <option value="principal">Principal</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="gender"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Gender</label>
                    <select id="gender" v-model="filtersForm.gender"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="status"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                    <select id="status" v-model="filtersForm.status"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="school"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                    <select id="school" v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All Schools</option>
                        <option v-for="school in schools" :key="school.id" :value="school.id">
                            {{ school.name }}
                        </option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <Button v-can="'create-teachers'" variant="default" class="h-10" @click="goToCreate">Add
                        Teacher</Button>
                </div>
            </div>
            <BaseDataTable :headers="headers" :items="teachers.data" :loading="loading" :server-options="serverOptions"
                :server-items-length="serverItemsLength" :expandable="true"
                :expand-row-keys="expandedRow ? [expandedRow] : []"
                @update:server-options="(opts: Record<string, any>) => Object.assign(serverOptions, opts)"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all min-w-full"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #item-name="row">
                    <div class="flex items-center gap-2 min-w-0">
                        <Avatar class="flex-shrink-0">
                            <AvatarImage v-if="row.profile_photo_url" :src="row.profile_photo_url" :alt="row.name" />
                            <AvatarFallback>{{ getInitials(row.name) }}</AvatarFallback>
                        </Avatar>
                        <span class="truncate">{{ row.name }}</span>
                    </div>
                </template>
                <template #item-email="row">
                    <span class="truncate block max-w-[200px] sm:max-w-none">{{ row.email }}</span>
                </template>
                <template #item-role="row">
                    <div class="flex flex-wrap gap-1">
                        <span v-for="role in row.roles" :key="role.id"
                            class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 rounded-full px-2 py-0.5 text-xs font-semibold">
                            {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                        </span>
                    </div>
                </template>
                <template #item-gender="row">
                    {{ row.teacher?.gender || '-' }}
                </template>
                <template #item-cnic="row">
                    <span class="truncate block max-w-[120px] sm:max-w-none">{{ row.teacher?.cnic || '-' }}</span>
                </template>
                <template #item-phone_number="row">
                    <span class="truncate block max-w-[120px] sm:max-w-none">{{ row.phone_number || '-' }}</span>
                </template>
                <template #item-school_id="row">
                    <span v-if="row.teacher && row.teacher.school"
                        class="inline-block bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full px-2 py-0.5 text-xs font-semibold">
                        {{ row.teacher.school.name }}
                    </span>
                    <span v-else>-</span>
                </template>
                <template #item-status="row">
                    <span :class="{
                        'inline-block rounded-full px-2 py-0.5 text-xs font-semibold': true,
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': row.teacher?.status === 'pending',
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': row.teacher?.status === 'approved',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': row.teacher?.status === 'rejected',
                    }">
                        {{ row.teacher?.status || '-' }}
                    </span>
                </template>
                <template #item-actions="row">
                    <button v-can="'update-teachers'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="editTeacher(row.id)" aria-label="Edit Teacher" title="Edit Teacher">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-teachers'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="askDeleteTeacher(row.id)" aria-label="Delete Teacher" title="Delete Teacher">
                        <Icon name="trash" class="w-5 h-5" />
                    </button>
                </template>
                <template #item-expand="row">
                    <button @click="toggleRowExpansion(row)"
                        class="text-gray-500 hover:text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary rounded p-1 transition">
                        <svg v-if="expandedRow === row.id" class="w-4 h-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </template>
                <template #expand="row">
                    <div v-if="expandedRow === row.id"
                        class="bg-white dark:bg-neutral-900 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-700">
                        <!-- Header -->
                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-4">
                                <Avatar class="w-16 h-16 shadow">
                                    <AvatarImage v-if="row.profile_photo_url" :src="row.profile_photo_url"
                                        :alt="row.name" />
                                    <AvatarFallback>{{ getInitials(row.name) }}</AvatarFallback>
                                </Avatar>
                                <div>
                                    <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ row.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ row.email }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">@{{ row.username }}</div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-2 flex-1">
                                <div class="flex flex-wrap gap-2 justify-center">
                                    <span v-if="row.teacher && row.teacher.school"
                                        class="inline-block bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ row.teacher.school.name }}
                                    </span>
                                    <span v-for="role in row.roles" :key="role.id"
                                        class="inline-block bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 items-center">
                                <Button v-can="'approve-teachers'" v-if="isAdmin && row.teacher?.status !== 'approved'"
                                    variant="outline" @click="approveTeacher(row.id)">Approve</Button>
                                <Button v-can="'reset-passwords'" variant="default" @click="resetPassword(row.id)">Reset
                                    Password</Button>

                            </div>
                        </div>
                        <!-- Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Contact Information
                                </div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Email:</span> {{ row.email }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Phone:</span> {{ row.phone_number || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Username:</span> {{ row.username }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">CNIC:</span> {{ row.teacher?.cnic || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Gender:</span> {{ row.teacher?.gender || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span class="font-medium">Marital
                                        Status:</span> {{ row.teacher?.marital_status || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span class="font-medium">Date
                                        of
                                        Birth:</span> {{ row.teacher?.dob || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span class="font-medium">Date
                                        of
                                        Joining:</span> {{ row.teacher?.date_of_joining || '-' }}</div>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Classes &
                                    Affiliations
                                </div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Class:</span> {{ row.teacher?.class?.name || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Experience (years):</span> {{
                                            row.teacher?.experience_years
                                            || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Salary:</span> {{ row.teacher?.salary || '-' }}</div>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Other Details</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span
                                        class="font-medium">Status:</span> {{ row.teacher?.status || '-' }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-200"><span class="font-medium">School
                                        ID:</span> {{ row.teacher?.school_id || '-' }}</div>
                                <div v-can="'show-passwords'">
                                    <div class="font-semibold text-gray-700 dark:text-gray-300 mt-4 mb-2">Password</div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-700 dark:text-gray-200">
                                            <template v-if="showPassword[row.id]">
                                                {{ decryptedPasswords[row.id] }}
                                            </template>
                                            <template v-else>
                                                •••••••••
                                            </template>
                                        </span>
                                        <button class="text-gray-500 hover:text-primary-600 p-2"
                                            @click="toggleShowPassword(row)">
                                            <Eye v-if="!showPassword[row.id] && !passwordLoading[row.id]"
                                                class="w-5 h-5" />
                                            <EyeOff v-else-if="showPassword[row.id] && !passwordLoading[row.id]"
                                                class="w-5 h-5" />
                                            <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </template>
            </BaseDataTable>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete Teacher"
            message="Are you sure you want to delete this teacher? This action cannot be undone."
            :confirm-text="'Delete'" :cancel-text="'Cancel'" @confirm="deleteTeacher">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteTeacher">Delete</Button>
                </div>
            </template>
        </AlertDialog>
        <AlertDialog v-model="showPasswordErrorDialog" :title="passwordErrorTitle" :message="passwordErrorMessage" />
        <PasswordResetModal v-if="showPasswordResetModal" :user-id="resetUserId" @success="onPasswordResetSuccess"
            @cancel="() => showPasswordResetModal = false" />
        <SchoolSwitcher :isSuperAdmin="true" @switched="onSchoolSwitched" />
        <!-- Touch-friendly Bottom Sheet for Mobile Filters (direct child of AppLayout) -->
        <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
            ref="filterSheet" class="dark:bg-neutral-900">
            <div class="sheet-content dark:bg-neutral-900">
                <h2 class="text-lg font-semibold mb-4">Teacher Filters</h2>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col">
                        <label for="role-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Role</label>
                        <select id="role-mobile" v-model="filtersForm.role"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="teacher">Teacher</option>
                            <option value="principal">Principal</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="gender-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Gender</label>
                        <select id="gender-mobile" v-model="filtersForm.gender"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="status-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                        <select id="status-mobile" v-model="filtersForm.status"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="search-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Search</label>
                        <input id="search-mobile" v-model="filtersForm.search" type="text"
                            placeholder="Name, Email, CNIC, Contact"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-0.5 bg-white dark:bg-neutral-900" />
                    </div>
                    <div class="flex flex-col">
                        <label for="school-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                        <select id="school-mobile" v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All Schools</option>
                            <option v-for="school in schools" :key="school.id" :value="school.id">
                                {{ school.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </vue-bottom-sheet>
        <PasswordVerificationModal v-model="showPasswordModal" @success="onPasswordVerified"
            @cancel="closePasswordModal" />

    </AppLayout>
</template>

<script setup lang="ts">
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { ref, computed, watch, onMounted, inject } from 'vue';
import { router, Head, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import Button from '@/components/ui/button/Button.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import Icon from '@/components/Icon.vue';
import { BreadcrumbItem, User } from '@/types';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import SchoolSwitcher from '@/components/ui/SchoolSwitcher.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarImage from '@/components/ui/avatar/AvatarImage.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import { Eye, EyeOff, Filter as FilterIcon, Plus } from 'lucide-vue-next';
import { nextTick } from 'vue';
import PasswordResetModal from '@/components/ui/PasswordResetModal.vue';
import PasswordVerificationModal from "@/components/ui/PasswordVerificationModal.vue";
import { toast } from "vue3-toastify";

const filterSheet = ref<InstanceType<typeof VueBottomSheet>>();

function openFilterSheet() {
    filterSheet.value?.open();
}

function closeFilterSheet() {
    filterSheet.value?.close();
}

// Extend the default Inertia page props with our custom props
type TeachersPageProps = any & {
    teachers: any;
    filters: any;
    schools: any[];
    auth: any;
};

const closePasswordModal = () => {
    showPasswordModal.value = false;
    selectedUserForPassword.value = undefined;
};

const page = usePage<TeachersPageProps>();
const teachers = computed(() => page.props.teachers);
const initialFilters = page.props.filters || {};
const auth = computed(() => page.props.auth || {});
const schoolStore = useSchoolStore();
const { schools, selectedSchool } = storeToRefs(schoolStore);

const filtersForm = useForm({
    role: initialFilters.role || '',
    gender: initialFilters.gender || '',
    status: initialFilters.status || '',
    search: initialFilters.search || '',
    school_id: selectedSchool.value?.id || '',
    page: 1,
    per_page: 12,
});

const loading = ref(false);
const serverOptions = ref({
    page: 1,
    rowsPerPage: 12,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

// Initialize serverOptions with actual data when teachers data is available
watch(() => teachers.value, (newTeachers) => {
    if (newTeachers && newTeachers.current_page) {
        serverOptions.value.page = newTeachers.current_page;
        serverOptions.value.rowsPerPage = newTeachers.per_page || 12;
    }
}, { immediate: true });

// Computed properties for pagination
const serverItemsLength = computed(() => {
    return teachers.value?.total || 0;
});

const currentServerOptions = computed(() => {
    return {
        page: serverOptions.value.page,
        perPage: serverOptions.value.rowsPerPage,
    };
});
const expandedRow = ref<number | null>(null);
const showDeleteDialog = ref(false);
const teacherToDelete = ref<number | null>(null);
const showPassword = ref<Record<number, boolean>>({});
const decryptedPasswords = ref<Record<number, string>>({});
const passwordLoading = ref<Record<number, boolean>>({});
const passwordHideTimeouts = ref<Record<number, number>>({});
const showPasswordErrorDialog = ref(false);
const passwordErrorTitle = ref('Password Error');
const passwordErrorMessage = ref('');
const showPasswordResetModal = ref(false);
const resetUserId = ref<number | undefined>(undefined);
const selectedUserForPassword = ref<User | undefined>(undefined)
const showPasswordModal = ref(false)


const headers = [
    { text: 'Name', value: 'name' },
    { text: 'Email', value: 'email' },
    { text: 'CNIC', value: 'cnic' },
    { text: 'Role', value: 'role' },
    { text: 'Gender', value: 'gender' },
    { text: 'Phone', value: 'phone_number' },
    { text: 'School', value: 'school_id' },
    { text: 'Status', value: 'status' }, // Add status column
    { text: 'Actions', value: 'actions', sortable: false },
];

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Teachers', href: '/teachers' },
];

// Determine if user is single-school (not superadmin, only one school linked)
const userSchools = computed(() => auth.value.user?.schools || []);
const isSingleSchoolUser = computed(() => userSchools.value.length === 1 && !auth.value.user?.isSuperAdmin);

// If single-school user, auto-select and lock school filter
onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
    if (isSingleSchoolUser.value) {
        filtersForm.school_id = userSchools.value[0].id;
    } else if (selectedSchool.value?.id) {
        filtersForm.school_id = selectedSchool.value.id;
    }
});


function toggleShowPassword(teacher: User) {
    const id = teacher.id;

    if (showPassword.value[id]) {
        // Hide password and clear timeout
        showPassword.value[id] = false;
        decryptedPasswords.value[id] = '';
        if (passwordHideTimeouts.value[id]) {
            clearTimeout(passwordHideTimeouts.value[id]);
            delete passwordHideTimeouts.value[id];
        }
        return;
    }

    // Save the selected teacher for verification
    selectedUserForPassword.value = teacher;
    showPasswordModal.value = true;
}
const onPasswordVerified = async () => {
    const teacher = selectedUserForPassword.value;

    if (!teacher) {
        toast.error('No teacher selected.');
        return;
    }

    const id = teacher.id;
    passwordLoading.value[id] = true;

    try {
        const response = await fetch(`/teachers/get-password/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();

        if (data && data.password) {
            decryptedPasswords.value[id] = data.password;
            showPassword.value[id] = true;

            // Auto-hide after 5 minutes
            passwordHideTimeouts.value[id] = setTimeout(() => {
                showPassword.value[id] = false;
                decryptedPasswords.value[id] = '';
                delete passwordHideTimeouts.value[id];
            }, 300000);
        } else {
            toast.error(data?.error || 'Failed to retrieve password.');
        }
    } catch (error) {
        toast.error('Failed to retrieve password.');
    } finally {
        passwordLoading.value[id] = false;
        closePasswordModal();
    }
};

// Watch for filter changes with debounce
let debounceTimer: number;
type FilterField = 'role' | 'gender' | 'status' | 'school_id';
const watchedFields: FilterField[] = ['role', 'gender', 'status', 'school_id'];

watchedFields.forEach((field: FilterField) => {
    watch(() => filtersForm[field], () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchData();
        }, 500); // 500ms debounce
    });
});

// Watch serverOptions for pagination changes
watch(serverOptions, fetchData, { deep: true });

// Watch for global school change and update filter + fetch
watch(
    () => selectedSchool.value?.id,
    (newSchoolId) => {
        if (newSchoolId && (!isSingleSchoolUser.value || filtersForm.school_id !== newSchoolId)) {
            filtersForm.school_id = newSchoolId;
            fetchData();
        }
    }
);

function fetchData() {
    loading.value = true;
    // Always set school_id to selected global school if not superadmin or multi-school
    if (!auth.value.user?.isSuperAdmin && selectedSchool.value?.id) {
        filtersForm.school_id = selectedSchool.value.id;
    }

    // Set pagination parameters in the form
    filtersForm.page = serverOptions.value.page;
    filtersForm.per_page = serverOptions.value.rowsPerPage;

    filtersForm.get('/teachers', {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => (loading.value = false),
    });
}

function toggleRowExpansion(row: any) {
    expandedRow.value = expandedRow.value === row.id ? null : row.id;
}

function editTeacher(id: number) {
    router.visit(`/teachers/${id}/edit?school_id=${selectedSchool.value?.id || ''}`);
}

function askDeleteTeacher(id: number) {
    teacherToDelete.value = id;
    showDeleteDialog.value = true;
}

function deleteTeacher() {
    if (!teacherToDelete.value) return;
    loading.value = true;
    router.delete(`/teachers/${teacherToDelete.value}`, {
        preserveState: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            teacherToDelete.value = null;
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
}
function goToCreate() {
    router.visit(`/teachers/create?school_id=${selectedSchool.value?.id || ''}`, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            console.log('finish')
        },
    });
}
function onSchoolSwitched(school: any) {
    fetchData(); // Refetch teachers list when school is switched
}

function getInitials(name: string) {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

const isAdmin = computed(() => auth.value.user?.roles?.some((r: any) => r.name === 'admin' || r.name === 'superadmin'));

function approveTeacher(id: number) {
    router.post(`/teachers/${id}/approve`, {}, {
        preserveState: true,
        onSuccess: () => fetchData(),
    });
}



function onSearchInput(event: Event) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchData();
    }, 500); // 500ms debounce
}

function clearSearch() {
    filtersForm.search = '';
    fetchData();
}

function resetPassword(id: number) {
    resetUserId.value = id;
    showPasswordResetModal.value = true;
}

function onPasswordResetSuccess() {
    showPasswordResetModal.value = false;
    resetUserId.value = undefined;
    fetchData(); // Refetch teachers to show updated password
}
</script>

<style scoped>
.sheet-content {
    padding: 20px;
}

/* Ensure table responsiveness */
.overflow-x-auto {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* Ensure table cells don't wrap unnecessarily */
:deep(.v-data-table) {
    min-width: 1200px;
    /* Minimum width to accommodate all columns */
}

:deep(.v-data-table th),
:deep(.v-data-table td) {
    white-space: nowrap;
    padding: 0.75rem 0.5rem;
}

/* Allow some columns to wrap if needed */
:deep(.v-data-table td .truncate) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>