<template>
    <div class="mb-4">
        <label :for="id" class="block text-neutral-700 dark:text-neutral-200 mb-1">
            {{ label }}<span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative flex flex-col items-center justify-center border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-900 cursor-pointer transition hover:border-blue-400 min-h-[120px] text-center"
            @dragover.prevent @drop.prevent="onDrop" @click="triggerFileInput">
            <input ref="fileInput" :id="id" type="file" accept="image/png, image/jpeg" @change="onFileChange"
                :required="required" class="hidden" v-bind="$attrs" />
            <InputError :error="error" />
            <div v-if="previewUrl" class="flex flex-col items-center justify-center w-full">
                <img :src="previewUrl" alt="Preview" class="h-24 w-24 object-cover rounded-full border mb-2" />
                <span class="text-xs text-neutral-500">Preview</span>
            </div>
            <div v-else class="flex flex-col items-center justify-center w-full py-6">
                <svg class="w-8 h-8 mx-auto text-neutral-400" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12" />
                </svg>
                <span class="block font-semibold text-neutral-500 mt-2">Click to upload</span>
                <span class="block text-neutral-400 text-sm">or drag and drop</span>
                <span class="block text-neutral-400 text-xs mt-1">PNG, JPG up to 2MB</span>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, watch } from 'vue';
import InputError from '../InputError.vue';
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
    label: String,
    modelValue: [File, null],
    required: Boolean,
    previewUrl: String,
    error: String,
    id: { type: String, default: () => Math.random().toString(36).substr(2, 9) }
});
const fileInput = ref(null);
const previewUrl = ref(props.previewUrl || null);

function triggerFileInput() {
    fileInput.value && fileInput.value.click();
}
function onFileChange(e) {
    const file = e.target.files[0] || null;
    emit('update:modelValue', file);
    setPreview(file);
}
function onDrop(e) {
    const file = e.dataTransfer.files[0] || null;
    emit('update:modelValue', file);
    setPreview(file);
}
function setPreview(file) {
    if (file && file.type.startsWith('image/')) {
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewUrl.value = null;
    }
}
watch(() => props.modelValue, (file) => setPreview(file));
// Initialize preview if editing
setPreview(props.modelValue);
</script>