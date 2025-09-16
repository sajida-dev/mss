<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    upcomingExams?: unknown[]; // Make this optional with default fallback
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    upcomingExams: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :upcomingExams="upcomingExams">
        <slot />
    </AppLayout>
</template>
