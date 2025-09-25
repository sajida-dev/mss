<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Installments Management" />

        <div class="max-w-7xl mx-auto w-full px-4 py-6 sm:py-8">

            <!-- Header Section -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
                        <Calendar class="w-8 h-8 text-blue-600" />
                        Installments Management
                    </h1>
                </div>
            </div>
            <!-- Student and Fee Info -->
            <div v-if="student"
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-6">
                <!-- Warning Info Box -->
                <div class="mb-6 p-4 border-l-4 border-yellow-400 bg-yellow-50 dark:bg-neutral-800/50 rounded-md">
                    <h3 class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">
                        Please Note
                    </h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Once you create the installments, they <strong>cannot be edited or deleted</strong>. Please
                        double-check the due dates and amounts before proceeding.
                    </p>
                </div>
                <div
                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ student.name }} ({{ student.registration_number }})
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Total Fee: <span class="font-medium text-gray-900 dark:text-gray-100">Rs {{ fee?.amount
                            }}</span>
                    </p>
                </div>
                <!-- Installment Generator -->
                <form @submit.prevent="submitInstallments" class="space-y-8" v-if="!existingInstallments.length">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div>
                            <Label for="num_installments"
                                class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                No. of Installments
                            </Label>
                            <input v-model.number="numInstallments" @input="generateInstallments" type="number" min="1"
                                id="num_installments"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                        </div>
                    </div>
                    <div v-for="(inst, i) in installments" :key="i"
                        class="p-6 border border-gray-300 dark:border-neutral-700 rounded-lg shadow-sm bg-white dark:bg-neutral-800 space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                Installment {{ i + 1 }}
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                            <div>
                                <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Due
                                    Date <span class="text-red-500">*</span></Label>
                                <input type="date" v-model="inst.due_date" required
                                    class="w-full px-3 py-2 text-sm border rounded-md border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>

                            <div>
                                <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Amount
                                    (Rs)</Label>
                                <input type="number" v-model.number="inst.amount" step="0.01" min="0" required disabled
                                    class="w-full px-3 py-2 text-sm border rounded-md border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div>
                                <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Fine Due
                                    Date <span class="text-red-500">*</span></Label>
                                <input type="date" v-model="inst.fine_due_date" required
                                    class="w-full px-3 py-2 text-sm border rounded-md border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>

                            <div>
                                <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Fine
                                    Amount
                                    (Rs) <span class="text-red-500">*</span></Label>
                                <input type="number" v-model.number="inst.fine_amount" step="0.01" min="0" required
                                    class="w-full px-3 py-2 text-sm border rounded-md border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>

                        <!-- Fee Item Breakdown -->
                        <div>
                            <Label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Fee Items
                                Breakdown</Label>

                            <div class="space-y-3">
                                <div v-for="(item, idx) in inst.fee_items_breakdown" :key="idx"
                                    class="flex items-center gap-3">
                                    <select v-model="item.type" disabled
                                        class="flex-1 px-3 py-2 text-sm border rounded-md border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled>Select Fee Type</option>
                                        <option value="tuition">Tuition Fee</option>
                                        <option value="library">Library Fee</option>
                                        <option value="security">Security Fee</option>
                                        <option value="papers">Papers Fee</option>
                                        <option value="sports">Sports Fee</option>
                                        <option value="transport">Transport Fee</option>

                                    </select>


                                    <input type="number" v-model.number="item.amount" placeholder="Amount" step="0.01"
                                        disabled min="0"
                                        class="w-32 px-3 py-2 text-sm border rounded-md border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />


                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <div class="flex gap-2">
                            <Button type="button" @click="router.get(route('fees.index'))" class="px-6 py-2 text-sm">
                                <ArrowLeft class="w-4 h-4" />
                                Cancel
                            </Button>
                            <Button type="submit" class="px-6 py-2 text-sm">
                                <Check v-if="!loading" class="w-4 h-4" />
                                <Loader v-else class="w-4 h-4 animate-spin" />
                                Save Installments
                            </Button>
                        </div>

                    </div>
                </form>
                <div v-else class="bg-white dark:bg-neutral-900 rounded-xl  py-6">
                    <div class="flex flex-row justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Existing Installments
                        </h3>
                        <Button type="button" @click="router.get(route('fees.index'))" class="px-6 py-2 text-sm">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back
                        </Button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-200 dark:border-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="p-3 uppercase tracking-wider">Installment</th>
                                    <th class="p-3 uppercase tracking-wider">Due Date</th>
                                    <th class="p-3 uppercase tracking-wider">Amount</th>
                                    <th class="p-3 uppercase tracking-wider">Status</th>
                                    <th class="p-3 uppercase tracking-wider">Paid At</th>
                                    <th class="p-3 uppercase tracking-wider">Fine Amount</th>
                                    <th class="p-3 uppercase tracking-wider">Fine Due Date</th>
                                    <th class="p-3 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="inst, index in existingInstallments" :key="inst.id"
                                    class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    <td class="p-3">Installment - {{ index + 1 }}</td>
                                    <td class="p-3">{{ inst.due_date }}</td>
                                    <td class="p-3">Rs {{ inst.amount }}</td>
                                    <td class="p-3">{{ inst.status }}</td>
                                    <td class="p-3">{{ inst.paid_at ?? '-' }}</td>
                                    <td class="p-3">{{ inst.fine_amount }}</td>
                                    <td class="p-3">{{ inst.fine_due_date }}</td>
                                    <td class="flex justify-center items-center gap-2">
                                        <Button v-can="'print-vouchers'" v-if="inst.status === 'unpaid'"
                                            variant="default"
                                            class="bg-indigo-700 hover:bg-indigo-800 text-white text-sm"
                                            @click="printVoucher(inst.id!)">
                                            <Printer class="w-4 h-4" />
                                        </Button>
                                        <Button variant="default" v-if="inst.status !== 'paid'"
                                            class="bg-green-700 hover:bg-green-800 text-white text-sm"
                                            @click="openVoucherModal(inst.id!)">
                                            <CreditCard class="w-4 h-4" />
                                        </Button>
                                        <span v-else>
                                            <span class="text-green-800 dark:text-green-700 font-semibold">Paid</span>
                                        </span>
                                    </td>
                                    <!-- Modal -->
                                    <UploadVoucherModal v-if="showVoucherModal && selectedStudentId === inst.id"
                                        :id="inst.id" :submitUrl="'installments.pay'" @close="closeVoucherModal"
                                        @uploaded="onVoucherUploaded" />
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Check, CreditCard, Loader, Printer, Search } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { toast } from 'vue3-toastify';
import UploadVoucherModal from '@/components/UploadVoucherModal.vue';
const loading = ref(false);

interface Student { id: number; name: string; registration_number: string; }
interface Fee { id: number; name: string; fee_type: string; amount: number; fee_description: string; }
interface FeeItem { id: number; type: string; amount: number; }
interface Installment {
    due_date: string;
    amount: number;
    fee_items_breakdown: FeeItem[];
    status?: string;
    paid_at?: string;
    id?: number;
    fine_amount?: number;
    fine_due_date?: string;
}

// const form = useForm({ registration_number: '' });


const props = defineProps<{
    student: Student | null;
    fee: Fee | null;
    fee_items: FeeItem[];
    installments: Installment[];
}>();

const student = ref(props.student);
const fee = ref(props.fee);
const feeItems = ref(props.fee_items);
const existingInstallments = ref(props.installments);
const numInstallments = ref(1);
const installments = ref<Installment[]>([]);

const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Fee', href: '/fees' },
];

function generateInstallments() {
    if (!fee.value || !numInstallments.value || numInstallments.value < 1) return;

    const n: number = numInstallments.value;
    const originalFeeItems = feeItems.value;

    const totalAmount = originalFeeItems.reduce((sum, item) => sum + item.amount, 0);

    const dividedItems = originalFeeItems.map(item => ({
        ...item,
        amount: parseFloat((item.amount / n).toFixed(2)),
    }));

    const baseInstallmentAmount = parseFloat(
        dividedItems.reduce((sum, item) => sum + item.amount, 0).toFixed(2)
    );

    let installmentsArray = Array.from({ length: n }, () => ({
        due_date: '',
        amount: baseInstallmentAmount,
        fee_items_breakdown: dividedItems.map(f => ({ ...f })),
    }));

    let totalGenerated = baseInstallmentAmount * n;

    let roundingDifference = parseFloat((totalAmount - totalGenerated).toFixed(2));

    let i = 0;
    while (Math.abs(roundingDifference) >= 0.01 && i < n) {
        installmentsArray[i].amount = parseFloat((installmentsArray[i].amount + (roundingDifference > 0 ? 0.01 : -0.01)).toFixed(2));
        roundingDifference = parseFloat((totalAmount - installmentsArray.reduce((sum, inst) => sum + inst.amount, 0)).toFixed(2));
        i++;
    }

    installments.value = installmentsArray;
}



function submitInstallments() {
    loading.value = true;
    if (!fee.value) return;
    router.post(route('installments.store'),
        {
            fee_id: fee.value.id,
            installments: installments.value,
        },
        {
            onSuccess: () => {
                toast.success('Installments created successfully.');
                loading.value = false;
            },
            onError: (e) => {
                toast.error('Failed to create installments.' + e);
                loading.value = false;
            },
        }
    );
}

function printVoucher(feeId: number) {
    const url = route('installments.voucher', { id: feeId });

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


const showVoucherModal = ref(false);
const selectedStudentId = ref<number | null>(null);

function openVoucherModal(id: number) {
    selectedStudentId.value = id!;
    showVoucherModal.value = true;
}
function closeVoucherModal() {
    showVoucherModal.value = false;
    selectedStudentId.value = null;
}
function onVoucherUploaded() {
    console.log('Voucher uploaded callback triggered');
    closeVoucherModal();
    router.visit(route('installments.create', fee.value?.id));
}

</script>
