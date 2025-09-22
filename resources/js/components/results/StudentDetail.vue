<template>
    <div class="bg-white dark:bg-neutral-900 w-full rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
            {{ student.student.name }} — Academic Year: {{ student.grouped_terms.year_name }}
        </h2>

        <!-- Terms Loop -->
        <div v-for="(term, code) in student.grouped_terms.terms" :key="code" class="mb-8 border-t pt-4">
            <h3 v-if="term.items.length" class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                Term: {{ term.term_name }}
            </h3>

            <div v-if="term.items.length">
                <div class="overflow-x-auto rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 border">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Subject</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Obtained</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Total</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    %</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Status</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                            <tr v-for="item in term.items" :key="item.subject_id"
                                class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.subject_name }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.obtained_marks }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.total_marks }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.percentage }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.status }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.remarks }}</td>
                            </tr>
                            <tr v-if="term.term_result">
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium bg-gray-50 text-gray-500 dark:text-gray-400 uppercase">
                                    Total Marks</th>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                    term.term_result.obtained_marks }} / {{ term.term_result.total_marks }}
                                </td>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium bg-gray-50 text-gray-500 dark:text-gray-400 uppercase">
                                    Percentage</th>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                    term.term_result.overall_percentage }}%
                                </td>
                            </tr>
                            <tr v-if="term.term_result">
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium bg-gray-50 text-gray-500 dark:text-gray-400 uppercase">
                                    Grade</th>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                    term.term_result.grade }}
                                </td>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium bg-gray-50 text-gray-500 dark:text-gray-400 uppercase">
                                    Remarks</th>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                    term.term_result.remarks }}
                                </td>
                            </tr>
                            <div v-else
                                class="mt-4 flex flex-row gap-2 justify-center items-center border border-amber-500 bg-amber-100 rounded-sm py-2 text-yellow-700 dark:text-yellow-300">
                                <Info class="w-5 h-5 text-yellow-700 dark:text-yellow-300" />
                                <p>
                                    Term result has not been calculated yet.
                                </p>
                            </div>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Academic Result Summary -->
        <div v-if="student.grouped_terms.all_terms_completed && student.grouped_terms.academic_result"
            class="mt-6 border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Academic Year Summary</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Overall Percentage:</strong> {{
                student.grouped_terms.academic_result.overall_percentage }}%</p>
            <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Final Grade:</strong> {{
                student.grouped_terms.academic_result.final_grade }}</p>
            <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Promotion Status:</strong> {{
                student.grouped_terms.academic_result.promotion_status }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Info } from 'lucide-vue-next';

const props = defineProps<{
    student: any;
    terms: Record<string, string>;
}>();
</script>
