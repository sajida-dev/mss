<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Edit School" />
        <div class="max-w-5xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-8 text-neutral-900 dark:text-neutral-100">Edit School</h1>
            <form @submit.prevent="submit"
                class="bg-white dark:bg-neutral-900 p-8 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-md w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-6">
                        <div>
                            <h2 class="text-lg font-semibold mb-4 text-neutral-800 dark:text-neutral-200">School
                                Information</h2>
                            <div class="flex flex-col gap-4">
                                <div>
                                    <Label class="mb-1 ml-1" for="name">Name<span class="text-red-500">*</span></Label>
                                    <Input id="name" v-model="form.name" required placeholder="School name"
                                        autocomplete="off" />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                                <div>
                                    <Label class="mb-1 ml-1" for="address">Address<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="address" v-model="form.address" required placeholder="School address"
                                        autocomplete="off" />
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>
                                <div>
                                    <Label class="mb-1 ml-1" for="contact">Contact<span
                                            class="text-red-500">*</span></Label>
                                    <Input id="contact" v-model="form.contact" required placeholder="Contact info"
                                        autocomplete="off" />
                                    <InputError class="mt-2" :message="form.errors.contact" />
                                </div>
                            </div>
                        </div>
                    </div>
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
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full">
                        <FileInput label="Main Image (optional)" v-model="form.main_image"
                            :preview-url="mainImagePreview" :error="form.errors.main_image" :required="false" />
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-center justify-end gap-4 mt-10">
                    <Button type="button" variant="outline" @click="goBack">Cancel</Button>
                    <Button :disabled="form.processing">Update</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm, usePage, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import { ref, onMounted, computed } from 'vue';
import ImageUpload from '@/components/ui/ImageUpload.vue';
import AvatarUpload from '@/components/ui/AvatarUpload.vue';
import FileInput from '@/components/form/FileInput.vue';

const page = usePage();
const school = page.props.school as any;
const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
    { title: 'Edit School', href: `/schools/${school.id}/edit` },
];

const form = useForm({
    name: school.name || '',
    address: school.address || '',
    contact: school.contact || '',
    logo: null,
    main_image: null,
});


const logoFile = ref<File | undefined>();
const logoPreview = ref<string>(school.logo_url || 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s');
const mainImageFile = ref<File | undefined>();
const mainImagePreview = ref<string>(school.main_image || 'https://www.shutterstock.com/image-vector/school-building-front-view-entrance-260nw-2494026401.jpg');
const schoolStore = useSchoolStore();
const { schools: schoolsRaw } = storeToRefs(schoolStore);
const schools = computed(() => Array.isArray(schoolsRaw.value) ? schoolsRaw.value : []);

onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
});

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('address', form.address);
    formData.append('contact', form.contact);

    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }
    if (mainImageFile.value) {
        formData.append('main_image', mainImageFile.value);
    }
    router.post(route('schools.update', school.id), formData, {
        forceFormData: true,
        onSuccess: async () => {
            await schoolStore.fetchSchools();
            toast.success('School updated successfully.');
            router.visit(route('schools.index'));
        },
        onError: () => {
            toast.error('Please fix the errors and try again.');
        },
    });
};

const goBack = () => {
    router.visit(route('schools.index'));
};
</script>
