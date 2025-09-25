<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head :title="title" />

        <div class="max-w-6xl mx-auto w-full px-4 py-6 sm:py-8">
            <h1
                class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-6 flex items-center gap-3">
                <component :is="icon" class="w-8 h-8 text-blue-600" />
                {{ title }}
            </h1>

            <!-- Tabs -->
            <div class="mb-6">
                <!-- Desktop Tabs -->
                <div
                    class="hidden md:flex gap-1 border border-gray-200 dark:border-neutral-700 rounded-lg p-1 bg-gray-50 dark:bg-neutral-800">
                    <template v-for="tab in tabs" :key="tab.route">
                        <button v-can="tab.permission" :class="tabClass(tab.route)" @click="goTo(tab.route)">
                            <component :is="tab.icon" class="w-4 h-4" />
                            {{ tab.label }}
                        </button>
                    </template>
                </div>

                <!-- Mobile Tabs -->
                <div class="md:hidden">
                    <div
                        class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="grid grid-cols-2 gap-2">
                            <template v-for="tab in tabs" :key="tab.route">
                                <button v-can="tab.permission" :class="mobileTabClass(tab.route)"
                                    @click="goTo(tab.route)">
                                    <component :is="tab.icon" class="w-6 h-6" />
                                    <span>{{ tab.label }}</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Slot Content -->
            <slot />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

// Props
const props = defineProps<{
    title: string;
    icon: any;
    tabs: {
        label: string;
        route: string;
        permission: string;
        icon: any;
    }[];
}>();

const page = usePage();

const routeName = computed(() => route().current());
const activeTab = computed(() => {
    for (const tab of props.tabs) {
        if (routeName.value?.startsWith(tab.route)) {
            return tab.route;
        }
    }
    return '';
});

function goTo(tabRoute: string) {
    if (!route().current(tabRoute)) {
        router.visit(route(tabRoute), {
            preserveScroll: true,
            preserveState: true,
        });
    }
}

function tabClass(tab: string) {
    return [
        'flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all',
        activeTab.value === tab
            ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm'
            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100',
    ];
}

function mobileTabClass(tab: string) {
    return [
        'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 text-sm font-medium',
        activeTab.value === tab
            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
            : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300',
    ];
}

// Breadcrumbs - Can be made dynamic too
const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Reports', href: '/reports' },
];
</script>
