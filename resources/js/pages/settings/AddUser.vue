<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Add New User" />
        <SettingsLayout>
            <div class="max-w-2xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
                <h1 class="text-2xl font-bold mb-6">Add New User</h1>

                <!-- System Explanation -->
                <div
                    class="mb-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">How User Creation Works</h3>
                    <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                        <p>• Select a school from the global school selector to create a user for that school</p>
                        <p>• Users are automatically assigned the selected role for the chosen school</p>
                        <p>• A temporary password is generated and shown after successful creation</p>
                        <p>• Users can only have roles from one school at a time</p>
                        <p>• Super admin role cannot be assigned during user creation</p>
                    </div>
                </div>

                <!-- Current School Context -->
                <div
                    class="mb-6 p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-200 mb-1">Current School
                                Context</h3>
                            <div class="text-sm text-purple-700 dark:text-purple-300">
                                {{ selectedSchool?.name || 'No school selected' }}
                            </div>
                            <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                                User will be created for this school
                            </div>
                        </div>
                        <div v-if="!selectedSchool" class="text-xs text-orange-600 dark:text-orange-400">
                            Please select a school first
                        </div>
                    </div>
                </div>

                <!-- User Creation Form -->
                <div
                    class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800">
                    <div class="p-6">
                        <form @submit.prevent="createUser" class="space-y-6" enctype="multipart/form-data">
                            <!-- User Details -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Information</h3>

                                <!-- Name and Username in one row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input v-model="createUserForm.name" type="text" required
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
                                            placeholder="Enter full name" />
                                        <div v-if="createUserForm.errors.name" class="text-xs text-red-500 mt-1">
                                            {{ createUserForm.errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Username <span class="text-red-500">*</span>
                                        </label>
                                        <input v-model="createUserForm.username" type="text" required
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
                                            placeholder="Enter username" />
                                        <div v-if="createUserForm.errors.username" class="text-xs text-red-500 mt-1">
                                            {{ createUserForm.errors.username }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Email and Phone in one row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input v-model="createUserForm.email" type="email" required
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
                                            placeholder="Enter email address" />
                                        <div v-if="createUserForm.errors.email" class="text-xs text-red-500 mt-1">
                                            {{ createUserForm.errors.email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Phone Number <span class="text-red-500">*</span>
                                        </label>
                                        <input v-model="createUserForm.phone" type="text" required
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
                                            placeholder="Enter phone number" />
                                        <div v-if="createUserForm.errors.phone" class="text-xs text-red-500 mt-1">
                                            {{ createUserForm.errors.phone }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        <div class="relative">
                                            <input v-model="createUserForm.password"
                                                :type="showPassword ? 'text' : 'password'" required
                                                class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
                                                placeholder="Enter password or leave empty for auto-generation" />
                                            <button type="button" @click="showPassword = !showPassword"
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                                <Eye v-if="!showPassword" class="w-4 h-4" />
                                                <EyeOff v-else class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" @click="generatePassword"
                                                class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                                                Generate Password
                                            </button>
                                            <button type="button" @click="clearPassword"
                                                class="px-3 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                                                Clear
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            Leave empty to auto-generate a secure password, or enter a custom password
                                            (minimum 8 characters)
                                        </p>
                                    </div>
                                    <div v-if="createUserForm.errors.password" class="text-xs text-red-500 mt-1">
                                        {{ createUserForm.errors.password }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Profile Photo <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-3">
                                        <!-- Image Preview -->
                                        <div v-if="imagePreview" class="flex items-center space-x-3">
                                            <img :src="imagePreview" alt="Profile preview"
                                                class="w-16 h-16 rounded-full object-cover border-2 border-gray-300 dark:border-neutral-600" />
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedImageName
                                                }}</p>
                                                <button type="button" @click="removeImage"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                    Remove image
                                                </button>
                                            </div>
                                        </div>

                                        <!-- File Input -->
                                        <div v-if="!imagePreview" class="flex items-center justify-center w-full">
                                            <label
                                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-neutral-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-neutral-800 hover:bg-gray-100 dark:hover:bg-neutral-700">
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <Icon name="Upload"
                                                        class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" />
                                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                                        <span class="font-semibold">Click to upload</span> or drag and
                                                        drop
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG up
                                                        to 2MB</p>
                                                </div>
                                                <input ref="fileInput" type="file" class="hidden" accept="image/*"
                                                    @change="handleImageUpload" />
                                            </label>
                                        </div>
                                    </div>
                                    <div v-if="createUserForm.errors.profile_photo" class="text-xs text-red-500 mt-1">
                                        {{ createUserForm.errors.profile_photo }}
                                    </div>
                                </div>
                            </div>

                            <!-- Role Selection -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Role Assignment</h3>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Role <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="createUserForm.role_name" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent">
                                        <option value="" disabled>
                                            Select a role for {{ selectedSchool?.name || 'selected school' }}</option>
                                        <option v-for="role in filteredRoles" :key="role.id" :value="role.name">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                    <div v-if="createUserForm.errors.role_name" class="text-xs text-red-500 mt-1">
                                        {{ createUserForm.errors.role_name }}
                                    </div>
                                    <div v-if="filteredRoles.length === 0 && selectedSchool"
                                        class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                        No roles available for the selected school. Please create roles for this school
                                        first.
                                    </div>
                                </div>

                                <!-- Available Roles Preview -->
                                <div v-if="filteredRoles.length > 0"
                                    class="p-3 bg-gray-50 dark:bg-neutral-800 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available
                                        Roles for {{ selectedSchool?.name }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="role in filteredRoles" :key="role.id"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-neutral-700">
                                <button type="button" @click="resetForm"
                                    class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-neutral-600 transition-colors">
                                    Reset Form
                                </button>
                                <button type="submit" :disabled="!canSubmit || creating"
                                    class="px-6 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                                    <svg v-if="creating" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ creating ? 'Creating User...' : 'Create User' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="successMessage"
                    class="mt-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-1">User Created
                                Successfully!</h3>
                            <div class="text-sm text-green-700 dark:text-green-300 space-y-2">
                                <p>{{ successMessage }}</p>
                                <div
                                    class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded border border-yellow-200 dark:border-yellow-800">
                                    <p class="text-xs font-medium text-yellow-800 dark:text-yellow-200 mb-1">Important:
                                    </p>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-300">Please save this temporary
                                        password. The user will need it to log in for the first time.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { toast } from 'vue3-toastify'
import { type BreadcrumbItem } from '@/types'
import { useSchoolStore } from '@/stores/school'
import { storeToRefs } from 'pinia'
import { Eye, EyeOff } from 'lucide-vue-next'
import Icon from '@/components/Icon.vue';

// Props from Inertia
const props = defineProps<{
    roles: any[]
    schools: any[]
    auth: {
        user: {
            id: number
            name: string
            email: string
        }
    }
}>()

// Reactive state
const creating = ref(false)
const successMessage = ref('')
const imagePreview = ref('')
const selectedImageName = ref('')
const fileInput = ref<HTMLInputElement>()
const showPassword = ref(false)

// Types
interface Role {
    id: number
    name: string
    school_id?: number
    school_name?: string
}

interface School {
    id: number
    name: string
    logo?: string
    phone?: string
}

// Store and computed
const roles = ref<Role[]>(props.roles || [])
const schools = computed(() => props.schools || [])
const schoolStore = useSchoolStore()
const { selectedSchool } = storeToRefs(schoolStore)

// Form
const createUserForm = useForm({
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    profile_photo: null as File | null,
    role_name: '',
    school_id: null as number | null,
})

// Computed properties
const filteredRoles = computed(() => {
    if (!selectedSchool.value || typeof selectedSchool.value.id !== 'number') {
        return []
    }

    const selectedSchoolId = selectedSchool.value.id
    return roles.value.filter((r: Role) => {
        return r.school_id === selectedSchoolId && r.name !== 'superadmin'
    })
})

const canSubmit = computed(() => {
    return createUserForm.name.trim() &&
        createUserForm.username.trim() &&
        createUserForm.email.trim() &&
        createUserForm.phone.trim() &&
        createUserForm.profile_photo &&
        createUserForm.role_name &&
        selectedSchool.value &&
        filteredRoles.value.length > 0
})

// Image upload methods
const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            toast.error('Please select a valid image file')
            return
        }

        // Validate file size (2MB limit)
        if (file.size > 2 * 1024 * 1024) {
            toast.error('Image size must be less than 2MB')
            return
        }

        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string
            selectedImageName.value = file.name
        }
        reader.readAsDataURL(file)

        // Set form data
        createUserForm.profile_photo = file
    }
}

const removeImage = () => {
    imagePreview.value = ''
    selectedImageName.value = ''
    createUserForm.profile_photo = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const generatePassword = () => {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'
    let pass = ''
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    createUserForm.password = pass
    showPassword.value = true
}

const clearPassword = () => {
    createUserForm.password = ''
    showPassword.value = false
}

// Methods
const createUser = () => {
    if (!canSubmit.value) {
        toast.error('Please fill in all required fields and select a school with available roles.')
        return
    }

    createUserForm.school_id = selectedSchool.value && typeof selectedSchool.value.id === 'number'
        ? selectedSchool.value.id
        : null

    if (!createUserForm.school_id) {
        toast.error('No school selected for user creation.')
        return
    }

    creating.value = true
    successMessage.value = ''

    createUserForm.post('/settings/user-management/create', {
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('User creation response:', response)

            // Extract success message from flash data
            const flash = response.props.flash as any
            if (flash && flash.success) {
                successMessage.value = flash.success
                toast.success('User created successfully!')
                resetForm()
            } else {
                // If no flash message, show a generic success message
                toast.success('User created successfully!')
                resetForm()
            }
        },
        onError: (errors) => {
            console.error('User creation errors:', errors)
            // Show each error as a separate toast
            Object.values(errors).flat().forEach(message => {
                if (typeof message === 'string') {
                    toast.error(message)
                }
            })
        },
        onFinish: () => {
            creating.value = false
        }
    })
}

const resetForm = () => {
    createUserForm.reset()
    createUserForm.school_id = null
    successMessage.value = ''
    removeImage()
}

// Breadcrumbs
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
    },
    {
        title: 'Add New User',
        href: '/settings/add-user',
    },
]
</script>
