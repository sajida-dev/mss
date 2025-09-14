<!-- Manage.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Manage Exams" />

        <div class="max-w-5xl mx-auto w-full px-4 py-6 sm:py-8">
            <h1
                class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-6 flex items-center gap-3">
                <FileText class="w-8 h-8 text-blue-600" />
                Manage Exams
            </h1>

            <!-- Tabs -->
            <div class="mb-6">
                <!-- Mobile Tabs -->
                <div class="md:hidden">
                    <div
                        class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="grid grid-cols-3 gap-2">
                            <button v-can="'read-exam-types'" :class="tabClass('exam-types')"
                                @click="goTo('exam-types.index')">
                                <FileText class="w-6 h-6" />
                                <span>Exam Types</span>
                            </button>
                            <button v-can="'read-exams'" :class="tabClass('exams')" @click="goTo('exams.index')">
                                <ClipboardList class="w-6 h-6" />
                                <span>Exams</span>
                            </button>
                            <button v-can="'read-exam-papers'" :class="tabClass('exam-papers')"
                                @click="goTo('exam-papers.index')">
                                <File class="w-6 h-6" />
                                <span>Exam Papers</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Tabs -->
                <div
                    class="hidden md:flex gap-1 border border-gray-200 dark:border-neutral-700 rounded-lg p-1 bg-gray-50 dark:bg-neutral-800">
                    <button v-can="'read-exam-types'" :class="desktopTabClass('exam-types')"
                        @click="goTo('exam-types.index')">
                        <FileText class="w-4 h-4" /> Exam Types
                    </button>
                    <button v-can="'read-exams'" :class="desktopTabClass('exams')" @click="goTo('exams.index')">
                        <ClipboardList class="w-4 h-4" /> Exams
                    </button>
                    <button v-can="'read-exam-papers'" :class="desktopTabClass('exam-papers')"
                        @click="goTo('exam-papers.index')">
                        <File class="w-4 h-4" /> Exam Papers
                    </button>
                </div>
            </div>

            <!-- Loading Spinner (optional) -->
            <div v-if="page.props.loading" class="text-center py-12">
                <Loader2 class="w-8 h-8 animate-spin text-gray-400 mx-auto" />
                <p class="text-sm text-gray-500 mt-2">Loading...</p>
            </div>

            <!-- Slot: the actual tab content -->
            <div v-else>
                <slot />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { FileText, ClipboardList, File, Loader2 } from 'lucide-vue-next'

const page = usePage()

const routeName = computed(() => route().current())
const activeTab = computed(() => {
    if (routeName.value?.startsWith('exam-types')) return 'exam-types'
    if (routeName.value?.startsWith('exams')) return 'exams'
    if (routeName.value?.startsWith('exam-papers')) return 'exam-papers'
    return ''
})

function goTo(tabRoute: string) {
    if (!route().current(tabRoute)) {
        router.visit(route(tabRoute), {
            preserveScroll: true,
            preserveState: true,
        })
    }
}

function tabClass(tab: string) {
    return [
        'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 text-sm font-medium',
        activeTab.value === tab
            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
            : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300',
    ]
}

function desktopTabClass(tab: string) {
    return [
        'flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all',
        activeTab.value === tab
            ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm'
            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100',
    ]
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Exams', href: '/exams' },
]
</script>
