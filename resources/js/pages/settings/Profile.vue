<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, inject } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User & { profile_photo_url?: string, profile_photo_path?: string };

const form = useForm({
    name: user.name,
    username: user.username,
    email: user.email,
    phone_number: user.phone_number,
    profile_photo: null as File | null,
});

const profilePhotoUrl = user.profile_photo_url || '/storage/default-profile.png';
const photoPreview = ref<string | null>(null);

const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
const maxSizeMB = 2;

const removePhoto = () => {
    if (window.confirm('Are you sure you want to remove your profile photo?')) {
        form.post(route('profile.remove-photo'), {
            preserveScroll: true,
            onSuccess: () => {
                photoPreview.value = null;
            },
        });
    }
};

const emitter = inject('emitter') as { emit?: Function } | undefined;

const onFileInput = (event: Event) => {
    const target = event.target as HTMLInputElement | null;
    if (target && target.files && target.files[0]) {
        const file = target.files[0];
        if (!allowedTypes.includes(file.type)) {
            if (emitter && emitter.emit) {
                emitter.emit('alert', {
                    title: 'Invalid File Type',
                    message: 'Only JPG and PNG images are allowed.',
                    confirmText: 'OK',
                });
            }
            return;
        }
        if (file.size > maxSizeMB * 1024 * 1024) {
            if (emitter && emitter.emit) {
                emitter.emit('alert', {
                    title: 'File Too Large',
                    message: 'Image size must be less than 2MB.',
                    confirmText: 'OK',
                });
            }
            return;
        }
        form.profile_photo = file;
        const reader = new FileReader();
        reader.onload = e => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    const data = { ...form.data() };
    if (form.profile_photo) {
        data.profile_photo = form.profile_photo;
    }
    (data as any)._method = 'PATCH';
    form.transform(() => data).post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            photoPreview.value = null;
            toast.success('Profile updated successfully!');
        },
        onError: () => {
            toast.error('Failed to update profile!');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Profile settings" />
        <SettingsLayout>
            <div class="flex flex-col space-y-8 max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0">
                <HeadingSmall title="Profile information" description="Update your name, email, and profile photo" />
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="flex flex-col md:flex-row gap-6 md:gap-6 items-stretch">
                        <div class="flex-1 flex flex-col gap-4">
                            <div>
                                <Label for="name">Name</Label>
                                <Input id="name" class="mt-1 block w-full" v-model="form.name" required
                                    autocomplete="name" placeholder="Full name" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>
                            <div>
                                <Label for="username" class="mt-2">Username</Label>
                                <Input id="username" type="text" class="mt-1 block w-full" v-model="form.username"
                                    required autocomplete="username" placeholder="Username" />
                                <InputError class="mt-2" :message="form.errors.username" />
                            </div>
                            <div>
                                <Label for="email" class="mt-2">Email address</Label>
                                <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                                    autocomplete="email" placeholder="Email address" />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div>
                                <Label for="phone_number" class="mt-2">Phone Number</Label>
                                <Input id="phone_number" type="text" class="mt-1 block w-full"
                                    v-model="form.phone_number" required autocomplete="tel"
                                    placeholder="Phone Number" />
                                <InputError class="mt-2" :message="form.errors.phone_number" />
                            </div>
                        </div>
                        <div
                            class="flex flex-col items-center justify-center gap-3 w-full max-w-xl md:max-w-[300px] mx-auto p-5 rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-md">
                            <div class="mb-1 font-semibold text-center text-foreground dark:text-neutral-100 text-base">
                                Profile Photo</div>
                            <img :src="photoPreview || profilePhotoUrl" alt="Profile Photo"
                                class="w-24 h-24 rounded-full object-cover border border-gray-300 dark:border-neutral-700 shadow" />
                            <label class="flex flex-col justify-center items-center mt-2 w-full ">
                                <span class="block text-xs text-muted-foreground dark:text-neutral-400 mb-1">Upload a
                                    new photo</span>
                                <input type="file" accept="image/jpeg,image/png"
                                    class="block w-full text-sm text-gray-500 dark:text-neutral-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/80 cursor-pointer dark:file:text-black mx-auto"
                                    @input="onFileInput" />
                                <InputError class="mt-2" :message="form.errors.profile_photo" />
                                <span class="block text-xs text-muted-foreground dark:text-neutral-400 mt-1">Allowed:
                                    JPG, PNG. Max size: 2MB.</span>
                            </label>
                            <Button v-if="user.profile_photo_path !== '/storage/default-profile.png'" type="button"
                                variant="destructive" class="mt-2" @click="removePhoto">
                                Remove Photo
                            </Button>
                        </div>
                    </div>
                    <div v-if="mustVerifyEmail && !user.email_verified_at"
                        class="rounded-lg bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 p-4 flex flex-col gap-2 mt-2">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                            Your email address is unverified.
                        </p>
                        <Link :href="route('verification.send')" method="post" as="button"
                            class="inline-block w-fit text-sm font-semibold text-yellow-700 dark:text-yellow-200 underline underline-offset-4 hover:text-yellow-900 dark:hover:text-yellow-100 transition"
                            @success="() => toast.success('Verification email sent!')">
                        Click here to resend the verification email.
                        </Link>
                        <div v-if="status === 'verification-link-sent'"
                            class="text-xs font-medium text-green-600 dark:text-green-400 mt-1">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>
                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600 dark:text-neutral-300">
                                Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
