<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import { Trash } from 'lucide-vue-next';

interface Props {
    schools: { id: number; name: string }[];
    classes: { id: number; name: string }[];
    students: { id: number; name: string; registration_number: string }[];
    selectedSchoolId?: number;
    selectedClassId?: number;
}
const types = {
    "admission": "Admission Fee",
    "monthly": "Monthly Fee",
    "papers": "Papers Fee",
};

const props = defineProps<Props>();

// UseForm now with feeItems array
const form = useForm({
    fee_items: [
        {
            type: '',
            amount: '',
        },
    ],
    type: '',
    due_date: '',
    school_id: props.selectedSchoolId ? String(props.selectedSchoolId) : '',
    class_id: props.selectedClassId ? String(props.selectedClassId) : '',

});

const errors = computed(() => form.errors);

const today = computed(() => new Date().toISOString().split('T')[0]);

function goBack() {
    router.visit(route('fees.index'));
}
function getFeeItemError(index: number, field: 'type' | 'amount') {
    const key = `fee_items.${index}.${field}`;
    // `form.errors` is a Partial<Record<string, string>>
    // So just cast to string or undefined
    return (form.errors as Record<string, string | undefined>)[key];
}

function onSchoolChange() {
    form.class_id = '';
    if (form.school_id) {
        router.get(
            route('fees.create'),
            { school_id: form.school_id },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }
}

function onClassChange() {
    if (form.school_id && form.class_id) {
        router.get(
            route('fees.create'),
            { school_id: form.school_id, class_id: form.class_id },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }
}

// Add new empty fee item
function addFeeItem() {
    form.fee_items.push({ type: '', amount: '' });
}

// Remove fee item by index
function removeFeeItem(index: number) {
    form.fee_items.splice(index, 1);
}

function submitForm() {
    // Validate that each fee item has type and amount before submit
    let valid = true;
    form.fee_items.forEach((item) => {
        if (!item.type || !item.amount || Number(item.amount) <= 0) {
            valid = false;
            toast.error('Please fill in all fee item types and positive amounts.');
        }
    });
    if (!valid) return;

    form.post(route('fees.store'), {
        onSuccess: (response) => {
            if (response.props.errors && Object.keys(response.props.errors).length > 0) {
                Object.values(response.props.errors).flat().forEach((message) => {
                    if (typeof message === 'string') toast.error(message);
                });
            } else {
                toast.success('Fee created successfully!');
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach((message) => {
                if (typeof message === 'string') toast.error(message);
            });
        },
    });
}
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full px-4 py-8">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New Fee</h1>
                    <Button variant="outline" @click="goBack">Back to Fees</Button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- School -->
                        <SelectInput id="school_id" v-model="form.school_id" label="School"
                            :options="props.schools.map((s) => ({ label: s.name, value: String(s.id) }))"
                            placeholder="Select School" :error="errors.school_id" @change="onSchoolChange" />

                        <!-- fee type -->
                        <SelectInput id="fee_type" v-model="form.type" label="Fee Type"
                            :options="Object.entries(types).map(([value, label]) => ({ label, value }))"
                            placeholder="Select Fee Type" :error="errors.type" />
                        <!-- Class -->
                        <SelectInput id="class_id" v-model="form.class_id" label="Class"
                            :options="props.classes.map((c) => ({ label: c.name, value: String(c.id) }))"
                            placeholder="Select Class" :error="errors.class_id" :disabled="!form.school_id"
                            @change="onClassChange" />
                        <!-- Due Date -->
                        <TextInput id="due_date" v-model="form.due_date" label="Due Date" type="date" :min="today"
                            :error="errors.due_date" />


                    </div>
                    <div class="bg-gray-50 dark:bg-neutral-800 shadow rounded-lg p-4">
                        <label class="block mb-4 text-gray-700 dark:text-gray-200 font-medium">Fee Items</label>

                        <div v-for="(item, index) in form.fee_items" :key="index"
                            class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
                            <!-- Fee Type -->
                            <div class="w-full md:w-1/2">
                                <select v-model="item.type" :class="[
                                    'w-full px-3 py-2 rounded-md border',
                                    'bg-white dark:bg-neutral-900',
                                    'text-gray-900 dark:text-gray-100',
                                    'border-gray-300 dark:border-gray-600',
                                    'focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500',
                                    getFeeItemError(index, 'type') ? 'border-red-500' : ''
                                ]">
                                    <option value="" disabled>Select Fee Type</option>
                                    <option value="tuition">Tuition Fee</option>
                                    <option value="library">Library Fee</option>
                                    <option value="security">Security Fee</option>
                                    <option value="papers">Papers Fee</option>
                                    <option value="sports">Sports Fee</option>
                                    <option value="transport">Transport Fee</option>
                                </select>
                                <p v-if="getFeeItemError(index, 'type')" class="text-red-500 text-sm mt-1">
                                    {{ getFeeItemError(index, 'type') }}
                                </p>
                            </div>

                            <!-- Amount -->
                            <div class="w-full md:w-1/2">
                                <input v-model="item.amount" type="number" min="0" step="0.01"
                                    placeholder="Amount (PKR)" :class="[
                                        'w-full px-3 py-2 rounded-md border',
                                        'bg-white dark:bg-neutral-900',
                                        'text-gray-900 dark:text-gray-100',
                                        'border-gray-300 dark:border-gray-600',
                                        'focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500',
                                        getFeeItemError(index, 'amount') ? 'border-red-500' : ''
                                    ]" />
                                <p v-if="getFeeItemError(index, 'amount')" class="text-red-500 text-sm mt-1">
                                    {{ getFeeItemError(index, 'amount') }}
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <div class="w-full md:w-auto flex items-center justify-end md:justify-start">

                                <button type="button" @click="removeFeeItem(index)"
                                    :disabled="form.fee_items.length === 1"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md disabled:opacity-50">
                                    <Trash type="button" class="w-5 h-5  text-white cursor-pointer" />
                                </button>
                            </div>
                        </div>

                        <!-- Add Fee Item Button -->
                        <button type="button" @click="addFeeItem"
                            class="px-4 py-2 text-sm font-medium text-primary-700 border border-primary-600 hover:bg-primary-100 rounded-md mt-2">
                            + Add Fee Item
                        </button>
                    </div>

                    <!-- Students Preview -->
                    <div v-if="props.students.length" class="bg-blue-100 dark:bg-blue-800 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                            Total Students in Selected Class ({{ props.students.length }})
                        </h3>
                        <div class="max-h-40 overflow-y-auto">
                            <div class="grid grid-cols-3 sm:grid-cols-1 gap-2">
                                <div v-for="student in props.students" :key="student.id"
                                    class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ student.id }} : {{ student.name }} (Reg# {{ student.registration_number }})
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else
                        class="text-center py-4 px-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="text-yellow-800 dark:text-yellow-200 font-medium mb-1">⚠️ No Students Found</div>
                        <div class="text-yellow-700 dark:text-yellow-300 text-sm">
                            No admitted students found in the selected class. Please ensure there are admitted students
                            before creating fees.
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" @click="goBack">Cancel</Button>
                        <Button type="submit" :disabled="form.processing || !props.students.length">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else-if="!props.students.length">No Students Available</span>
                            <span v-else>Create Fee</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
