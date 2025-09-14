<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="School Details" />
        <div class="max-w-5xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <!-- Main Image as Header -->
            <div class="relative w-full mb-20">
                <img :src="mainImageSrc" alt="Main Image"
                    class="w-full h-64 object-cover rounded-t-xl border border-neutral-200 dark:border-neutral-700 shadow-md" />
                <!-- Logo, overlapping header -->
                <div class="absolute left-1/2 -bottom-18 transform -translate-x-1/2">
                    <img :src="logoSrc" alt="School Logo"
                        class="w-36 h-36 rounded-full object-cover border-4 border-white dark:border-neutral-900 shadow-lg bg-white dark:bg-neutral-900" />
                </div>
            </div>
            <div class="bg-white dark:bg-neutral-900 p-8 rounded-b-xl rounded-t-none shadow mt-16">
                <h1 class="text-2xl font-bold mb-6 text-neutral-900 dark:text-neutral-100 text-center">School Details
                </h1>
                <div class="mb-4 text-center"><span class="font-semibold">Name:</span> {{ school.name }}</div>
                <div class="mb-4 text-center"><span class="font-semibold">Address:</span> {{ school.address }}</div>
                <div class="mb-4 text-center"><span class="font-semibold">Contact:</span> {{ school.contact }}</div>
                <div class="flex justify-center mt-8">
                    <button @click="goBack" class="bg-blue-500 text-white px-4 py-2 rounded">Back</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { School } from '@/stores/school';

const page = usePage();
const school = page.props.school as School;
const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
    { title: 'School Details', href: `/schools/${school.id}` },
];
const goBack = () => {
    router.visit(route('schools.index'));
};

const logoSrc = computed(() => school.logo_url || 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s');
const mainImageSrc = computed(() => school.main_image_url || 'https://www.shutterstock.com/image-vector/school-building-front-view-entrance-260nw-2494026401.jpg');
</script>