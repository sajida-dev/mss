<template>
    <template>
        <tr
            :class="['border-b border-gray-200 dark:border-neutral-800 transition', { 'bg-gray-50 dark:bg-neutral-800': expanded }]">
            <!-- Name/Profile -->
            <td class="py-3 px-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="h-10 w-10 rounded-full bg-gray-200 dark:bg-neutral-700 flex items-center justify-center text-base font-medium text-gray-700 dark:text-gray-300 overflow-hidden">
                        <img v-if="image" :src="image" alt="Profile" class="object-cover w-full h-full" />
                        <span v-else>{{ initial }}</span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ name }}</span>
                </div>
            </td>
            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ regNo }}</td>
            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ className }}</td>
            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ contactNo }}</td>
            <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                    <button @click="$emit('view')" title="View"><span class="icon-eye" /></button>
                    <button @click="$emit('edit')" title="Edit"><span class="icon-pencil" /></button>
                    <button @click="$emit('delete')" title="Delete"><span class="icon-trash" /></button>
                    <button @click="expanded = !expanded" title="Show more">
                        <span v-if="!expanded" class="icon-plus" />
                        <span v-else class="icon-minus" />
                    </button>
                </div>
            </td>
        </tr>
        <tr v-if="expanded">
            <td :colspan="5" class="p-0 bg-gray-50 dark:bg-neutral-900">
                <Collapsible :open="expanded">
                    <CollapsibleContent>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                            <div v-for="field in moreFields" :key="field.label" class="flex text-sm">
                                <span class="w-32 text-gray-500 dark:text-gray-400">{{ field.label }}:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ field.value }}</span>
                            </div>
                        </div>
                    </CollapsibleContent>
                </Collapsible>
            </td>
        </tr>
    </template>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Collapsible from '@/components/ui/collapsible/Collapsible.vue';
import CollapsibleContent from '@/components/ui/collapsible/CollapsibleContent.vue';
const props = defineProps<{
    name: string;
    regNo: string;
    className: string;
    contactNo: string;
    image?: string | null;
    initial?: string;
    moreFields: { label: string; value: string | number }[];
}>();
defineEmits(['edit', 'delete', 'view']);
const expanded = ref(false);
</script>