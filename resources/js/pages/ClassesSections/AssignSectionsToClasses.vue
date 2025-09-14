<template>


    <Head title="Assign Sections to Classes" />

    <div class="max-w-7xl mx-auto w-full px-4 py-6 sm:py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1
                    class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
                    <Users class="w-8 h-8 text-green-600" />
                    Assign Sections to Classes
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    Manage which sections are available in each class
                </p>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <div
                    class="w-6 h-6 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <Info class="w-4 h-4 text-blue-600" />
                </div>
                <div>
                    <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-2">How to use this page:</h3>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span><strong>Select a school</strong> from the school switcher at the top of the
                                page</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Tap on a <strong>class name</strong> to expand and see available sections</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Check or uncheck sections to assign or remove them from the class</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Tap <strong>Save Assignments</strong> to save your changes</span>
                        </li>
                    </ul>
                    <div class="mt-3 p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                        <p class="text-xs text-blue-700 dark:text-blue-200">
                            <strong>Note:</strong> All changes only affect the currently selected school. You cannot
                            accidentally change classes or sections for other schools.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <div
                class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                <Loader2 class="w-8 h-8 text-gray-400 animate-spin" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Loading Classes...</h3>
            <p class="text-gray-600 dark:text-gray-400">Please wait while we load the class data</p>
        </div>

        <!-- Classes List -->
        <div v-else class="space-y-4">
            <div v-for="cls in classes" :key="cls.id"
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden">
                <!-- Class Header -->
                <button
                    class="w-full flex justify-between items-center px-4 py-4 bg-gray-50 dark:bg-neutral-800 hover:bg-gray-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all"
                    @click="toggleAccordion(cls.id)">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <BookOpen class="w-5 h-5 text-green-600" />
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ cls.name
                            }}</span>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ selectedSections[cls.id]?.length || 0 }} sections assigned
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ isAccordionOpen(cls.id) ? 'Hide' : 'Show' }}
                        </span>
                        <ChevronDown class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isAccordionOpen(cls.id) }" />
                    </div>
                </button>

                <!-- Class Content -->
                <div v-show="isAccordionOpen(cls.id)"
                    class="p-4 bg-white dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            Select sections to assign to <strong>{{ cls.name }}</strong>:
                        </p>

                        <!-- Sections Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-3 mb-4">
                            <label v-for="section in sections" :key="section.id"
                                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-neutral-700 transition-colors">
                                <input type="checkbox" :value="section.id" v-model="selectedSections[cls.id]"
                                    class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                                <div class="flex items-center gap-2">
                                    <Users class="w-4 h-4 text-gray-500" />
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ section.name
                                    }}</span>
                                </div>
                            </label>
                        </div>

                        <!-- Save Button -->
                        <Button @click="saveAssignments(cls.id)" :disabled="saving[cls.id]"
                            class="w-full px-6 py-3 text-base font-medium bg-green-600 hover:bg-green-700 disabled:opacity-50">
                            <Loader2 v-if="saving[cls.id]" class="w-5 h-5 mr-3 animate-spin" />
                            <CheckCircle v-else class="w-5 h-5 mr-3" />
                            {{ saving[cls.id] ? 'Saving...' : 'Save Assignments' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="classes.length === 0" class="text-center py-12">
                <div
                    class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <BookOpen class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No classes found</h3>
                <p class="text-gray-600 dark:text-gray-400">There are no classes available in the selected school
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import { toast } from 'vue3-toastify';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { BookOpen, Users, ChevronDown, Info, Loader2, CheckCircle } from 'lucide-vue-next';

interface ClassItem {
    id: number;
    name: string;
    sections: { id: number; name: string }[];
}
interface SectionItem {
    id: number;
    name: string;
}

const classes = ref<ClassItem[]>([]);
const sections = ref<SectionItem[]>([]);
const loading = ref(true);
const saving = reactive<{ [key: number]: boolean }>({});
const openAccordions = ref<number[]>([]);
const selectedSections = reactive<{ [key: number]: number[] }>({});

const schoolStore = useSchoolStore();
const { selectedSchool } = storeToRefs(schoolStore);


function isAccordionOpen(classId: number) {
    return openAccordions.value.includes(classId);
}

function toggleAccordion(classId: number) {
    if (isAccordionOpen(classId)) {
        openAccordions.value = openAccordions.value.filter(id => id !== classId);
    } else {
        openAccordions.value.push(classId);
    }
}

async function fetchData() {
    if (!selectedSchool.value?.id) {
        classes.value = [];
        sections.value = [];
        return;
    }
    loading.value = true;
    try {
        const res = await fetch(`/section-assignment?school_id=${selectedSchool.value.id}`);
        const data = await res.json();
        classes.value = data.classes;
        sections.value = data.sections;
        // Initialize selectedSections for each class
        for (const cls of data.classes) {
            selectedSections[cls.id] = (cls.sections || []).map((section: { id: number }) => section.id);
        }
    } catch (e) {
        toast.error('Failed to load data.');
    } finally {
        loading.value = false;
    }
}

function getCsrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function saveAssignments(classId: number) {
    if (!selectedSchool.value || !selectedSchool.value.id) {
        toast.error('No school selected.');
        return;
    }
    saving[classId] = true;
    try {
        router.post(`/section-assignment/${classId}/assign`, {
            section_ids: selectedSections[classId],
            school_id: selectedSchool.value.id,
        }, {
            onSuccess: () => {
                toast.success('Assignments saved successfully!');
            },
            onError: () => {
                toast.error('Failed to save assignments.');
            },
            onFinish: () => {
                saving[classId] = false;
            }
        });
    } catch (e) {
        toast.error('An error occurred.');
        saving[classId] = false;
    }
}

onMounted(fetchData);
watch(() => selectedSchool.value?.id, fetchData);
</script>

<style scoped>
.bg-primary {
    background-color: #2563eb;
}

.bg-primary-dark {
    background-color: #1e40af;
}
</style>