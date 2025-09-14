<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="User Management" />
        <SettingsLayout>
            <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">User Management</h1>

                    <Link href="/settings/add-user" v-can="'create-users'"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New User
                    </Link>
                </div>

                <!-- System Explanation -->
                <div
                    class="mb-6 p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                    <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-200 mb-2">How User Role Assignment
                        Works</h3>
                    <div class="text-sm text-purple-700 dark:text-purple-300 space-y-1">
                        <p>• Users are not linked to schools until they are assigned roles</p>
                        <p>• Select a school from the global school selector to assign roles for that school</p>
                        <p>• Each role assignment links the user to the selected school</p>
                        <p>• Users can have different roles in different schools</p>
                        <p>• Super admin role is system-wide and cannot be assigned manually</p>
                    </div>
                </div>

                <p class="mb-4 text-sm text-muted-foreground">Manage users and their roles. Search, filter, and assign
                    roles to users.</p>

                <!-- Users Table Component -->
                <UsersTable :users="users" :roles="roles" :schools="schools" :auth="props.auth"
                    @openAssignRoleModal="openAssignRoleModal" @togglePasswordVisibility="togglePasswordVisibility"
                    @resetPassword="handleResetPassword" />
                <!-- Modals ... -->
                <RoleAssignModal v-model="showAssignModal" :user="selectedUser as any" :school="selectedSchool as any"
                    :roles="roles" :assigningRole="assigningRole" :assignRoleError="assignRoleError"
                    @assign="assignRole" @cancel="closeAssignRoleModal" />
                <PasswordVerificationModal v-model="showPasswordModal" @success="onPasswordVerified"
                    @cancel="closePasswordModal" />
                <PasswordResetModal v-if="showPasswordResetModal" :user-id="resetUserId"
                    @success="onPasswordResetSuccess" @cancel="() => showPasswordResetModal = false" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { toast } from 'vue3-toastify'
import { type BreadcrumbItem } from '@/types'
import RoleAssignModal from '@/components/ui/RoleAssignModal.vue';
import PasswordVerificationModal from '@/components/ui/PasswordVerificationModal.vue';
import UsersTable from '@/components/users/UsersTable.vue';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';
import { onBeforeUnmount } from 'vue';
import PasswordResetModal from '@/components/ui/PasswordResetModal.vue';

// Props from Inertia
const props = defineProps<{
    users: any
    roles: any[]
    schools: any[] // <-- Added this line
    filters: {
        search: string
        role: string
        per_page: number
    }
    auth: {
        user: {
            id: number
            name: string
            email: string
        }
    }
}>()

// Reactive state
const showAssignModal = ref(false)
const showPasswordModal = ref(false)
const selectedUserForPassword = ref<User | undefined>(undefined)
const passwordHideTimeouts = ref<Record<number, number>>({})
const showPasswordResetModal = ref(false);
const resetUserId = ref<number | undefined>(undefined);
const newPasswordShown = ref('');

// Add types for user, role, and school
interface Role { id: number; name: string; school_id?: number; school_name?: string; }
interface School { id: number; name: string; logo?: string; phone?: string; }
interface User {
    id: number;
    name: string;
    email: string;
    profile_photo_url?: string;
    roles: Role[];
    roles_by_school?: Record<string, Role[]>;
    created_at: string;
    password?: string;
    show_password?: boolean;
    decrypted_password?: string; // Added for decrypted password
}

const selectedUser = ref<User | undefined>(undefined);
const roles = ref<Role[]>(props.roles || []);
const schools = computed(() => props.schools || []);
const schoolStore = useSchoolStore();
const { selectedSchool } = storeToRefs(schoolStore);

// Restore users variable, assuming it comes from props or API
const users = computed<User[]>(() => {
    if (Array.isArray(props.users)) return props.users.filter(Boolean);
    if (props.users && Array.isArray(props.users.data)) return props.users.data.filter(Boolean);
    return [];
});


const assignRoleForm = useForm({
    user_id: null as number | null,
    role_name: '' as string,
    school_id: null as number | null, // Add this property for type safety
});





const assignRoleError = ref("");
const assigningRole = ref(false);

// Check if user is current user
const isCurrentUser = (userId: number) => {
    return props.auth?.user?.id === userId
}







// Open assign role modal
function openAssignRoleModal(user: any) {
    if (!user) return;
    selectedUser.value = user;
    showAssignModal.value = true;
}

// Assign role
const assignRole = (roleName: string) => {
    assignRoleError.value = "";
    if (!selectedUser.value || !roleName) {
        return;
    }
    assignRoleForm.user_id = selectedUser.value.id;
    assignRoleForm.role_name = roleName;
    const schoolId = selectedSchool.value && typeof selectedSchool.value.id === 'number' ? selectedSchool.value.id : null;
    if (!schoolId) {
        assignRoleError.value = 'No school selected.';
        return;
    }
    assignRoleForm.school_id = schoolId;
    assigningRole.value = true;
    assignRoleForm.post('/settings/user-management/assign-role', {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props.flash as any;
            if (flash) {
                if (flash.warning) toast.warning(flash.warning);
                if (flash.success) toast.success(flash.success);
                if (flash.info) toast.info(flash.info);
            } else {
                toast.success('Role assigned successfully');
            }
            // Update local state, close modal, reset form
            closeAssignRoleModal();
            selectedUser.value = undefined;
            assignRoleForm.role_name = '';
        },
        onError: (errors) => {
            assignRoleError.value = Object.values(errors).flat().join(' ');
            Object.values(errors).flat().forEach(message => toast.error(message));
        },
        onFinish: () => {
            assigningRole.value = false;
            assignRoleForm.reset();
            assignRoleForm.user_id = null;
            assignRoleForm.role_name = '';
            assignRoleForm.school_id = null;
        }
    });
};

// Remove role
const removeRoleForm = useForm({
    user_id: null as number | null,
    role_id: null as number | null,
});

const removeRole = (userId: number, roleId: number) => {
    removeRoleForm.user_id = userId;
    removeRoleForm.role_id = roleId;

    removeRoleForm.delete('/settings/user-management/remove-role', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Role removed successfully');
            // Update local state
            const userIndex = users.value.findIndex((u: any) => u.id === userId);
            if (userIndex !== -1) {
                const removedRole = users.value[userIndex].roles.find((r: any) => r.id === roleId);
                if (removedRole) {
                    // Remove from roles array
                    users.value[userIndex].roles = users.value[userIndex].roles.filter((r: any) => r.id !== roleId);

                    // Remove from roles_by_school
                    if (users.value[userIndex].roles_by_school) {
                        Object.keys(users.value[userIndex].roles_by_school!).forEach(schoolName => {
                            if (users.value[userIndex].roles_by_school![schoolName]) {
                                users.value[userIndex].roles_by_school![schoolName] = users.value[userIndex].roles_by_school![schoolName].filter((r: any) => r.id !== roleId);
                            }
                        });

                        // Remove empty school entries
                        Object.keys(users.value[userIndex].roles_by_school!).forEach(schoolName => {
                            if (users.value[userIndex].roles_by_school![schoolName] && users.value[userIndex].roles_by_school![schoolName].length === 0) {
                                delete users.value[userIndex].roles_by_school![schoolName];
                            }
                        });
                    }
                }
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message));
        },
        onFinish: () => {
            removeRoleForm.reset();
            removeRoleForm.user_id = null;
            removeRoleForm.role_id = null;
        }
    });
};



// Breadcrumbs
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
    },
    {
        title: 'User Management',
        href: '/settings/users',
    },
]

// Remove the watcher for flash messages that uses { immediate: true }
// Only keep toast notifications for success, warning, error, and password retrieval, but do not use { immediate: true }.
const page = usePage()
watch(() => page.props.flash, (flash: any) => {
    if (flash) {
        if (flash.success) toast.success(flash.success);
        if (flash.warning) toast.warning(flash.warning);
        if (flash.error) toast.error(flash.error);
        if (flash.info) toast.info(flash.info);
        if (flash.password_retrieved && flash.message) toast.info(flash.message);
    }
});

// Ensure modal is hidden on navigation and component lifecycle
onMounted(() => {
    showPasswordModal.value = false;
    selectedUserForPassword.value = undefined;
});

onBeforeUnmount(() => {
    showPasswordModal.value = false;
    selectedUserForPassword.value = undefined;
    // Clear any pending timeouts
    Object.values(passwordHideTimeouts.value).forEach(timeoutId => {
        clearTimeout(timeoutId);
    });
    passwordHideTimeouts.value = {};
});

function closeAssignRoleModal() {
    showAssignModal.value = false;
    selectedUser.value = undefined;
}

// Check if user has affiliations with other schools
const hasOtherSchoolAffiliations = (user: User): boolean => {
    if (!user.roles_by_school || Object.keys(user.roles_by_school).length === 0) {
        return false;
    }

    const currentSchoolId = selectedSchool.value?.id;
    if (!currentSchoolId) {
        return false;
    }

    // Check if user has roles in any school other than the currently selected one
    return Object.keys(user.roles_by_school || {}).some(schoolName => {
        const roles = user.roles_by_school?.[schoolName] || [];
        return roles.some(role => role.school_id !== currentSchoolId);
    });
};

// Password visibility toggle with admin verification
const togglePasswordVisibility = async (user: User) => {
    if (!user) return;

    if (!user.show_password) {
        selectedUserForPassword.value = user;
        showPasswordModal.value = true;
    } else {
        hidePassword(user.id);
    }
};

// Hide password after timeout
const hidePassword = (userId: number) => {
    const user = users.value.find(u => u.id === userId);
    if (!user) return;

    user.show_password = false;

    // Clear existing timeout
    if (passwordHideTimeouts.value[userId]) {
        clearTimeout(passwordHideTimeouts.value[userId]);
        delete passwordHideTimeouts.value[userId];
    }
};

// Close password modal
const closePasswordModal = () => {
    showPasswordModal.value = false;
    selectedUserForPassword.value = undefined;
};

// Handle successful password verification
const onPasswordVerified = async () => {
    // Password verified, now fetch and show the user's decrypted password
    if (selectedUserForPassword.value) {
        const user = users.value.find(u => u.id === selectedUserForPassword.value!.id);
        if (user) {
            try {
                const response = await fetch(`/settings/user-management/get-password/${user.id}`);
                const data = await response.json();
                if (data && data.password) {
                    user.decrypted_password = data.password;
                    user.show_password = true;
                    passwordHideTimeouts.value[user.id] = setTimeout(() => {
                        hidePassword(user.id);
                    }, 300000);
                } else if (data && data.error) {
                    toast.error(data.error);
                } else {
                    toast.error('Failed to retrieve password.');
                }
            } catch (error) {
                toast.error('Failed to retrieve password.');
            }
        }
    }
    closePasswordModal();
};

function handleResetPassword(userId: number) {
    resetUserId.value = userId;
    showPasswordResetModal.value = true;
}

function onPasswordResetSuccess(newPassword: string) {
    newPasswordShown.value = newPassword;
    showPasswordResetModal.value = false;
    // Optionally, show a toast or modal with the new password
    toast.success('Password reset successfully. New password: ' + newPassword);
}


</script>