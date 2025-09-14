<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :class="className" v-bind="$attrs">
        <path fill="currentColor" d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09v6.91L12 23 1 15.91V9l11-6z" />
        <path fill="currentColor" d="M12 5.5L6 9v4.5l6 3.5 6-3.5V9l-6-3.5z" />
        <path fill="currentColor" d="M12 12.5L8 10.5v2l4 2.5 4-2.5v-2l-4 2z" />
    </svg>
</template>
