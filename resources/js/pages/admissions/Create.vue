<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Add New Student" />
        <div class="p-4 bg-neutral-100 dark:bg-neutral-900 min-h-screen">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">Add New Student</h1>
            <AdmissionsForm v-if="schools.length && classes.length" :form="form" :errors="form.errors" mode="create"
                :classes="classesList" :schools="schoolsList" @submit="submit" @cancel="goBack" />
            <div v-else class="flex flex-col items-center justify-center py-10 gap-2">
                <span v-if="!schools.length && !classes.length">Loading schools and classes...</span>
                <span v-else-if="!schools.length">Unable to load schools. Please check your network or contact
                    admin.</span>
                <span v-else-if="!classes.length">Unable to load classes. Please check your network or contact
                    admin.</span>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import AdmissionsForm from '@/components/admissions/AdmissionsForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import { Head } from '@inertiajs/vue3';
import { computed, watch, onMounted } from 'vue';
import { route } from 'ziggy-js';
import { BreadcrumbItem } from '@/types';

const schoolStore = useSchoolStore();
const { schools: schoolsRaw, classes: classesRaw } = storeToRefs(schoolStore);
const schools = computed(() => Array.isArray(schoolsRaw.value) ? schoolsRaw.value : []);
const classes = computed(() => Array.isArray(classesRaw.value) ? classesRaw.value : []);
const schoolsList = computed(() => schools.value);
const classesList = computed(() => classes.value.map((c) => ({ label: c.name, value: c.id })));

onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admissions', href: '/admissions' },
    { title: 'Add Student', href: '/admissions/create' }
];

const form = useForm({
    school_id: null as number | null,
    class_id: null as number | null,
    nationality: '',
    registration_number: '',
    name: '',
    b_form_number: '',
    admission_date: '',
    date_of_birth: '',
    gender: '',
    class_shift: '',
    previous_school: '',
    inclusive: '',
    other_inclusive_type: '',
    religion: '',
    is_bricklin: false,
    is_orphan: false,
    is_qsc: false,
    profile_photo_path: null,
    father_name: '',
    guardian_name: '',
    father_cnic: '',
    mother_cnic: '',
    father_profession: '',
    no_of_children: '',
    job_type: '',
    father_education: '',
    mother_education: '',
    mother_profession: '',
    father_income: '',
    mother_income: '',
    household_income: '',
    permanent_address: '',
    phone_no: '',
    mobile_no: '',
    admission_fee: null,
    due_date: null,
});

// Watch for changes in form.school_id and update the selected school in the store
watch(
    () => form.school_id,
    (newSchoolId) => {
        const school = Array.isArray(schools.value) ? schools.value.find(s => s.id === newSchoolId) : undefined;
        if (school) {
            schoolStore.setSchool(school);
        }
    }
);

watch(
    [() => schoolStore.selectedSchool, () => schools.value],
    ([newSchool, newSchools]) => {
        if (newSchool && Array.isArray(newSchools) && newSchools.length > 0) {
            const school = newSchools.find(s => s.id === newSchool.id);
            if (school && form.school_id !== school.id) {
                form.school_id = school.id;
                form.class_id = null;
                schoolStore.setSchool(school); // Ensure store is always in sync
            }
        }
    },
    { immediate: true }
);

const validateForm = () => {
    const validationErrors: Record<string, string> = {}

    if (form.inclusive === 'others' && !form.other_inclusive_type) {
        validationErrors.other_inclusive_type = 'Other Inclusive Type is required when Inclusive is "others".'
    }

    return validationErrors
}

function submit() {

    const validationErrors = validateForm()

    if (Object.keys(validationErrors).length > 0) {
        Object.assign(form.errors, validationErrors)
        return
    }

    form.post(route('admissions.store'), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Student added successfully!');
            router.visit(route('admissions.index'));
        },
        onError: (e) => {
            console.log('error: ', e)
            toast.error('Please fix the errors in the form.' + e);
        },
    });
}
watch(() => form.inclusive, (newValue) => {
    if (newValue !== 'others') {
        form.other_inclusive_type = ''
        form.errors.other_inclusive_type = ''
    }
})

function goBack() {
    router.visit(route('admissions.index'));
}
</script>

<style scoped></style>