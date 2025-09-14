<template>
    <div>
        <!-- Filters -->
        <div class="mb-6 p-4 rounded shadow bg-gray-100 dark:bg-neutral-800">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search
                        Users</label>
                    <input v-model="filters.search" type="text" placeholder="Search by name or email..."
                        class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                        @input="debouncedSearch" />
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by
                        Role</label>
                    <select v-model="filters.role"
                        class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="applyFilters">
                        <option value="">All Roles</option>
                        <option v-for="role in roles" :key="role.id" :value="role.name">
                            {{ role.name }} ({{ role.school_name }})
                        </option>
                    </select>
                </div>
                <div class="w-full sm:w-32">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Per
                        Page</label>
                    <select v-model="filters.per_page"
                        class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="applyFilters">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <BaseDataTable :headers="userTableHeaders" :items="userTableItems" :loading="loading" :expandable="true"
            :expand-row-keys="expandedRow ? [expandedRow] : []">
            <template #item-avatar="row">
                <div v-if="row" class="flex items-center justify-center">
                    <img v-if="row.profile_photo_url" :src="row.profile_photo_url"
                        class="w-8 h-8 rounded-full border object-cover" />
                    <div v-else
                        class="w-8 h-8 rounded-full bg-gray-200 dark:bg-neutral-700 flex items-center justify-center text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ row?.name ? getInitials(row.name) : '?' }}</div>
                </div>
            </template>
            <template #item-name="row">
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ row.name }}</span>
            </template>
            <template #item-school="row">
                <span v-if="getUserSchools(row).length === 1"
                    class="inline-flex items-center px-2 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800 text-xs">
                    {{ getUserSchools(row)[0] }}
                </span>
                <span v-else-if="getUserSchools(row).length > 1"
                    class="inline-flex items-center px-2 py-1 rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800 text-xs cursor-pointer"
                    :title="getUserSchools(row).join(', ')">
                    Multiple
                </span>
                <span v-else class="text-xs text-gray-400">No school</span>
            </template>
            <template #item-roles="row">
                <div class="flex flex-wrap gap-1.5">
                    <template v-if="row.roles_by_school && Object.keys(row.roles_by_school).length">
                        <div v-for="(roles, schoolName) in row.roles_by_school" :key="schoolName">
                            <span v-for="role in roles" :key="role.id"
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shadow-sm bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800 mr-1 mb-1">
                                {{ role.name }}
                                <button v-if="!isSuperAdmin(row) && !isCurrentUser(row.id)"
                                    @click.stop="askRemoveRole(row.id, role.id)"
                                    class="ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    title="Remove role">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        </div>
                    </template>
                    <template v-else>
                        <span v-for="role in row.roles" :key="role.id + '-' + (role.school_id || 'global')"
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shadow-sm bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800">
                            {{ role.name }}
                            <button v-if="!isSuperAdmin(row) && !isCurrentUser(row.id)"
                                @click.stop="askRemoveRole(row.id, role.id)"
                                class="ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                title="Remove role">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    </template>
                    <span
                        v-if="(!row.roles || row.roles.length === 0) && (!row.roles_by_school || Object.keys(row.roles_by_school).length === 0)"
                        class="text-gray-400 dark:text-gray-500 text-xs italic">No roles assigned</span>
                </div>
            </template>
            <template #item-joined="row">
                <span class="text-gray-600 dark:text-gray-400 text-sm">{{ formatDate(row.created_at) }}</span>
            </template>
            <template #item-actions="row">
                <button v-if="!isSuperAdmin(row)" type="button" @click="openAssignRoleModal(row)"
                    class="px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition text-sm">Assign
                    Role</button>
                <span v-else class="text-xs text-gray-500 dark:text-gray-400 italic">Super Admin</span>
            </template>
            <template #item-expand="row">
                <button @click="toggleRowExpansion(row)"
                    class="text-gray-500 hover:text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary rounded p-1 transition">
                    <svg v-if="expandedRow === row.id" class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </template>
            <template #expand="row">
                <div v-if="expandedRow === row.id"
                    class="p-4 bg-gray-50 dark:bg-neutral-800 rounded-b-lg border-t border-gray-200 dark:border-neutral-700">
                    <!-- Profile Image Section -->
                    <div class="flex items-center mb-4 pb-4 border-b border-gray-200 dark:border-neutral-700">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <img v-if="row.profile_photo_url" :src="row.profile_photo_url"
                                    class="w-16 h-16 rounded-full border-2 border-gray-300 dark:border-neutral-600 object-cover shadow-sm"
                                    :alt="`${row.name}'s profile photo`" />
                                <div v-else
                                    class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-xl font-bold border-2 border-gray-300 dark:border-neutral-600 shadow-sm">
                                    {{ getInitials(row.name) }}
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ row.name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ row.email }}</p>
                                <p v-if="row.username" class="text-xs text-gray-500 dark:text-gray-500">@{{ row.username
                                    }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <div class="font-semibold mb-1">Contact Information</div>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Email:</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">{{ row.email }}</span>
                                </div>
                                <div v-if="row.phone_number">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone:</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">{{ row.phone_number
                                        }}</span>
                                </div>
                                <div v-if="row.username">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Username:</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">{{ row.username
                                        }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold mb-1">Roles & School Affiliations</div>
                            <div v-if="row.roles_by_school && Object.keys(row.roles_by_school).length">
                                <div v-for="(roles, schoolName) in row.roles_by_school" :key="schoolName" class="mb-2">
                                    <span class="font-medium text-sm">{{ schoolName }}</span>
                                    <span v-if="roles.length" class="ml-2">
                                        <span v-for="role in roles" :key="role.id"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shadow-sm bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800 mr-1 mb-1">
                                            {{ role.name }}
                                        </span>
                                    </span>
                                    <span v-else class="ml-2 italic text-xs text-gray-400">No roles</span>
                                </div>
                            </div>
                            <div v-else class="italic text-xs text-gray-400">No school affiliations yet</div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold mb-1">Password</div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-400 dark:text-gray-500">
                                    <template v-if="row.show_password && row.decrypted_password">
                                        {{ row.decrypted_password }}
                                    </template>
                                    <template v-else>
                                        ••••••••••••
                                    </template>
                                </span>
                                <button @click="emit('togglePasswordVisibility', row)"
                                    class="ml-2 p-1 rounded hover:bg-gray-200 dark:hover:bg-neutral-700 transition-colors"
                                    title="View/Hide Password">
                                    <Eye v-if="!row.show_password" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                                <button @click="emit('resetPassword', row.id)"
                                    class="ml-2 px-2 py-1 rounded bg-blue-500 text-white text-xs hover:bg-blue-600 transition-colors"
                                    title="Reset Password">
                                    Reset Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </BaseDataTable>
        <AlertDialog v-model="showRemoveDialog" title="Remove Role"
            message="Are you sure you want to remove this role from the user? This action cannot be undone."
            confirmText="Remove" cancelText="Cancel" @confirm="confirmRemoveRole" />
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import BaseDataTable from '@/components/ui/BaseDataTable.vue'
import AlertDialog from '@/components/AlertDialog.vue'
import { Eye, EyeOff } from 'lucide-vue-next';

// Props
const props = defineProps({
    users: {
        type: Array,
        required: true
    },
    roles: {
        type: Array,
        required: true
    },
    schools: {
        type: Array,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
})

// Emits
const emit = defineEmits(['openAssignRoleModal', 'togglePasswordVisibility', 'resetPassword'])

// Reactive state
const loading = ref(false)
const expandedRow = ref(null)

// Filters
const filters = reactive({
    search: '',
    role: '',
    per_page: 15
})

// Debounced search
let searchTimeout
const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 300)
}

// Apply filters and fetch data
const applyFilters = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams({
            search: filters.search,
            role: filters.role,
            per_page: filters.per_page.toString(),
            page: '1' // Reset to first page when filtering
        })

        // Use Inertia to navigate with new filters
        router.get('/settings/users', Object.fromEntries(params))
    } catch (error) {
        console.error('Error applying filters:', error)
        toast.error('Failed to apply filters')
    } finally {
        loading.value = false
    }
}

// Utility functions
const getInitials = (name) => {
    if (!name) return '';
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

const getRoleNames = (roles) => {
    return roles.map((r) => r.name).join(', ');
}

const getSchoolName = (schoolId) => {
    if (!schoolId) return 'Global';
    const school = props.schools.find(s => s.id === schoolId);
    return school ? school.name : 'Unknown School';
}

// Check if user is current user
const isCurrentUser = (userId) => {
    return props.auth?.user?.id === userId
}

// Check if user is super admin
const isSuperAdmin = (user) => {
    if (!user || !user.roles) {
        return false;
    }

    if (!Array.isArray(user.roles)) {
        return false;
    }

    return user.roles.some((role) => role.name === 'superadmin');
}

// Event handlers
const openAssignRoleModal = (user) => {
    emit('openAssignRoleModal', user)
}

const togglePasswordVisibility = (userId) => {
    emit('togglePasswordVisibility', userId)
}

const toggleRowExpansion = (row) => {
    expandedRow.value = expandedRow.value === row.id ? null : row.id
}

// State for role removal confirmation dialog
const showRemoveDialog = ref(false)
const roleToRemove = ref({ userId: null, roleId: null })

const askRemoveRole = (userId, roleId) => {
    roleToRemove.value = { userId, roleId }
    showRemoveDialog.value = true
}

const confirmRemoveRole = () => {
    const { userId, roleId } = roleToRemove.value
    actuallyRemoveRole(userId, roleId)
}

const actuallyRemoveRole = async (userId, roleId) => {
    try {
        router.delete(`/settings/user-management/remove-role`, {
            data: { user_id: userId, role_id: roleId },
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Role removed successfully!')
                // Update the user's roles in the table
                const userIndex = props.users.findIndex(user => user.id === userId)
                if (userIndex !== -1) {
                    const updatedUser = { ...props.users[userIndex] }
                    // Remove the role from roles_by_school if it exists
                    if (updatedUser.roles_by_school) {
                        Object.keys(updatedUser.roles_by_school).forEach(schoolName => {
                            updatedUser.roles_by_school[schoolName] = updatedUser.roles_by_school[schoolName].filter(r => r.id !== roleId)
                        })
                    }
                    // Remove the role from the roles array if it exists
                    updatedUser.roles = updatedUser.roles.filter(r => r.id !== roleId)
                    props.users[userIndex] = updatedUser
                }
            },
            onError: (errors) => {
                Object.values(errors).flat().forEach(message => toast.error(message))
            }
        })
    } catch (error) {
        toast.error('Failed to remove role')
    }
}

// Utility function to get all unique schools for a user
const getUserSchools = (user) => {
    if (!user) return [];
    const schools = new Set();
    if (user.roles_by_school) {
        Object.keys(user.roles_by_school).forEach(schoolName => {
            if (schoolName && user.roles_by_school[schoolName].length > 0) {
                schools.add(schoolName);
            }
        });
    } else if (user.roles && user.roles.length > 0) {
        user.roles.forEach(role => {
            if (role.school_id) {
                const school = props.schools.find(s => s.id === role.school_id);
                if (school) schools.add(school.name);
            }
        });
    }
    return Array.from(schools);
}

// Table configuration
const userTableHeaders = [
    { text: 'Avatar', value: 'avatar', sortable: false },
    { text: 'Name', value: 'name' },
    { text: 'School', value: 'school' },
    { text: 'Roles', value: 'roles' },
    { text: 'Joined', value: 'joined' },
    { text: 'Actions', value: 'actions', sortable: false }
    // Removed the expand column to avoid duplicate expand buttons
]

const userTableItems = computed(() => {
    return props.users
        .filter(user => !!user && typeof user === 'object')
        .map(user => {
            // Get the primary school name from roles
            let schoolName = '';
            if (user.roles && user.roles.length > 0) {
                const primaryRole = user.roles[0];
                if (primaryRole.school_id) {
                    schoolName = getSchoolName(primaryRole.school_id);
                }
            }

            return {
                ...user,
                school_name: schoolName,
            };
        });
})
</script>