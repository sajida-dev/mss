<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Add Teacher" />
        <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Add New Teacher</h1>
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
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" :class="[
                                'w-full px-3 py-2 pr-10 rounded-lg border bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-colors',
                                form.errors.password
                                    ? 'border-red-500 dark:border-red-500'
                                    : 'border-gray-300 dark:border-neutral-600'
                            ]"
                                placeholder="At least 8 characters, including uppercase, lowercase, number, and special character (@$!%*#?&^_-)">
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <Eye v-if="!showPassword" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="text-xs text-red-500 mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>
                    <SelectInput label="Gender" v-model="form.gender" :error="form.errors.gender" required
                        :options="[{ label: 'Male', value: 'Male' }, { label: 'Female', value: 'Female' }]" />
                    <SelectInput label="Marital Status" v-model="form.marital_status"
                        :error="form.errors.marital_status" required
                        :options="[{ label: 'Single', value: 'Single' }, { label: 'Married', value: 'Married' }]" />

                    <!-- Warning when no roles are available -->
                    <div v-if="!hasRoles" class="md:col-span-3">
                        <div
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        No roles available for this school
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>There are no teacher or principal roles configured for the selected school.
                                            Please contact the administrator to set up roles before creating teachers.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <SelectInput v-if="hasRoles" label="Select Role" v-model="form.role_id" :error="form.errors.role_id"
                        required
                        :options="roles.map((r: any) => ({ label: r.name.charAt(0).toUpperCase() + r.name.slice(1), value: r.id }))" />

                    <TextInput label="Date of Birth" v-model="form.dob" :error="form.errors.dob" required type="date"
                        placeholder="mm/dd/yyyy" />
                    <TextInput label="Salary" v-model="form.salary" :error="form.errors.salary" required type="number"
                        min="0" placeholder="e.g. 50000" />
                    <TextInput label="Teacher Contact No" v-model="form.phone_number" :error="form.errors.phone_number"
                        required placeholder="e.g. 03001234567 or +923001234567" />
                    <TextInput label="Date of Joining" v-model="form.date_of_joining"
                        :error="form.errors.date_of_joining" required type="date" placeholder="mm/dd/yyyy" />
                    <TextInput label="Current Experience in Years" v-model="form.experience_years"
                        :error="form.errors.experience_years" type="number" min="0" max="99" placeholder="e.g. 5" />

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
                    <Button variant="default" type="submit" :disabled="form.processing || !hasRoles">Add
                        Teacher</Button>
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
import { Eye, EyeOff } from 'lucide-vue-next';
import { toast } from 'vue3-toastify';

const schoolStore = useSchoolStore();
const { schools: schoolsRaw, selectedSchool: selectedSchoolRaw, classes: classesRaw, sections: sectionsRaw } = storeToRefs(schoolStore);
const schools = computed(() => Array.isArray(schoolsRaw.value) ? schoolsRaw.value : []);
const classes = computed(() => Array.isArray(classesRaw.value) ? classesRaw.value : []);
const sections = computed(() => Array.isArray(sectionsRaw.value) ? sectionsRaw.value : []);
const selectedSchool = computed(() => selectedSchoolRaw.value);
const page = usePage<any>();
const roles = ref(page.props.roles || []);
const hasRoles = ref(page.props.hasRoles || false);
const selectedSchoolId = ref(page.props.selectedSchoolId || null);
const showPassword = ref(false);
const form = useForm({
    profile_photo: null,
    name: '',
    email: '',
    username: '',
    password: '',
    cnic: '',
    gender: '',
    marital_status: '',
    role_id: '',
    dob: '',
    salary: '',
    phone_number: '',
    date_of_joining: '',
    experience_years: '',
    school_id: selectedSchoolId.value || '',
    class_id: '',
});

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Teachers', href: '/teachers' },
    { title: 'Add Teacher', href: '/teachers/create' },
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
    form.post('/teachers', {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/teachers');
            toast.success('Teacher added successfully');
        },
        onError: () => {
            toast.error('Failed to add teacher. Please check the form for errors.');
        },
        onFinish: () => {
            form.reset('password', 'profile_photo');
        },
    });
}
function goBack() {
    router.visit('/teachers');
}
</script>