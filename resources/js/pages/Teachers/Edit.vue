<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Edit Teacher" />
        <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Edit Teacher</h1>
            <form @submit.prevent="submit"
                class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <TextInput label="Teacher Name" v-model="form.name" :error="form.errors.name" required
                        placeholder="e.g. Ahmed Ali" />
                    <TextInput label="Teacher CNIC" v-model="form.cnic" :error="form.errors.cnic" required
                        placeholder="e.g. 12345-1234567-1" />
                    <TextInput label="Username" v-model="form.username" :error="form.errors.username" required
                        placeholder="e.g. teacher" />
                    <TextInput label="Email" v-model="form.email" :error="form.errors.email" required type="email"
                        placeholder="e.g. teacher@email.com" />
                    <SelectInput label="Gender" v-model="form.gender" :error="form.errors.gender" required
                        :options="[{ label: 'Male', value: 'Male' }, { label: 'Female', value: 'Female' }]" />
                    <SelectInput label="Marital Status" v-model="form.marital_status"
                        :error="form.errors.marital_status" required
                        :options="[{ label: 'Single', value: 'Single' }, { label: 'Married', value: 'Married' }]" />
                    <SelectInput label="Select Role" v-model="form.role_id" :error="form.errors.role_id" required
                        :options="roles.map((r: any) => ({ label: r.name.charAt(0).toUpperCase() + r.name.slice(1), value: r.id }))" />
                    <TextInput label="Date of Birth" v-model="form.dob" :error="form.errors.dob" required type="date"
                        placeholder="mm/dd/yyyy" />
                    <TextInput label="Salary" v-model="form.salary" :error="form.errors.salary" required type="number"
                        min="0" placeholder="e.g. 50000 in pkr" />
                    <TextInput label="Teacher Contact No" v-model="form.phone_number" :error="form.errors.phone_number"
                        required placeholder="e.g. 03001234567 or +923001234567" />
                    <TextInput label="Date of Joining" v-model="form.date_of_joining"
                        :error="form.errors.date_of_joining" required type="date" placeholder="mm/dd/yyyy" />
                    <TextInput label="Current Experience in Years" v-model="form.experience_years"
                        :error="form.errors.experience_years" type="number" min="0" max="99" placeholder="e.g. 5" />
                    <SelectInput v-if="schools.length" label="School" v-model="form.school_id"
                        :error="form.errors.school_id" required
                        :options="schools.map((s: any) => ({ label: s.name, value: s.id }))" />
                    <SelectInput v-if="filteredClasses.length" label="Assign Class" v-model="form.class_id"
                        :error="form.errors.class_id" required
                        :options="filteredClasses.map((c: any) => ({ label: c.name, value: c.id }))" />
                    <!-- Profile photo in last row, full width -->
                    <div class="md:col-span-3">
                        <FileInput label="Profile Photo (optional)" v-model="form.profile_photo"
                            :error="form.errors.profile_photo" :required="false" />
                    </div>
                </div>
                <div class="mt-8 flex gap-2 justify-end">
                    <Button variant="outline" type="button" @click="goBack">Cancel</Button>
                    <Button variant="default" type="submit" :disabled="form.processing">Update Teacher</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import FileInput from '@/components/form/FileInput.vue';
import { BreadcrumbItem } from '@/types';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';

const page = usePage<any>();
const teacher = page.props.teacher;
const roles = ref(page.props.roles || []);
const schoolStore = useSchoolStore();
const { schools: schoolsRaw, selectedSchool: selectedSchoolRaw, classes: classesRaw, sections: sectionsRaw } = storeToRefs(schoolStore);
const schools = computed(() => Array.isArray(schoolsRaw.value) ? schoolsRaw.value : []);
const classes = computed(() => Array.isArray(classesRaw.value) ? classesRaw.value : []);
const sections = computed(() => Array.isArray(sectionsRaw.value) ? sectionsRaw.value : []);
const selectedSchool = computed(() => selectedSchoolRaw.value);

const form = useForm({
    profile_photo: null,
    name: teacher.name || '',
    email: teacher.email || '',
    username: teacher.username || '',
    cnic: teacher.teacher?.cnic || '',
    gender: teacher.teacher?.gender || '',
    marital_status: teacher.teacher?.marital_status || '',
    role_id: teacher.teacher?.role_id || '',
    dob: teacher.teacher?.dob || '',
    salary: teacher.teacher?.salary || '',
    phone_number: teacher.phone_number || '',
    date_of_joining: teacher.teacher?.date_of_joining || '',
    experience_years: teacher.teacher?.experience_years || '',
    school_id: teacher.teacher?.school_id || selectedSchool.value?.id || '',
    class_id: teacher.teacher?.class_id || '',
});

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Teachers', href: '/teachers' },
    { title: 'Edit Teacher', href: `/teachers/${teacher.id}/edit` },
];

const filteredClasses = computed(() => {
    if (!selectedSchool.value) return [];
    return Array.isArray(classes.value)
        ? classes.value.filter((c: any) => c.pivot?.school_id == selectedSchool.value?.id)
        : [];
});

onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
});

function submit() {

    const formData = new FormData();
    const formDataObj = form.data();
    Object.keys(formDataObj).forEach(key => {
        const value = formDataObj[key as keyof typeof formDataObj];
        if (value !== null && value !== undefined) {
            formData.append(key, value);
        }
    });
    formData.append('_method', 'PUT');

    router.post(`/teachers/${teacher.id}`, formData, {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/teachers');
        },
        onError: (errors) => {
            console.error('Form errors:', errors); // Debug log
        },
    });
}
function goBack() {
    router.visit('/teachers');
}
</script>