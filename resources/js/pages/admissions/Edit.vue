<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Edit Student" />
        <div class="p-4 bg-neutral-100 dark:bg-neutral-900 min-h-screen">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">Edit Student</h1>
            <AdmissionsForm v-if="schools.length && classes.length" :form="form" :errors="form.errors" mode="edit"
                :classes="classesList" :schools="schoolsList" :existing-photo="student.profile_photo_path"
                @submit="submit" @cancel="goBack" />
            <div v-else class="flex items-center justify-center py-10">
                <span>Loading...</span>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { usePage, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import AdmissionsForm from '@/components/admissions/AdmissionsForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch, onMounted } from 'vue';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import { route } from 'ziggy-js';

const breadcrumbs = [
    { title: 'Admissions', href: '/admissions' },
    { title: 'Edit Student' }
];

const page = usePage();
const student = page.props.student;

const schoolStore = useSchoolStore();
const { schools: schoolsRaw, classes: classesRaw, selectedSchool } = storeToRefs(schoolStore);
const schools = computed(() => Array.isArray(schoolsRaw.value) ? schoolsRaw.value : []);
const classes = computed(() => Array.isArray(classesRaw.value) ? classesRaw.value : []);
const schoolsList = computed(() => schools.value);
const classesList = computed(() => classes.value.map((c) => ({ label: c.name, value: c.id })));

onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
});

const form = useForm({
    school_id: student.school_id,
    class_id: student.class_id,
    nationality: student.nationality,
    registration_number: student.registration_number,
    name: student.name,
    b_form_number: student.b_form_number,
    admission_date: student.admission_date,
    date_of_birth: student.date_of_birth,
    gender: student.gender,
    class_shift: student.class_shift,
    previous_school: student.previous_school,
    inclusive: student.inclusive,
    other_inclusive_type: student.other_inclusive_type,
    religion: student.religion,
    is_bricklin: student.is_bricklin,
    is_orphan: student.is_orphan,
    is_qsc: student.is_qsc,
    profile_photo_path: null,
    father_name: student.father_name,
    guardian_name: student.guardian_name,
    father_cnic: student.father_cnic,
    mother_cnic: student.mother_cnic,
    father_profession: student.father_profession,
    no_of_children: student.no_of_children,
    job_type: student.job_type,
    father_education: student.father_education,
    mother_education: student.mother_education,
    mother_profession: student.mother_profession,
    father_income: student.father_income,
    mother_income: student.mother_income,
    household_income: student.household_income,
    permanent_address: student.permanent_address,
    phone_no: student.phone_no,
    mobile_no: student.mobile_no,
    admission_fee: student.fee.amount,
    due_date: student.fee.due_date,
});

watch(
    [() => selectedSchool.value, () => schools.value],
    ([newSchool, newSchools]) => {
        if (newSchool && Array.isArray(newSchools) && newSchools.length > 0) {
            const school = newSchools.find(s => s.id === newSchool.id);
            if (school && form.school_id !== school.id) {
                form.school_id = school.id;
                schoolStore.setSchool(school); // Ensure store is always in sync
            }
        }
    },
    { immediate: true }
);

function submit() {
    form.post(route('admissions.update', student.id), {
        forceFormData: true,
        data: {
            ...form.data(),
            _method: 'PUT',
        },
        onSuccess: () => {
            toast.success('Student updated successfully!');
            router.visit(route('admissions.index'));
        },
        onError: (e) => {
            console.log('error: ', e)
            toast.error('Please fix the errors in the form.' + e);
        },
    });
}

function goBack() {
    router.visit(route('admissions.index'));
}
</script>
