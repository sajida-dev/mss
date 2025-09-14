<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import AppHeader from './AppHeader.vue';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex flex-row justify-between sticky top-0 z-50  bg-purple-900  h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <div class="hidden lg:block">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
            </template>
            <!-- Only show logo in header on mobile -->
            <Link :href="route('dashboard')" class="flex items-center gap-x-2 lg:hidden">
            <AppLogo />
            </Link>
        </div>
        <AppHeader :breadcrumbs="breadcrumbs" />
    </header>
</template>
