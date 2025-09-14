<template>
    <Dialog :model-value="modelValue" @update:modelValue="val => $emit('update:modelValue', val)">
        <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div
                class="relative z-50 bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-xl border border-gray-200 dark:border-neutral-700 min-w-[400px] max-w-md mx-auto w-full">
                <h2 class="text-lg font-bold mb-4 text-center">Verify Admin Password</h2>
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-1">Security Check</h4>
                            <p class="text-xs text-blue-700 dark:text-blue-300">Please enter your admin password to view
                                the user password.</p>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="onVerify">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Admin Password</label>
                        <div class="relative">
                            <input v-model="password" :type="showPassword ? 'text' : 'password'"
                                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                                autocomplete="current-password" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-2 top-2 text-gray-500">
                                <EyeOff v-if="showPassword" class="h-5 w-5" />
                                <Eye v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <div v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button @click="onCancel" type="button"
                            class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-neutral-600">Cancel</button>
                        <button type="submit" :disabled="loading || !password"
                            class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ loading ? 'Verifying...' : 'Verify' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import Dialog from './dialog/Dialog.vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps({
    modelValue: Boolean,
    loading: Boolean,
    error: { type: String, default: null }
});
const emit = defineEmits(['update:modelValue', 'verify', 'cancel', 'success']);

const password = ref('');
const verifyingPassword = ref(false);
const showPassword = ref(false);

// Password verification form
const passwordVerificationForm = useForm({
    password: '',
});

watch(() => props.modelValue, (val) => {
    if (!val) {
        password.value = '';
        passwordVerificationForm.reset();
    }
});

function onVerify() {
    if (!password.value) {
        toast.error('Please enter your admin password');
        return;
    }

    verifyingPassword.value = true;
    passwordVerificationForm.password = password.value;

    passwordVerificationForm.post('/settings/user-management/verify-password', {
        preserveScroll: true,
        onSuccess: (response) => {
            toast.success('Password verification successful');
            emit('success', password.value);
            onCancel();
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => {
                if (typeof message === 'string') {
                    toast.error(message);
                }
            });
        },
        onFinish: () => {
            verifyingPassword.value = false;
        }
    });
}

function onCancel() {
    emit('cancel');
    emit('update:modelValue', false);
}
</script>