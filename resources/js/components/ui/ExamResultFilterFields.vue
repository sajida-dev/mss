<template>
    <div class="grid grid-cols-1 lg:grid-cols-6 gap-1">

        <!-- Academic Year -->
        <div>
            <Label>Academic Year</Label>
            <select :value="selectedAcademicYear"
                @change="emit('update:selectedAcademicYear', ($event.target as HTMLSelectElement).value)"
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm mt-1 bg-white dark:bg-neutral-900">
                <option value="">Select Academic Year</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.name }}</option>
            </select>
        </div>

        <!-- Class -->
        <div>
            <Label>Class</Label>
            <select :value="selectedClass"
                @change="emit('update:selectedClass', ($event.target as HTMLSelectElement).value)"
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm  mt-1 bg-white dark:bg-neutral-900">
                <option value="">Select Class</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
            </select>
        </div>

        <!-- Section -->
        <div class="w-full">
            <Label>Section</Label>
            <select :value="selectedSection" :disabled="!selectedClass"
                @change="emit('update:selectedSection', ($event.target as HTMLSelectElement).value)"
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm mt-1 bg-white dark:bg-neutral-900">
                <option value="">All Sections</option>
                <option v-for="sec in sections" :key="sec.id" :value="sec.id">{{ sec.name }}</option>
            </select>
        </div>

        <!-- Term -->
        <div>
            <Label>Term</Label>
            <select :value="selectedTerm"
                @change="emit('update:selectedTerm', ($event.target as HTMLSelectElement).value)"
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm mt-1 bg-white dark:bg-neutral-900">
                <option value="">Select Term</option>
                <option v-for="(label, value) in terms" :key="value" :value="value">{{ label }}</option>
            </select>
        </div>
        <!-- Exam -->
        <div class="lg:col-span-2">
            <Label>Exam</Label>
            <select :value="selectedExam"
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm mt-1 bg-white dark:bg-neutral-900"
                @change="emit('update:selectedExam', ($event.target as HTMLSelectElement).value)">
                <option value="">All Exams</option>
                <option v-for="exam in exams" :key="exam.id" :value="exam.id">{{ exam.title }}</option>
            </select>
        </div>
    </div>
</template>

<script setup lang="ts">
import Label from './label/Label.vue'

const props = defineProps<{
    selectedClass: any
    selectedSection: any
    selectedTerm: any
    selectedExam: any
    selectedAcademicYear: any
    classes: any[]
    sections: any[]
    terms: Record<string, string>
    academicYears: any[]
    exams: any[]
}>()

const emit = defineEmits<{
    (e: 'update:selectedClass', value: any): void
    (e: 'update:selectedSection', value: any): void
    (e: 'update:selectedTerm', value: any): void
    (e: 'update:selectedExam', value: any): void
    (e: 'update:selectedAcademicYear', value: any): void
}>()
</script>
