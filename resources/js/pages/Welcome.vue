<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import {
    GraduationCap,
    Users,
    BookOpen,
    Shield,
    ArrowRight,
    School,
    Calendar,
    Award
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});
</script>

<template>

    <Head title="Welcome to Multi School System" />

    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 overflow-hidden">
        <!-- Header -->
        <header class="relative z-10 px-4 sm:px-6 lg:px-8 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <AppLogo />

                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#features"
                        class="text-gray-600 hover:text-purple-600 transition-colors font-medium">Features</a>
                    <a href="#about" class="text-gray-600 hover:text-purple-600 transition-colors font-medium">About</a>
                    <a href="#contact"
                        class="text-gray-600 hover:text-purple-600 transition-colors font-medium">Contact</a>
                </nav>

                <div class="flex items-center space-x-3">
                    <Link :href="route('login')"
                        class="hidden sm:inline-flex items-center px-4 py-2 text-purple-600 hover:text-purple-700 font-medium transition-colors">
                    Sign In
                    </Link>
                    <Link :href="route('register')"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Get Started
                    <ArrowRight class="w-4 h-4 ml-2" />
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative z-10 px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left space-y-8">
                        <div class="space-y-4">
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                                <span
                                    class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                                    WELCOME
                                </span>
                                <br>
                                <span class="text-orange-500">TO THE FUTURE</span>
                                <br>
                                <span class="text-gray-700">of Education</span>
                            </h1>
                            <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                                A modern, comprehensive school management platform designed to streamline educational
                                operations,
                                enhance student learning, and empower educators with powerful tools.
                            </p>
                        </div>

                        <!-- Call to Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link :href="route('login')"
                                class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 group">
                            <Shield class="w-5 h-5 mr-2" />
                            Login to Dashboard
                            <ArrowRight class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                            </Link>
                            <Link :href="route('register')"
                                class="inline-flex items-center justify-center px-8 py-4 bg-white border-2 border-purple-200 text-purple-600 rounded-xl font-semibold text-lg hover:bg-purple-50 hover:border-purple-300 transform hover:-translate-y-1 transition-all duration-200 group">
                            <Users class="w-5 h-5 mr-2" />
                            Create Account
                            <ArrowRight class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>

                        <!-- Features Preview -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-8">
                            <div
                                class="text-center p-4 rounded-lg bg-white/50 backdrop-blur-sm border border-purple-100">
                                <GraduationCap class="w-8 h-8 text-purple-600 mx-auto mb-2" />
                                <p class="text-sm font-medium text-gray-700">Student Management</p>
                            </div>
                            <div
                                class="text-center p-4 rounded-lg bg-white/50 backdrop-blur-sm border border-purple-100">
                                <BookOpen class="w-8 h-8 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium text-gray-700">Academic Tools</p>
                            </div>
                            <div
                                class="text-center p-4 rounded-lg bg-white/50 backdrop-blur-sm border border-purple-100">
                                <Calendar class="w-8 h-8 text-orange-600 mx-auto mb-2" />
                                <p class="text-sm font-medium text-gray-700">Scheduling</p>
                            </div>
                            <div
                                class="text-center p-4 rounded-lg bg-white/50 backdrop-blur-sm border border-purple-100">
                                <Award class="w-8 h-8 text-green-600 mx-auto mb-2" />
                                <p class="text-sm font-medium text-gray-700">Performance</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Illustration -->
                    <div class="relative">
                        <div class="relative w-full h-96 lg:h-[500px] flex items-center justify-center">
                            <!-- Background Elements -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="w-80 h-80 bg-gradient-to-br from-purple-100 to-blue-100 rounded-full opacity-30 blur-xl">
                                </div>
                            </div>

                            <!-- Central School Building -->
                            <div class="relative z-10">
                                <div
                                    class="w-64 h-48 bg-gradient-to-br from-purple-400 to-blue-500 rounded-t-2xl relative shadow-2xl">
                                    <!-- Roof -->
                                    <div
                                        class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-72 h-8 bg-gradient-to-r from-purple-600 to-blue-600 rounded-t-2xl">
                                    </div>

                                    <!-- Windows -->
                                    <div class="absolute top-8 left-4 w-12 h-12 bg-yellow-300 rounded-lg shadow-inner">
                                    </div>
                                    <div class="absolute top-8 right-4 w-12 h-12 bg-yellow-300 rounded-lg shadow-inner">
                                    </div>
                                    <div class="absolute top-24 left-4 w-12 h-12 bg-yellow-300 rounded-lg shadow-inner">
                                    </div>
                                    <div
                                        class="absolute top-24 right-4 w-12 h-12 bg-yellow-300 rounded-lg shadow-inner">
                                    </div>

                                    <!-- Door -->
                                    <div
                                        class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-20 bg-gradient-to-b from-purple-700 to-purple-800 rounded-t-lg">
                                    </div>

                                    <!-- Wheels/Base -->
                                    <div class="absolute -bottom-2 left-8 w-8 h-4 bg-gray-600 rounded-full"></div>
                                    <div class="absolute -bottom-2 right-8 w-8 h-4 bg-gray-600 rounded-full"></div>
                                </div>
                            </div>

                            <!-- Characters -->
                            <div class="absolute left-8 top-1/2 transform -translate-y-1/2 z-20">
                                <div
                                    class="w-16 h-20 bg-gradient-to-b from-purple-500 to-purple-600 rounded-full relative">
                                    <div
                                        class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-orange-200 rounded-full">
                                    </div>
                                    <div class="absolute top-8 -left-2 w-4 h-8 bg-purple-500 rounded-full"></div>
                                    <div class="absolute top-8 -right-2 w-4 h-8 bg-purple-500 rounded-full"></div>
                                </div>
                            </div>

                            <div class="absolute right-8 top-1/2 transform -translate-y-1/2 z-20">
                                <div
                                    class="w-16 h-20 bg-gradient-to-b from-yellow-500 to-orange-500 rounded-full relative">
                                    <div
                                        class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-orange-200 rounded-full">
                                    </div>
                                    <div class="absolute top-8 -left-2 w-4 h-8 bg-yellow-500 rounded-full"></div>
                                    <div class="absolute top-8 -right-2 w-4 h-8 bg-yellow-500 rounded-full"></div>
                                </div>
                            </div>

                            <!-- Abstract Blocks -->
                            <div class="absolute left-4 top-8 w-6 h-6 bg-blue-300 rounded-lg opacity-60"></div>
                            <div class="absolute left-6 top-12 w-4 h-4 bg-blue-400 rounded-lg opacity-60"></div>
                            <div class="absolute right-4 top-8 w-6 h-6 bg-purple-300 rounded-lg opacity-60"></div>
                            <div class="absolute right-6 top-12 w-4 h-4 bg-purple-400 rounded-lg opacity-60"></div>

                            <!-- Plants -->
                            <div class="absolute bottom-4 left-4 w-8 h-8 bg-green-400 rounded-full opacity-60"></div>
                            <div class="absolute bottom-6 left-6 w-6 h-6 bg-green-500 rounded-full opacity-60"></div>
                            <div class="absolute bottom-4 right-4 w-8 h-8 bg-yellow-400 rounded-full opacity-60"></div>
                            <div class="absolute bottom-6 right-6 w-6 h-6 bg-yellow-500 rounded-full opacity-60"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Floating Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-20 left-10 w-4 h-4 bg-purple-300 rounded-full opacity-30 animate-bounce"></div>
            <div class="absolute top-40 right-20 w-6 h-6 bg-blue-300 rounded-full opacity-30 animate-bounce"
                style="animation-delay: 1s;"></div>
            <div class="absolute bottom-20 left-20 w-3 h-3 bg-orange-300 rounded-full opacity-30 animate-bounce"
                style="animation-delay: 2s;"></div>
            <div class="absolute bottom-40 right-10 w-5 h-5 bg-green-300 rounded-full opacity-30 animate-bounce"
                style="animation-delay: 0.5s;"></div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-left {
    from {
        opacity: 0;
        transform: translateX(30px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out;
}

.animate-fade-in-left {
    animation: fade-in-left 0.8s ease-out 0.2s both;
}

@media (max-width: 640px) {
    .animate-fade-in-left {
        animation: fade-in-up 0.8s ease-out 0.2s both;
    }
}
</style>
