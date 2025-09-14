<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Attendance Management" />

        <div class="max-w-7xl mx-auto w-full px-4 py-6 sm:py-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
                        <Calendar class="w-8 h-8 text-blue-600" />
                        Attendance Management
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        Mark and manage student attendance
                    </p>
                </div>
            </div>

            <!-- Filters Section -->
            <div
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-6">
                <!-- Desktop Filters -->
                <div class="hidden md:grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Class Selection -->
                    <div>
                        <Label for="class_id" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Building2 class="w-4 h-4 inline mr-2" />
                            Class <span class="text-red-500">*</span>
                        </Label>
                        <select id="class_id" v-model="selectedClass"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="">Select Class</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                    </div>

                    <!-- Section Selection -->
                    <div>
                        <Label for="section_id" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Users class="w-4 h-4 inline mr-2" />
                            Section
                        </Label>
                        <select id="section_id" v-model="selectedSection"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="">All Sections</option>
                            <option v-for="section in filteredSections" :key="section.id" :value="section.id">{{
                                section.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Teacher Selection -->
                    <div>
                        <Label for="teacher_id" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Users class="w-4 h-4 inline mr-2" />
                            Teacher
                        </Label>
                        <select id="teacher_id" v-model="selectedTeacher"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="">Select Teacher</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.user?.name || 'Unknown Teacher' }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Selection -->
                    <div>
                        <Label for="date" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Calendar class="w-4 h-4 inline mr-2" />
                            Date <span class="text-red-500">*</span>
                        </Label>
                        <input type="date" id="date" v-model="selectedDate"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                    </div>

                    <!-- Load Students Button -->
                    <div class="flex items-end">
                        <Button @click="loadStudents" :disabled="!selectedClass || !selectedDate"
                            class="w-full px-3 py-2 text-sm">
                            <RefreshCw class="w-4 h-4 mr-2" />
                            Load Students
                        </Button>
                    </div>
                </div>

                <!-- Mobile Filter Button -->
                <div class="md:hidden">
                    <Button @click="openFilterSheet" variant="outline" class="w-full px-3 py-2 text-sm">
                        <Filter class="w-4 h-4 mr-2" />
                        Filters
                    </Button>
                </div>
            </div>

            <!-- Attendance Table -->
            <div v-if="students.length > 0"
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Mark Attendance for {{ selectedClassName }} - {{ formatDate(selectedDate) }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ students.length }} student{{ students.length !== 1 ? 's' : '' }} found
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Student
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Roll No
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Remarks
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                            <tr v-for="student in students" :key="student.id"
                                class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                <img v-if="student.profile_photo_url" :src="student.profile_photo_url"
                                                    alt="Profile Photo" class="h-10 w-10 rounded-full object-cover">

                                                <span v-else
                                                    class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                    {{ getInitials(student.name || 'Student') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ student.name || 'Unknown' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ student.section?.name || 'No Section' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ student.registration_number || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select v-model="attendanceData[student.id].status"
                                        class="px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option v-for="(label, value) in statuses" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="text" v-model="attendanceData[student.id].remarks"
                                        placeholder="Optional remarks"
                                        class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Save Button -->
                <div class="p-6 border-t border-gray-200 dark:border-neutral-700">
                    <Button @click="saveAttendance" :disabled="load" class="w-full sm:w-auto px-4 py-2 text-sm">
                        <Loader2 v-if="load" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        {{ load ? 'Saving...' : 'Save Attendance' }}
                    </Button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="selectedClass && selectedDate" class="text-center py-12">
                <div
                    class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <Users class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No students found</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    No students are enrolled in the selected class and section.
                </p>
            </div>

            <!-- Instructions -->
            <div v-else class="text-center py-12">
                <div
                    class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <Calendar class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Select Class and Date</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Choose a class and date to start marking attendance.
                </p>
            </div>
        </div>

        <!-- Touch-friendly Bottom Sheet for Mobile Filters -->
        <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
            ref="filterSheet" class="dark:bg-neutral-900">
            <div class="sheet-content dark:bg-neutral-900">
                <h2 class="text-lg font-semibold mb-4">Attendance Filters</h2>
                <div class="flex flex-col gap-4">
                    <!-- Class Selection -->
                    <div class="flex flex-col">
                        <label for="class_id_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Class <span class="text-red-500">*</span>
                        </label>
                        <select id="class_id_mobile" v-model="selectedClass"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900">
                            <option value="">Select Class</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                    </div>

                    <!-- Section Selection -->
                    <div class="flex flex-col">
                        <label for="section_id_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Section
                        </label>
                        <select id="section_id_mobile" v-model="selectedSection"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900">
                            <option value="">All Sections</option>
                            <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Teacher Selection -->
                    <div class="flex flex-col">
                        <label for="teacher_id_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Teacher
                        </label>
                        <select id="teacher_id_mobile" v-model="selectedTeacher"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900">
                            <option value="">Select Teacher</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.user?.name || 'Unknown Teacher' }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Selection -->
                    <div class="flex flex-col">
                        <label for="date_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date_mobile" v-model="selectedDate"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900" />
                    </div>

                    <!-- Load Students Button -->
                    <div class="pt-2">
                        <Button @click="loadStudentsAndClose" :disabled="!selectedClass || !selectedDate"
                            class="w-full px-3 py-2 text-sm">
                            <RefreshCw class="w-4 h-4 mr-2" />
                            Load Students
                        </Button>
                    </div>
                </div>
            </div>
        </vue-bottom-sheet>
    </AppLayout>
</template>

<script setup lang="ts">
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Calendar, Building2, Users, RefreshCw, Save, Loader2, Filter } from 'lucide-vue-next';
import { useSections } from '@/composables/useSections';



interface Student {
    id: number;
    name: string;
    profile_photo_url?: string;
    registration_number?: string;
    class?: {
        name: string;
    };
    section?: {
        name: string;
    };
}

interface Class {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
    class_id: number;

}

interface Teacher {
    id: number;
    user_id: number;
    user?: {
        name: string;
    };
}

interface Props {
    classes: Class[];
    sections: Section[];
    teachers: Teacher[];
    students: Student[];
    attendances: Record<number, any>;
    selectedClass?: string;
    selectedSection?: string;
    selectedTeacher?: string;
    selectedDate: string;
    statuses: Record<string, string>;
}

const props = defineProps<Props>();

const filterSheet = ref<InstanceType<typeof VueBottomSheet>>();


function openFilterSheet() {
    filterSheet.value?.open();
}

function closeFilterSheet() {
    filterSheet.value?.close();
}

const breadcrumbItems = [
    { title: 'Dashboard', href: '/' },
    { title: 'Attendance', href: '/attendance' }
];

const selectedClass = ref(props.selectedClass || '');
const selectedSection = ref(props.selectedSection || '');
const selectedTeacher = ref(props.selectedTeacher || '');
const selectedDate = ref(props.selectedDate);
const students = ref<Student[]>(props.students || []);
const load = ref(false);


const { sections: filteredSections, fetchSectionsByClass, loading } = useSections();


watch(selectedClass, (newClassId) => {
    selectedSection.value = '';
    fetchSectionsByClass(newClassId);
});

// Initialize attendance data for each student
const attendanceData = ref<Record<number, { status: string; remarks: string }>>({});

// Watch for changes in students and initialize attendance data
watch(() => props.students, (newStudents) => {
    students.value = newStudents || [];
    initializeAttendanceData();
}, { immediate: true });

// Watch for changes in attendances and update the form
watch(() => props.attendances, (newAttendances) => {
    if (newAttendances) {
        Object.entries(newAttendances).forEach(([studentId, attendance]) => {
            const studentIdNum = parseInt(studentId);
            if (attendanceData.value[studentIdNum]) {
                attendanceData.value[studentIdNum].status = attendance.status;
                attendanceData.value[studentIdNum].remarks = attendance.remarks || '';
            }
        });
    }
}, { immediate: true });

function initializeAttendanceData() {
    attendanceData.value = {};
    students.value.forEach(student => {
        attendanceData.value[student.id] = {
            status: 'present',
            remarks: ''
        };
    });
}

const selectedClassName = computed(() => {
    const cls = props.classes.find(c => c.id.toString() === selectedClass.value.toString());
    return cls ? cls.name : '';
});

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function loadStudents() {
    if (!selectedClass.value || !selectedDate.value) {
        toast.error('Please select both class and date');
        return;
    }

    router.visit(route('attendance.index'), {
        data: {
            class_id: selectedClass.value,
            section_id: selectedSection.value,
            teacher_id: selectedTeacher.value,
            date: selectedDate.value
        },
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}

function loadStudentsAndClose() {
    loadStudents();
    closeFilterSheet();
}

function saveAttendance() {
    if (!selectedClass.value || !selectedDate.value) {
        toast.error('Please select both class and date');
        return;
    }

    if (students.value.length === 0) {
        toast.error('No students to mark attendance for');
        return;
    }

    load.value = true;

    const form = useForm({
        class_id: selectedClass.value,
        section_id: selectedSection.value,
        teacher_id: selectedTeacher.value,
        date: selectedDate.value,
        attendance_data: Object.entries(attendanceData.value).map(([studentId, data]) => {
            const studentIdNum = parseInt(studentId);
            return {
                student_id: studentIdNum,
                status: data.status,
                remarks: data.remarks
            };
        })
    });

    form.post(route('attendance.store'), {
        onSuccess: () => {
            toast.success('Attendance marked successfully!');
            load.value = false;
        },
        onError: (errors) => {
            toast.error('Failed to mark attendance. Please try again.');
            load.value = false;
        }
    });
}
</script>