<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import GlobalAlertDialog from '@/components/GlobalAlertDialog.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const flash = (page.props.flash ?? {}) as { success?: string; error?: string };

watch(
    () => flash.success,
    (message) => {
        if (message) {
            toast.success(message, {
                autoClose: 4000,
                position: 'top-right',
                theme: 'colored',
            });
        }
    },
    { immediate: true }
);

watch(
    () => flash.error,
    (message) => {
        if (message) {
            toast.error(message, {
                autoClose: 4000,
                position: 'top-right',
                theme: 'colored',
            });
        }
    },
    { immediate: true }
);
</script>

<template>
    <AppShell variant="sidebar">
        <div class="flex min-h-screen h-full w-full">
            <AppSidebar />
            <div class="flex-1 flex flex-col min-w-0">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <AppContent variant="sidebar" class="flex flex-col overflow-x-hidden">
                    <!-- Add proper spacing for fixed school switcher -->
                    <div class="mt-14 md:mt-3 lg:mt-3">
                        <slot />
                    </div>
                </AppContent>
            </div>
        </div>
        <GlobalAlertDialog />
    </AppShell>
</template>