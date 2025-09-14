<template>
    <Dialog :model-value="true" @update:modelValue="$emit('cancel')">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div
                class="relative z-50 bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-xl border border-gray-200 dark:border-neutral-700 min-w-[400px] max-w-md mx-auto w-full">
                <h2 class="text-lg font-bold mb-4 text-center">Reset User Password</h2>
                <form @submit.prevent="onSubmit">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Admin Password (for verification)</label>
                        <div class="relative">
                            <input v-model="adminPassword" :type="showAdminPassword ? 'text' : 'password'"
                                class="w-full px-3 py-2 pr-10 rounded border border-gray-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                                autocomplete="current-password" required />
                            <button type="button" @click="showAdminPassword = !showAdminPassword"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <Eye v-if="!showAdminPassword" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">New Password</label>
                        <div class="relative">
                            <input v-model="newPassword" :type="showNewPassword ? 'text' : 'password'"
                                class="w-full px-3 py-2 pr-10 rounded border border-gray-300 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-400 focus:border-blue-500 dark:focus:border-blue-400"
                                autocomplete="new-password" required />
                            <button type="button" @click="showNewPassword = !showNewPassword"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <Eye v-if="!showNewPassword" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <button type="button" class="mt-2 text-xs text-blue-600 hover:underline"
                            @click="generatePassword">Generate Random Password</button>
                    </div>
                    <div v-if="error" class="text-xs text-red-500 mb-2">{{ error }}</div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button @click="$emit('cancel')" type="button"
                            class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-neutral-600">Cancel</button>
                        <button type="submit" :disabled="loading"
                            class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <span v-if="loading">Resetting...</span>
                            <span v-else>Reset Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Dialog from './dialog/Dialog.vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { toast } from 'vue3-toastify';

const props = defineProps({
    userId: { type: [String, Number], required: true }
});
const emit = defineEmits(['success', 'cancel']);

const adminPassword = ref('');
const newPassword = ref('');
const error = ref('');
const loading = ref(false);
const showAdminPassword = ref(false);
const showNewPassword = ref(false);

function generatePassword() {
    // Simple random password generator
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    newPassword.value = pass;
}

generatePassword(); // Generate by default

function onSubmit() {
    error.value = '';
    loading.value = true;
    useForm({
        user_id: props.userId,
        admin_password: adminPassword.value,
        new_password: newPassword.value
    }).post('/settings/user-management/reset-password', {
        preserveScroll: true,
        onSuccess: (response) => {
            loading.value = false;
            console.log('response', response)
            if (response.success) {
                emit('success', response.success);
                toast.success('Password reset successfully');
            } else if (response.error) {
                error.value = response.error;
                toast.error(error.value);
            } else {
                error.value = 'Password reset failed. Please try again.';
                toast.error(error.value);
            }
        },
        onError: (errors) => {
            loading.value = false;
            error.value = Object.values(errors).flat().join(' ');
            toast.error(error.value);
        }
    });
}
</script>