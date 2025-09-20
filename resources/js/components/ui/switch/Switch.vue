<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue'
import { Check } from 'lucide-vue-next'

const props = defineProps({
    checked: Boolean,
    disabled: Boolean,
    class: String
})
const emit = defineEmits(['update:checked'])

const isChecked = ref(props.checked)

watch(() => props.checked, (val) => {
    isChecked.value = val
})

const toggle = () => {
    if (props.disabled) return
    isChecked.value = !isChecked.value
    emit('update:checked', isChecked.value)
}
</script>

<template>
    <button type="button" :class="[
        'relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 ',
        isChecked ? 'bg-[#7c3aed]' : 'bg-gray-200 dark:bg-neutral-700',
        props.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
        props.class
    ]" :aria-checked="isChecked" :disabled="props.disabled" role="switch" @click="toggle">
        <span :class="[
            ' h-6 w-6 rounded-full  transition-all duration-200 flex items-center justify-center',
            isChecked ? 'translate-x-5 bg-white dark:bg-neutral-100 text-[#7c3aed]' : 'translate-x-1 bg-white dark:bg-neutral-100',
        ]">
            <Check v-if="isChecked" class="w-4 h-4" />
        </span>
    </button>
</template>

<style scoped></style>