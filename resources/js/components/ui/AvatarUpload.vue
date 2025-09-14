<template>
    <div class="flex flex-col items-center">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}<span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative flex flex-col items-center justify-center rounded-full border-2 border-dashed border-gray-300 dark:border-neutral-700 cursor-pointer bg-gray-50 dark:bg-neutral-800 hover:border-purple-400 dark:hover:border-purple-500 transition overflow-hidden"
            :style="{ width: width + 'px', height: height + 'px' }" @click="triggerInput"
            @dragover.prevent="dragActive = true" @dragleave.prevent="dragActive = false" @drop.prevent="onDrop"
            :class="dragActive ? 'border-purple-400 dark:border-purple-500' : ''">
            <img v-if="previewUrl" :src="previewUrl" :alt="label || 'Avatar Preview'"
                :style="{ width: width + 'px', height: height + 'px' }" class="object-cover rounded-full" />
            <span v-else class="flex items-center justify-center w-full h-full text-gray-300 dark:text-neutral-600">
                <Icon name="User" :class="`w-[${width / 2}px] h-[${height / 2}px]`" />
            </span>
            <input ref="fileInput" type="file" :accept="accept" class="hidden" @change="onFileChange" />
        </div>
        <button type="button" @click="triggerInput"
            class="mt-2 px-4 py-1 rounded bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 focus:outline-none">Choose</button>
        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ hint || 'PNG, JPG up to 1MB' }}</span>
        <div v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import Icon from '@/components/Icon.vue';

const props = defineProps({
    modelValue: File,
    label: String,
    hint: String,
    required: Boolean,
    accept: { type: String, default: 'image/*' },
    maxSizeMB: { type: Number, default: 1 },
    preview: String,
    width: { type: Number, default: 96 },
    height: { type: Number, default: 96 },
});
const emit = defineEmits(['update:modelValue', 'update:preview']);

const fileInput = ref<HTMLInputElement | null>(null);
const dragActive = ref(false);
const error = ref('');
const previewUrl = ref(props.preview || '');

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