<template>
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-y-auto">
        <div class="p-4 border-b border-gray-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold dark:text-gray-100">Students</h2>
        </div>
        <ul>
            <li v-for="res in results" :key="res.student.id" @click="$emit('select', res)" :class="[
                'cursor-pointer px-4 py-2 border-b transition',
                selectedStudent?.student.id === res.student.id
                    ? 'bg-gray-100 dark:bg-blue-900/20'
                    : 'hover:bg-gray-50 dark:hover:bg-neutral-800'
            ]">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-md bold font-medium dark:text-gray-100">
                            {{ res.student.name }}
                        </p>
                        <p class="text-xs dark:text-gray-400">
                            Reg#: {{ res.student.registration_number }}
                        </p>
                    </div>
                    <div
                        class="text-xs bg-blue-200 dark:bg-blue-400 border border-blue-500 px-2 py-0.5 rounded-2xl dark:text-gray-200">
                        {{ res.student.class_name }}
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
interface Term {
    term_result?: {
        overall_percentage: number;
    } | null;
}

interface GroupedTerms {
    terms: Record<string, Term>;
}

interface StudentGrouped {
    student: {
        id: number;
        name: string;
        registration_number: string;
        class_name: string;
    };
    grouped_terms: GroupedTerms;
}

defineProps<{
    results: StudentGrouped[];
    selectedStudent?: StudentGrouped | null;
}>();

defineEmits<{
    (e: 'select', student: any): void;
}>();
</script>
