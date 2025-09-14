<template>


    <Head title="Assign Classes to Schools" />

    <div class="max-w-7xl mx-auto w-full px-4 py-6 sm:py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1
                    class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
                    <Building2 class="w-8 h-8 text-blue-600" />
                    Assign Classes to Schools
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    Manage which classes are available in each school
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
                            <span>Tap on a <strong>school name</strong> to expand and see available classes</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Check or uncheck classes to assign or remove them from the school</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Tap <strong>Save Assignments</strong> to save your changes</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <div
                class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                <Loader2 class="w-8 h-8 text-gray-400 animate-spin" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Loading Schools...</h3>
            <p class="text-gray-600 dark:text-gray-400">Please wait while we load the school data</p>
        </div>

        <!-- Schools List -->
        <div v-else class="space-y-4">
            <div v-for="school in schools" :key="school.id"
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden">
                <!-- School Header -->
                <button
                    class="w-full flex justify-between items-center px-4 py-4 bg-gray-50 dark:bg-neutral-800 hover:bg-gray-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    @click="toggleAccordion(school.id)">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <Avatar class="h-10 w-10">
                                <AvatarImage
                                    :src="school?.logo_url ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s'"
                                    :alt="school?.name ?? 'No School Selected'" />
                            </Avatar>
                            <!-- <Building2 class="w-5 h-5 text-blue-600" /> -->
                        </div>
                        <div class="text-left">
                            <span class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ school.name
                            }}</span>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ selectedClasses[school.id]?.length || 0 }} classes assigned
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ isAccordionOpen(school.id) ? 'Hide' : 'Show' }}
                        </span>
                        <ChevronDown class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isAccordionOpen(school.id) }" />
                    </div>
                </button>

                <!-- School Content -->
                <div v-show="isAccordionOpen(school.id)"
                    class="p-4 bg-white dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            Select classes to assign to <strong>{{ school.name }}</strong>:
                        </p>

                        <!-- Classes Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                            <label v-for="cls in classes" :key="cls.id"
                                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-neutral-700 transition-colors">
                                <input type="checkbox" :value="cls.id" v-model="selectedClasses[school.id]"
                                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                                <div class="flex items-center gap-2">
                                    <BookOpen class="w-4 h-4 text-gray-500" />
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ cls.name }}</span>
                                </div>
                            </label>
                        </div>

                        <!-- Save Button -->
                        <Button @click="saveAssignments(school.id)" :disabled="saving[school.id]"
                            class="w-full px-6 py-3 text-base font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
                            <Loader2 v-if="saving[school.id]" class="w-5 h-5 mr-3 animate-spin" />
                            <CheckCircle v-else class="w-5 h-5 mr-3" />
                            {{ saving[school.id] ? 'Saving...' : 'Save Assignments' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="schools.length === 0" class="text-center py-12">
                <div
                    class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <Building2 class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No schools found</h3>
                <p class="text-gray-600 dark:text-gray-400">There are no schools available to assign classes to</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import { useSchoolStore } from '@/stores/school';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Building2, BookOpen, ChevronDown, Info, Loader2, CheckCircle } from 'lucide-vue-next';
import { Avatar, AvatarImage } from '@/components/ui/avatar';


interface School {
    id: number;
    name: string;
    logo_url?: string;
    classes: { id: number; name: string }[];
}
interface ClassItem {
    id: number;
    name: string;
}

const schools = ref<School[]>([]);
const classes = ref<ClassItem[]>([]);
const loading = ref(true);
const saving = reactive<{ [key: number]: boolean }>({});
const openAccordions = ref<number[]>([]);
const selectedClasses = reactive<{ [key: number]: number[] }>({});
const schoolStore = useSchoolStore();


function isAccordionOpen(schoolId: number) {
    return openAccordions.value.includes(schoolId);
}

function toggleAccordion(schoolId: number) {
    if (isAccordionOpen(schoolId)) {
        openAccordions.value = openAccordions.value.filter(id => id !== schoolId);
    } else {
        openAccordions.value.push(schoolId);
    }
}

async function fetchData() {
    loading.value = true;
    try {
        const res = await fetch('/class-assignment');
        const data = await res.json();
        schools.value = data.schools;
        classes.value = data.classes;
        // Initialize selectedClasses for each school
        for (const school of data.schools) {
            selectedClasses[school.id] = (school.classes || []).map((cls: { id: number }) => cls.id);
        }
    } catch (e) {
        toast.error('Failed to load data.');
    } finally {
        loading.value = false;
    }
}

async function saveAssignments(schoolId: number) {
    saving[schoolId] = true;
    try {
        router.post(`/class-assignment/${schoolId}/assign`, {
            class_ids: selectedClasses[schoolId],
        }, {
            onSuccess: async () => {
                toast.success('Assignments saved successfully!');
                await schoolStore.fetchSchools(); // Refresh global state after assignment
            },
            onError: () => {
                toast.error('Failed to save assignments.');
            },
            onFinish: () => {
                saving[schoolId] = false;
            }
        });
    } catch (e) {
        toast.error('An error occurred.');
        saving[schoolId] = false;
    }
}

onMounted(fetchData);
</script>

<style scoped>
.bg-primary {
    background-color: #2563eb;
}

.bg-primary-dark {
    background-color: #1e40af;
}
</style>