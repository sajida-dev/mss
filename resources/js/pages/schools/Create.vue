<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Add School" />
        <div class="max-w-5xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-8 text-neutral-900 dark:text-neutral-100">Add School</h1>
            <form @submit.prevent="submit"
                class="bg-white dark:bg-neutral-900 p-8 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-md w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column: School Info -->
                    <div class="flex flex-col gap-6">
                        <div>
                            <h2 class="text-lg font-semibold mb-4 text-neutral-800 dark:text-neutral-200">School
                                Information</h2>
                            <div class="flex flex-col gap-4">
                                <div>
                                    <Label class="mb-1 ml-1" for="name">Name<span class="text-red-500">*</span></Label>
                                    <Input id="name" v-model="form.name" required
                                        placeholder="School name (letters, numbers, spaces, hyphens, periods only)"
                                        autocomplete="off" pattern="[a-zA-Z0-9\s\-\.]+"
                                        title="Letters, numbers, spaces, hyphens, and periods only" />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                                <div>
                                    <Label class="mb-1 ml-1" for="address">Address<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="address" v-model="form.address" required
                                        placeholder="School address (minimum 10 characters)" autocomplete="off"
                                        minlength="10" maxlength="500"
                                        title="Address must be at least 10 characters long" />
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>
                                <div>
                                    <Label class="mb-1 ml-1" for="contact">Contact<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="contact" v-model="form.contact" required
                                        placeholder="Contact number e.g. 03001234567 or +923001234567"
                                        autocomplete="off" pattern="[\d\s\-\+\(\)]{7,20}"
                                        title="7-20 digits, may include spaces, dashes, plus signs, and parentheses" />
                                    <InputError class="mt-2" :message="form.errors.contact" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Logo Only -->
                    <div class="flex flex-col gap-6 items-center justify-center">
                        <div>
                            <div class="flex flex-col items-center gap-2">
                                <AvatarUpload v-model="logoFile" :preview="logoPreview"
                                    @update:preview="(val: string) => logoPreview = val" label="Logo"
                                    hint="PNG, JPG up to 1MB" :max-size-m-b="1" :width="150" :height="150" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Main Image: Full width below columns -->
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full">
                        <ImageUpload v-model="mainImageFile" :preview="mainImagePreview"
                            @update:preview="(val: string) => mainImagePreview = val" label="Main Image"
                            hint="PNG, JPG up to 2MB" :max-size-m-b="2" :width="640" :height="256" />
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-center justify-end gap-4 mt-10">
                    <Button type="button" variant="outline" @click="goBack">Cancel</Button>
                    <Button :disabled="form.processing">Save</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import type { School } from '@/stores/school';
import { ref, onMounted, computed } from 'vue';
import ImageUpload from '@/components/ui/ImageUpload.vue';
import AvatarUpload from '@/components/ui/AvatarUpload.vue';

const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
    { title: 'Add School', href: '/schools/create' },
];

const form = useForm({
    name: '',
    address: '',
    contact: '',
});

const logoFile = ref<File | undefined>();
const logoPreview = ref<string>('');
const mainImageFile = ref<File | undefined>();
const mainImagePreview = ref<string>('');
const schoolStore = useSchoolStore();
const { schools: schoolsRaw } = storeToRefs(schoolStore);
const schools = computed(() => Array.isArray(schoolsRaw.value) ? schoolsRaw.value : []);

onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
});

function submit() {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('address', form.address);
    formData.append('contact', form.contact);
    if (logoFile.value) formData.append('logo', logoFile.value);
    if (mainImageFile.value) formData.append('main_image', mainImageFile.value);
    router.post(route('schools.store'), formData, {
        forceFormData: true,
        onSuccess: async (page) => {
            toast.success('School created successfully.');
            await schoolStore.fetchSchools();
            router.visit(route('schools.index'));
        },
        onError: () => {
            toast.error('Please fix the errors and try again.');
        },
    });
}

const goBack = () => {
    router.visit(route('schools.index'));
};
</script>