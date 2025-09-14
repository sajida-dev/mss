<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <div class="text-6xl font-bold text-gray-300 dark:text-gray-700 mb-4">
                    {{ status }}
                </div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                    {{ getTitle() }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    {{ message }}
                </p>
            </div>

            <div class="space-y-4">
                <Button @click="goBack" variant="outline" class="w-full">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Go Back
                </Button>

                <Button @click="goHome" class="w-full">
                    <Home class="w-4 h-4 mr-2" />
                    Go to Dashboard
                </Button>
            </div>

            <!-- Additional help for common errors -->
            <div v-if="status === 404" class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                    Need Help?
                </h3>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    The page you're looking for might have been moved, deleted, or you entered the wrong URL.
                </p>
            </div>

            <div v-if="status === 500" class="mt-8 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                    Something went wrong
                </h3>
                <p class="text-xs text-red-700 dark:text-red-300">
                    We're working to fix this issue. Please try again in a few moments.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ArrowLeft, Home } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Props {
    status: number;
    message: string;
}

const props = defineProps<Props>();

const getTitle = () => {
    switch (props.status) {
        case 404:
            return 'Page Not Found';
        case 403:
            return 'Access Denied';
        case 419:
            return 'Page Expired';
        case 429:
            return 'Too Many Requests';
        case 500:
            return 'Server Error';
        case 503:
            return 'Service Unavailable';
        default:
            return 'Error';
    }
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/dashboard');
    }
};

const goHome = () => {
    router.visit('/dashboard');
};
</script>