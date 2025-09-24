<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Fees" />
        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 pt-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Fees</h1>

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
                        placeholder="Search fees by student name, registration number..."
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
                <Button v-can="'create-fees'" variant="default" class="h-10" @click="goToCreate">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Fee
                </Button>
                <button @click="open"
                    class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition"
                    title="Show filters for fee records">
                    <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                    <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                </button>
            </div>
            <!-- Filter UI (hidden on mobile, shown in bottom sheet) -->
            <div class="grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6 items-end hidden lg:grid">
                <div class="flex flex-col">
                    <label for="type"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Type</label>
                    <select id="type" v-model="filtersForm.type"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="tuition">Tuition</option>
                        <option value="exam">Exam</option>
                        <option value="admission">Admission</option>
                        <option value="papers">Papers</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="status"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                    <select id="status" v-model="filtersForm.status"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="due_date_from"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Due Date From</label>
                    <input id="due_date_from" v-model="filtersForm.due_date_from" type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900" />
                </div>
                <div class="flex flex-col">
                    <label for="due_date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Due
                        Date To</label>
                    <input id="due_date_to" v-model="filtersForm.due_date_to" type="date"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900" />
                </div>
                <div class="flex flex-col">
                    <label for="amount"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Amount</label>
                    <input id="amount" v-model="filtersForm.amount" type="number" min="0" placeholder="Amount"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900" />
                </div>
                <div class="flex flex-col">
                    <Button v-can="'create-fees'" variant="default" class="h-10" @click="goToCreate">Add Fee</Button>
                </div>
            </div>
        </div>

        <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
            ref="myBottomSheet" class="dark:bg-neutral-900">
            <div class="sheet-content dark:bg-neutral-900">
                <h2 class="text-lg font-semibold mb-4">Fee Filters</h2>

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
                            placeholder="Search fees by student name, registration number..."
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
                        <label for="type-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Type</label>
                        <select id="type-mobile" v-model="filtersForm.type"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="tuition">Tuition</option>
                            <option value="exam">Exam</option>
                            <option value="admission">Admission</option>
                            <option value="papers">Papers</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="status-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                        <select id="status-mobile" v-model="filtersForm.status"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Due Date
                            Range</label>
                        <div class="flex gap-2">
                            <input type="date" v-model="filtersForm.due_date_from"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900"
                                placeholder="From" />
                            <input type="date" v-model="filtersForm.due_date_to"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900"
                                placeholder="To" />
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <label for="amount-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Amount</label>
                        <input id="amount-mobile" v-model="filtersForm.amount" type="number" min="0"
                            placeholder="Amount"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900" />
                    </div>
                    <div class="flex flex-col">
                        <label for="student-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Student
                            ID</label>
                        <input id="student-mobile" v-model="filtersForm.student_id" type="number" min="0"
                            placeholder="Student ID"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900" />
                    </div>
                </div>
            </div>
        </vue-bottom-sheet>

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 ">
            <!-- Fee Listing Table -->
            <BaseDataTable :headers="headers" :items="items" :loading="loading" :server-options="serverOptions"
                :server-items-length="serverItemsLength" :expandable="true"
                :expand-row-keys="expandedRow ? [expandedRow] : []"
                @update:server-options="(opts: Record<string, any>) => Object.assign(serverOptions, opts)"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all min-w-full"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #item-type="row">
                    <span
                        class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full px-2 py-0.5 text-xs font-semibold">
                        {{ row.type }}
                    </span>
                </template>
                <template #item-status="row">
                    <span :class="{
                        'inline-block rounded-full px-2 py-0.5 text-xs font-semibold': true,
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': row.status === 'Paid',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': row.status === 'Unpaid',
                    }">
                        {{ row.status }}
                    </span>
                </template>
                <template #item-amount="row">
                    <span class="font-semibold">Rs. {{ row.amount.toLocaleString() }}</span>
                </template>
                <template #item-actions="row">
                    <button v-can="'update-fees'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="editFee(row.id)" aria-label="Edit Fee" title="Edit Fee">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-fees'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="askDeleteFee(row.id)" aria-label="Delete Fee" title="Delete Fee">
                        <Icon name="trash" class="w-5 h-5" />
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

                        <!-- 👤 Student Header with Photo & Actions -->
                        <div class="flex flex-col md:flex-row items-center justify-between gap-6 border-b pb-4 mb-6">
                            <div class="flex items-center gap-4">
                                <img :src="row.fee.student.profile_photo_url" alt="Profile Photo"
                                    class="w-24 h-24 rounded-full object-cover border-4 border-purple-500" />
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">{{ row.student_name
                                    }}
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 flex items-center gap-1">
                                        <ClipboardList class="w-4 h-4" /> Registration #: {{
                                            row.fee.student.registration_number
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 flex items-center gap-1">
                                        <GraduationCap class="w-4 h-4" /> Class: {{ row.fee.student.class.name }} -
                                        Shift:
                                        {{
                                            row.fee.student.class_shift
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 flex items-center gap-1">
                                        <School class="w-4 h-4" /> School: {{ row.fee.student.school.name }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2 mt-4 md:mt-0">
                                <Button v-can="'print-vouchers'"
                                    v-if="row.status === 'unpaid' && row.due_date > today && row.type !== 'installments'"
                                    variant="default" class="bg-indigo-700 hover:bg-indigo-800 text-white text-sm"
                                    @click="printVoucher(row.id)">
                                    <Printer class="w-4 h-4 mr-2" /> Print Voucher
                                </Button>

                                <Button v-can="'mark-as-paid'" v-if="row.status === 'unpaid'" variant="default"
                                    class="bg-green-700 hover:bg-green-800 text-white text-sm"
                                    @click="openVoucherModal(row.id)">
                                    <CreditCard class="w-4 h-4 mr-2" /> Upload Voucher
                                </Button>
                                <span v-if="row.status === 'paid'">
                                    <span class="text-green-800 dark:text-green-700 font-semibold">Paid</span>
                                </span>

                                <Button v-can="'create-installments'"
                                    v-if="(row.type === 'monthly' && row.status === 'unpaid')"
                                    class="bg-yellow-700 hover:bg-yellow-800 text-white text-sm"
                                    @click="navigateToInstallments(row.id)">
                                    <Receipt class="w-4 h-4 mr-2" />Create
                                    Installments
                                </Button>
                                <Button v-else-if="(row.type === 'installments')" v-can="'view-installments'"
                                    class="bg-yellow-700 hover:bg-yellow-800 text-white text-sm"
                                    @click="navigateToInstallments(row.id)">
                                    <Receipt class="w-4 h-4 mr-2" />
                                    View Installments
                                </Button>
                            </div>
                        </div>

                        <!--Personal Information-->
                        <div class="mb-6">
                            <h3 class="flex items-center text-lg font-semibold text-blue-700 dark:text-blue-300 mb-4">
                                <UserPlus class="w-5 h-5 mr-2" /> Personal Information
                            </h3>
                            <div div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <FileText class="w-4 h-4" /> Nationality: {{ row.fee.student.nationality }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClipboardList class="w-4 h-4" /> B-Form #: {{ row.fee.student.b_form_number }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CalendarCheck class="w-4 h-4" /> Admission Date: {{ row.fee.student.admission_date
                                    }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <CalendarCheck class="w-4 h-4" /> Date of Birth: {{ row.fee.student.date_of_birth }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Gender: {{ row.fee.student.gender }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Award class="w-4 h-4" /> Status: {{ row.fee.student.status }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <School class="w-4 h-4" /> Previous School: {{ row.fee.student.previous_school ||
                                        'N/A'
                                    }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <BookOpenCheck class="w-4 h-4" /> Religion: {{ row.fee.student.religion }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <Users class="w-4 h-4" /> Inclusive: {{ row.fee.student.inclusive }}
                                </div>
                                <div v-if="row.other_inclusive_type" class="flex items-center gap-2">
                                    <ClipboardList class="w-4 h-4" /> Other Inclusive Type: {{
                                        row.fee.student.other_inclusive_type
                                    }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Orphan: {{ row.fee.student.is_orphan ? 'Yes' : 'No' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> Bricklin: {{ row.fee.student.is_bricklin ? 'Yes' : 'No'
                                    }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserPlus class="w-4 h-4" /> QSC: {{ row.fee.student.is_qsc ? 'Yes' : 'No' }}
                                </div>

                            </div>
                        </div>
                        <!-- Divider -->
                        <hr class="my-4 border-gray-300 dark:border-gray-600" />
                        <!-- Voucher Section -->
                        <div class=" pt-4 mt-4">
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
                            :submitUrl="'fees.markAsPaid'" @close="closeVoucherModal" @uploaded="onVoucherUploaded" />
                    </div>
                </template>

            </BaseDataTable>
        </div>
        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-model="showDeleteDialog" title="Delete Fee"
            message="Are you sure you want to delete this fee? This action cannot be undone." :confirm-text="'Delete'"
            :cancel-text="'Cancel'" @confirm="deleteFee">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteFee">Delete</Button>
                </div>
            </template>
        </AlertDialog>
    </AppLayout>
</template>

<script setup lang="ts">
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { ref, watch, computed } from "vue";
import { useForm, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { BreadcrumbItem } from '@/types';
import AppLayout from "@/layouts/AppLayout.vue";
import { Award, BookOpenCheck, CalendarCheck, ClipboardList, CreditCard, FileText, FilterIcon, GraduationCap, Printer, Receipt, School, UserPlus, Users } from "lucide-vue-next";
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import Button from '@/components/ui/button/Button.vue';
import Icon from '@/components/Icon.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import { Plus } from "lucide-vue-next";
import UploadVoucherModal from "@/components/UploadVoucherModal.vue";

// Define props interface for the data coming from backend
interface Props {
    fees: {
        data: Array<{
            id: number;
            voucher_number: string;
            type: string;
            status: string;
            amount: number;
            due_date: string;
            fine_amount?: number;
            fine_due_date?: string;
            paid_at?: string;
            student?: {
                name: string;
                registration_number: string;
                nationality: string;
                b_form_number: string;
                admission_date: string;
                date_of_birth: string;
                gender: string;
                status: string;
                previous_school?: string;
                religion: string;
                inclusive: string;
                other_inclusive_type?: string;
                is_orphan: boolean;
                is_bricklin: boolean;
                is_qsc: boolean;
                school?: {
                    name: string;
                };
                class?: {
                    name: string;
                };
            };
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        type?: string;
        status?: string;
        due_date_from?: string;
        due_date_to?: string;
        amount?: string;
        student_id?: string;
    };
}

const today = new Date().toISOString().split('T')[0];
const expandedRow = ref<number | null>(null);
function toggleRowExpansion(row: any) {
    expandedRow.value = expandedRow.value === row.id ? null : row.id;
}
// Define props
const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Fees',
        href: route('fees.index'),
    },
];
function printVoucher(installmentId: number) {
    const url = route('installments.voucher', { id: installmentId });
    fetch(url)
        .then(response => {
            if (!response.ok) {
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

const myBottomSheet = ref<InstanceType<typeof VueBottomSheet>>()

const open = () => {
    myBottomSheet?.value?.open();
}

const close = () => {
    myBottomSheet?.value?.close();
}

// Use Inertia form for filters
const filtersForm = useForm({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    status: props.filters?.status || '',
    due_date_from: props.filters?.due_date_from || '',
    due_date_to: props.filters?.due_date_to || '',
    amount: props.filters?.amount || '',
    student_id: props.filters?.student_id || '',
});

const loading = ref(false);
const serverOptions = ref({
    page: props.fees.current_page,
    rowsPerPage: props.fees.per_page,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

const serverItemsLength = computed(() => props.fees.total);

const headers = [
    { text: '#', value: 'id' },
    { text: 'Student', value: 'student_name' },
    { text: 'Voucher #', value: 'voucher_number' },
    { text: 'Type', value: 'type' },
    { text: 'Status', value: 'status' },
    { text: 'Amount', value: 'amount' },
    { text: 'Due Date', value: 'due_date' },
    { text: 'Fine Amount', value: 'fine_amount' },
    { text: 'Fine Due Date', value: 'fine_due_date' },
    { text: 'Paid At', value: 'paid_at' },
    { text: 'Actions', value: 'actions', sortable: false },
];

const items = computed(() => {
    return props.fees.data.map((fee, index) => ({
        id: fee.id,
        student_name: fee.student?.name || `Student #${fee.id}`,
        voucher_number: fee.voucher_number,
        type: fee.type,
        status: fee.status,
        amount: fee.amount,
        due_date: fee.due_date,
        fine_amount: fee.fine_amount,
        fine_due_date: fee.fine_due_date,
        paid_at: fee.paid_at ?? '-',
        fee: fee,
    }));
});

function fetchData() {
    loading.value = true;
    filtersForm.get(route('fees.index'), {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            console.log('Fetch data success:', page.props.students);
            // students.value = page.props.students as StudentsPagination;
            // total.value = students.value && typeof students.value.total === 'number' ? students.value.total : 0;
            // loading.value = false;
        },
        onError: () => {
            loading.value = false;
        }
    });
}


// Delete dialog state
const showDeleteDialog = ref(false);
const feeToDelete = ref<number | null>(null);

// Function to apply filters
function applyFilters() {
    filtersForm.get(route('fees.index'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

// Watch individual form fields with debounce
let debounceTimer: number;
type FilterField = 'search' | 'type' | 'status' | 'due_date_from' | 'due_date_to' | 'amount' | 'student_id';
const watchedFields: FilterField[] = ['search', 'type', 'status', 'due_date_from', 'due_date_to', 'amount', 'student_id'];

watchedFields.forEach((field: FilterField) => {
    watch(() => filtersForm[field], () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            applyFilters();
        }, 500); // 500ms debounce
    });
});

function editFee(id: number) {
    router.visit(route('fees.edit', id));
}

function navigateToInstallments(id: number) {
    router.visit(route('installments.create', id));
}

function goToCreate() {
    router.visit(route('fees.create'));
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

function askDeleteFee(id: number) {
    feeToDelete.value = id;
    showDeleteDialog.value = true;
}

function deleteFee() {
    if (!feeToDelete.value) return;

    router.delete(route('fees.destroy', feeToDelete.value), {
        onSuccess: () => {
            toast.success('Fee deleted successfully');
            showDeleteDialog.value = false;
            feeToDelete.value = null;
        },
        onError: () => {
            toast.error('Failed to delete fee');
            showDeleteDialog.value = false;
            feeToDelete.value = null;
        },
    });
}


const showVoucherModal = ref(false);
const selectedStudentId = ref<number | null>(null);
function openVoucherModal(id: number) {
    selectedStudentId.value = id;
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
</script>

<style scoped>
.sheet-content {
    padding: 20px;
}
</style>