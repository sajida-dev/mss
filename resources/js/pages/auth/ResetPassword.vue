<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
const currentYear = new Date().getFullYear();

</script>

<template>

    <Head title="Reset password" />

    <div class="min-h-screen bg-purple-800 flex items-center justify-center">
        <div
            class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden m-2 md:m-0">
            <!-- Left Panel -->
            <div class="w-full md:w-1/2 bg-purple-900 flex flex-col justify-between p-6 md:p-8 relative">
                <div>
                    <div class="flex items-center mb-4 md:mb-8">
                        <span class="text-white text-xl md:text-2xl font-bold tracking-wide">Multi School System</span>
                    </div>
                    <div class="w-full h-40 md:h-72 flex items-center justify-center mb-4 md:mb-8 mt-4 md:mt-8">
                        <!-- Placeholder for illustration - you can replace with actual image -->
                        <div class="text-purple-200 text-center">
                            <div class="text-6xl mb-4">🔑</div>
                            <p class="text-lg font-semibold">Reset Password</p>
                            <p class="text-sm opacity-75">Secure Your Account</p>
                        </div>
                    </div>
                </div>
                <div
                    class="flex flex-col md:flex-row justify-between text-xs text-purple-200 mt-4 md:mt-6 space-y-2 md:space-y-0">
                    <span>Multi School System — Education Management</span>
                    <span>©{{ currentYear }} All Rights Reserved</span>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-6 md:p-12">
                <div class="w-full max-w-xs sm:max-w-md">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2 text-center md:text-left">Reset
                        Password</h2>
                    <p class="text-gray-500 mb-4 md:mb-6 text-center md:text-left">Please enter your new password below
                    </p>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1" for="email">Email Address</label>
                            <input id="email" name="email" v-model="form.email" type="email"
                                class="w-full px-3 py-2 md:px-4 md:py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                                readonly>
                            <InputError :message="form.errors.email" />
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1" for="password">New Password</label>
                            <div class="relative">
                                <input id="password" name="password" v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'" placeholder="Enter new password"
                                    class="w-full px-3 py-2 md:px-4 md:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400"
                                    required autofocus>
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <Eye v-if="!showPassword" class="w-5 h-5" />
                                    <EyeOff v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1" for="password_confirmation">Confirm New
                                Password</label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    placeholder="Confirm new password"
                                    class="w-full px-3 py-2 md:px-4 md:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400"
                                    required>
                                <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <Eye v-if="!showPasswordConfirmation" class="w-5 h-5" />
                                    <EyeOff v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing"
                                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                            <span v-else>Reset Password</span>
                            <ArrowRight v-if="!form.processing" class="w-4 h-4" />
                        </button>

                        <div class="text-xs text-gray-500 mt-3 text-center">
                            Your password will be updated securely
                        </div>
                    </form>

                    <div class="mt-4 md:mt-6 text-center text-sm text-gray-600">
                        Remember your password?
                        <Link :href="route('login')" class="text-purple-700 font-semibold hover:underline">Back to Login
                        </Link>
                    </div>

                    <div class="flex justify-between text-xs text-gray-400 mt-8">
                        <span>Privacy</span>
                        <span>Terms of Service.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
