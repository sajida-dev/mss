<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-white/30">
        <div
            class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-6 w-full max-w-md relative border border-neutral-200 dark:border-neutral-700">
            <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                @click="$emit('close')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-lg font-semibold mb-4 text-center text-gray-900 dark:text-gray-100">Upload Paid Voucher</h2>
            <form @submit.prevent="submit">
                <FileInput v-model="file" accept="image/*" @change="previewUrl = null" />
                <div v-if="previewUrl" class="flex flex-col items-center mt-2">
                    <img :src="previewUrl" alt="Preview" class="w-40 h-auto border rounded mb-2" />
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button"
                        class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-gray-200"
                        @click="$emit('close')">Cancel</button>
                    <button type="submit" :disabled="!file || loading"
                        class="px-4 py-2 rounded bg-green-700 text-white hover:bg-green-800 disabled:opacity-60">
                        <span v-if="loading">Uploading...</span>
                        <span v-else>Upload & Approve</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, defineProps, defineEmits, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import FileInput from '@/components/form/FileInput.vue';

const props = defineProps<{
    id: number,
    submitUrl?: string,

}>();
const emit = defineEmits(['uploaded', 'close']);

const file = ref<File | null>(null);
const previewUrl = ref<string | null>(null);
const loading = ref(false);

watch(file, (f) => {
    if (f) previewUrl.value = URL.createObjectURL(f);
    else previewUrl.value = null;
});

function submit() {
    if (!file.value) {
        toast.error('Please select a voucher image.');
        return;
    }
    loading.value = true;
    const formData = new FormData();
    formData.append('paid_voucher_image', file.value);
    router.post(route(props.submitUrl, props.id), formData, {
        forceFormData: true,
        onSuccess: (page) => {
            console.log('Upload success response:', page);
            toast.success('Paid voucher uploaded successfully.');
            emit('uploaded');
        },
        onError: (e) => {
            console.error('Upload error:', e);
            toast.error('Failed to upload voucher.');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}
</script>

<style scoped>
/* Modal styles for clarity */
</style>