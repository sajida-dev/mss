<template>
    <div class="flex flex-col w-full">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}<span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="flex flex-col items-center justify-center w-full border-2 border-dashed border-gray-300 dark:border-neutral-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-neutral-800 hover:bg-gray-100 dark:hover:bg-neutral-700 transition"
            :class="[dragActive ? 'border-purple-400 dark:border-purple-500' : '', sizeClass]" @click="triggerInput"
            @dragover.prevent="dragActive = true" @dragleave.prevent="dragActive = false" @drop.prevent="onDrop">
            <div class="flex flex-col items-center justify-center py-8">
                <Icon name="Upload" class="w-10 h-10 mb-3 text-gray-400 dark:text-gray-500" />
                <p class="mb-2 text-base text-gray-500 dark:text-gray-400">
                    <span class="font-semibold">Click to upload</span> or drag and drop
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ hint || 'PNG, JPG up to 2MB' }}</p>
            </div>
            <input ref="fileInput" type="file" :accept="accept" class="hidden" @change="onFileChange" />
        </div>
        <div v-if="previewUrl" class="mt-4 flex justify-center">
            <img :src="previewUrl" :alt="label || 'Image Preview'"
                :class="['object-cover border rounded-lg shadow', previewSizeClass]" />
        </div>
        <div v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import Icon from '@/components/Icon.vue';

const props = defineProps({
    modelValue: File,
    label: String,
    hint: String,
    required: Boolean,
    accept: { type: String, default: 'image/*' },
    maxSizeMB: { type: Number, default: 2 },
    preview: String,
    width: { type: [String, Number], default: 640 }, // px or tailwind class
    height: { type: [String, Number], default: 256 }, // px or tailwind class
});
const emit = defineEmits(['update:modelValue', 'update:preview']);

const fileInput = ref<HTMLInputElement | null>(null);
const dragActive = ref(false);
const error = ref('');
const previewUrl = ref(props.preview || '');

const sizeClass = computed(() => {
    // Use width/height as tailwind classes if string, else use max-w/max-h
    if (typeof props.width === 'string') return `w-full ${props.width}`;
    return 'w-full';
});
const previewSizeClass = computed(() => {
    if (typeof props.width === 'number' && typeof props.height === 'number') {
        return `max-w-[${props.width}px] h-[${props.height}px]`;
    }
    return 'w-full';
});

function triggerInput() {
    fileInput.value?.click();
}
function onFileChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (files && files[0]) {
        handleFile(files[0]);
    }
}
function onDrop(e: DragEvent) {
    dragActive.value = false;
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        handleFile(e.dataTransfer.files[0]);
    }
}
function handleFile(file: File) {
    error.value = '';
    if (!file.type.startsWith('image/')) {
        error.value = 'Please select a valid image file';
        return;
    }
    if (file.size > props.maxSizeMB * 1024 * 1024) {
        error.value = `Image size must be less than ${props.maxSizeMB}MB`;
        return;
    }
    const reader = new FileReader();
    reader.onload = (ev) => {
        previewUrl.value = ev.target?.result as string;
        emit('update:preview', previewUrl.value);
    };
    reader.readAsDataURL(file);
    emit('update:modelValue', file);
}

watch(() => props.preview, (val) => {
    if (val) previewUrl.value = val;
});
</script>