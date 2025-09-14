<template>
    <AppLayout :breadcrumbs="[{ title: 'Manage Classes & Sections', href: '/manage/classes-sections' }]">

        <Head title="Manage Classes & Sections" />
        <div class="max-w-5xl mx-auto w-full px-4 py-6 sm:py-8">
            <h1
                class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-6 flex items-center gap-3">
                <Building2 class="w-8 h-8 text-blue-600" />
                Manage Classes & Sections
            </h1>

            <!-- Mobile-First Responsive Tabs -->
            <div class="mb-6">
                <!-- Mobile Tabs (Primary Design) -->
                <div class="md:hidden">
                    <div
                        class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Classes Tab -->
                            <button v-can="'read-classes'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'classes'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'classes'">
                                <Building2 class="w-6 h-6" />
                                <span>Classes</span>
                            </button>

                            <!-- Sections Tab -->
                            <button v-can="'read-sections'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'sections'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'sections'">
                                <Users class="w-6 h-6" />
                                <span>Sections</span>
                            </button>

                            <!-- Assign Classes Tab -->
                            <button v-can="'assign-classes-to-schools'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'assign-classes'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'assign-classes'">
                                <Link class="w-6 h-6" />
                                <span>Assign Classes</span>
                            </button>

                            <!-- Assign Sections Tab -->
                            <button v-can="'assign-sections-to-classes'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'assign-sections'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'assign-sections'">
                                <Link class="w-6 h-6" />
                                <span>Assign Sections</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Tabs (Secondary Design) -->
                <div
                    class="hidden md:flex gap-1 border border-gray-200 dark:border-neutral-700 rounded-lg p-1 bg-gray-50 dark:bg-neutral-800">
                    <button v-can="'read-classes'"
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'classes' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'classes'">
                        <Building2 class="w-4 h-4" />
                        Classes
                    </button>
                    <button v-can="'read-sections'"
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'sections' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'sections'">
                        <Users class="w-4 h-4" />
                        Sections
                    </button>
                    <button v-can="'assign-classes-to-schools'"
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'assign-classes' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'assign-classes'">
                        <Link class="w-4 h-4" />
                        Assign Classes to Schools
                    </button>
                    <button v-can="'assign-sections-to-classes'"
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'assign-sections' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'assign-sections'">
                        <Link class="w-4 h-4" />
                        Assign Sections to Classes
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div v-if="activeTab === 'classes'">
                <ClassesIndex :classes="classes" />
            </div>
            <div v-else-if="activeTab === 'sections'">
                <SectionsIndex :sections="sections" />
            </div>
            <div v-else-if="activeTab === 'assign-classes'">
                <AssignClassesToSchools />
            </div>
            <div v-else-if="activeTab === 'assign-sections'">
                <AssignSectionsToClasses />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClassesIndex from '@/pages/Classes/Index.vue';
import SectionsIndex from '@/pages/Sections/Index.vue';
import AssignClassesToSchools from './AssignClassesToSchools.vue';
import AssignSectionsToClasses from './AssignSectionsToClasses.vue';
import { Building2, Users, Link } from 'lucide-vue-next';

const page = usePage();
const flash = (page.props as any).flash || {};
const initialTab = flash.initialTab || 'classes';
const activeTab = ref(initialTab);
const classes = computed(() => page.props.classes as { id: number; name: string }[]);
const sections = computed(() => page.props.sections as { id: number; name: string }[]);
</script>