<script setup lang="ts">
import { ref, inject, onMounted, onUnmounted } from 'vue';
import AlertDialog from './AlertDialog.vue';
import type mitt from 'mitt';

const show = ref(false);
const title = ref('');
const message = ref('');
const confirmText = ref('');
const cancelText = ref('');
const onConfirm = ref<null | (() => void)>(null);

type AlertPayload = {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    onConfirm?: () => void;
};

const emitter = inject('emitter') as ReturnType<typeof mitt> | undefined;

function openAlert(payload: AlertPayload) {
    title.value = payload.title || 'Warning';
    message.value = payload.message || '';
    confirmText.value = payload.confirmText || '';
    cancelText.value = payload.cancelText || '';
    onConfirm.value = payload.onConfirm || null;
    show.value = true;
}

function handleConfirm() {
    if (onConfirm.value) onConfirm.value();
    show.value = false;
}

onMounted(() => {
    emitter?.on('alert', (payload) => openAlert(payload as AlertPayload));
});
onUnmounted(() => {
    emitter?.off('alert', (payload) => openAlert(payload as AlertPayload));
});
</script>
<template>
    <AlertDialog v-model="show" :title="title" :message="message" :confirmText="confirmText" :cancelText="cancelText"
        @confirm="handleConfirm" />
</template>