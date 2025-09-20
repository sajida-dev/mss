<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import Collapsible from '@/components/ui/collapsible/Collapsible.vue'
import CollapsibleTrigger from '@/components/ui/collapsible/CollapsibleTrigger.vue'
import CollapsibleContent from '@/components/ui/collapsible/CollapsibleContent.vue'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import Switch from '@/components/ui/switch/Switch.vue'
import { toast } from 'vue3-toastify'
import { Trash2, Plus, Loader, Check } from 'lucide-vue-next'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import { type BreadcrumbItem } from '@/types';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { useSchoolStore } from '@/stores/school'
import { storeToRefs } from 'pinia'

interface Role {
    id: number
    name: string
    school_id?: number
    school_name?: string
    permissions: { id: number; name: string }[]
}
interface Permission {
    id: number
    name: string
}

const roles = ref<Role[]>([])
const permissions = ref<Permission[]>([])
const loading = ref(true)
const expandedRole = ref<number | null>(null)
const updating = ref<{ [key: string]: boolean }>({})
const updated = ref<{ [key: string]: boolean }>({})
const errorMsg = ref('')
const permissionForms = ref<{ [roleId: number]: any }>({})
const showAddRole = ref(false)
const showAddPermission = ref(false)
const showAddPermissionModal = ref(false)
const newRoleForm = useForm({ name: '', school_id: null as number | null })
const newPermissionForm = useForm({ name: '', userName: '', phoneNumber: '' })
const deletingRoleId = ref<number | null>(null)
const deletingPermissionId = ref<number | null>(null)
const activeTab = ref<'roles' | 'permissions'>('roles')
const schoolStore = useSchoolStore()
const { selectedSchool, schools } = storeToRefs(schoolStore)

// Filter roles by selected school
const filteredRoles = computed(() => {
    if (!selectedSchool.value) return roles.value;
    return roles.value.filter(role =>
        role.school_id === selectedSchool.value?.id || !role.school_id // Include global roles too
    );
});

// Add a placeholder for userIsSuperAdmin (replace with real logic as needed)
const userIsSuperAdmin = true // TODO: Replace with real check from auth/props

const fetchRolesAndPermissions = async () => {
    loading.value = true
    const [rolesRes, permsRes] = await Promise.all([
        fetch('/admin/roles'),
        fetch('/admin/permissions'),
    ])
    roles.value = await rolesRes.json()
    permissions.value = await permsRes.json()
    loading.value = false
}
onMounted(() => {
    fetchRolesAndPermissions();

    if (userIsSuperAdmin) {
        fetch('/admin/schools')
            .then(res => res.json())
            .then(response => {
                const schoolsArray = response.schools;
                if (!Array.isArray(schoolsArray)) {
                    console.error('Expected an array for schools, but got:', response);
                    return;
                }

                schools.value = schoolsArray;

                const lastId = localStorage.getItem('selectedSchoolId');
                const last = schoolsArray.find((s: { id: number }) => s.id === Number(lastId));

                schoolStore.setSchool(last || schoolsArray[0]);
            })
            .catch(err => {
                console.error('Error fetching schools:', err);
            });
    }
});


const hasPermission = (role: Role, permId: number) => {
    return Array.isArray(role.permissions) && role.permissions.some(p => p.id === permId)
}

const getPermissionForm = (role: Role) => {
    if (!permissionForms.value[role.id]) {
        permissionForms.value[role.id] = useForm({
            permissions: Array.isArray(role.permissions) ? role.permissions.map(p => p.id) : []
        })
    }
    return permissionForms.value[role.id]
}

// const togglePermission = async (role: Role, perm: Permission) => {

//     const key = `${role.id}-${perm.id}`
//     updating.value[key] = true
//     errorMsg.value = ''

//     const form = getPermissionForm(role)
//     const hasPerm = hasPermission(role, perm.id)
//     if (hasPerm) {
//         form.permissions = form.permissions.filter((id: number) => id !== perm.id)
//     } else {
//         form.permissions = [...form.permissions, perm.id]
//     }

//     // Always send an array of permission IDs
//     const payload = {
//         permissions: form.permissions
//     };

//     form.put(`/admin/roles/${role.id}`, {
//         preserveScroll: true,
//         only: ['permissions'], // Only send permissions field
//         onSuccess: (response: any) => {
//             // Update the role in the local roles array
//             if (response && response.props && response.props.role) {
//                 const updatedRole = response.props.role
//                 const idx = roles.value.findIndex(r => r.id === role.id)
//                 if (idx !== -1) {
//                     roles.value[idx].permissions = updatedRole.permissions
//                     // Sync the cached form's permissions to backend
//                     if (permissionForms.value[role.id]) {
//                         permissionForms.value[role.id].permissions = updatedRole.permissions.map((p: any) => p.id)
//                     }
//                 }
//             } else {
//                 // fallback: update from form
//                 const idx = roles.value.findIndex(r => r.id === role.id)
//                 if (idx !== -1) {
//                     roles.value[idx].permissions = permissions.value.filter(p => form.permissions.includes(p.id))
//                 }
//             }
//             if (!hasPerm) {
//                 toast.success(`'${capitalize(perm.name)}' assigned to '${capitalize(role.name)}'`)
//             } else {
//                 toast.info(`'${capitalize(perm.name)}' removed from '${capitalize(role.name)}'`)
//             }
//             updating.value[key] = false
//             updated.value[key] = true
//             setTimeout(() => { updated.value[key] = false }, 1200)
//             router.visit(route('roles.settings'), {
//                 preserveState: true,
//                 preserveScroll: true,
//             })
//         },
//         onError: (errors: any) => {
//             updating.value[key] = false
//             errorMsg.value = errors?.permissions?.[0] || 'Failed to update permissions. Please refresh and try again.'
//             toast.error(errorMsg.value)
//         }
//     })
// }

const togglePermission = async (role: Role, perm: Permission) => {
    const key = `${role.id}-${perm.id}`

    // Start updating for this key
    updating.value[key] = true
    errorMsg.value = ''

    const form = getPermissionForm(role)
    const hasPerm = hasPermission(role, perm.id)

    if (hasPerm) {
        form.permissions = form.permissions.filter((id: number) => id !== perm.id)
    } else {
        form.permissions = [...form.permissions, perm.id]
    }

    const payload = { permissions: form.permissions }

    form.put(`/admin/roles/${role.id}`, {
        preserveScroll: true,
        only: ['permissions'],
        onSuccess: (response: any) => {
            // Clear loading state for this key
            updating.value[key] = false

            // Show updated success for this key
            updated.value[key] = true
            setTimeout(() => {
                updated.value[key] = false
            }, 1200)

            // Update local roles array accordingly
            if (response && response.props && response.props.role) {
                const updatedRole = response.props.role
                const idx = roles.value.findIndex(r => r.id === role.id)
                if (idx !== -1) {
                    roles.value[idx].permissions = updatedRole.permissions
                    if (permissionForms.value[role.id]) {
                        permissionForms.value[role.id].permissions = updatedRole.permissions.map((p: any) => p.id)
                    }
                }
            } else {
                const idx = roles.value.findIndex(r => r.id === role.id)
                if (idx !== -1) {
                    roles.value[idx].permissions = permissions.value.filter(p => form.permissions.includes(p.id))
                }
            }

            // Toast notifications
            if (!hasPerm) {
                toast.success(`'${capitalize(perm.name)}' assigned to '${capitalize(role.name)}'`)
            } else {
                toast.info(`'${capitalize(perm.name)}' removed from '${capitalize(role.name)}'`)
            }

            // Optional: No need to re-visit route here, it causes re-render
            // router.visit(route('roles.settings'), {
            //     preserveState: true,
            //     preserveScroll: true,
            // })
        },
        onError: (errors: any) => {
            updating.value[key] = false
            errorMsg.value = errors?.permissions?.[0] || 'Failed to update permissions. Please refresh and try again.'
            toast.error(errorMsg.value)
        }
    })
}


function capitalize(str: string) {
    return str.charAt(0).toUpperCase() + str.slice(1)
}

const addRole = () => {
    // Always use the selected school from the store
    newRoleForm.school_id = selectedSchool.value?.id || null
    newRoleForm.post('/admin/roles', {
        preserveScroll: true,
        onSuccess: (response) => {
            toast.success('Role added!')
            showAddRole.value = false
            newRoleForm.reset()
            if (response && response.props && response.props.role) {
                roles.value.push(response.props.role as Role)
            } else {
                fetch('/admin/roles').then(res => res.json()).then(data => { roles.value = data })
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message))
        }
    })
}
const addPermission = () => {
    newPermissionForm.post('/admin/permissions', {
        preserveScroll: true,
        onSuccess: (response) => {
            toast.success('Permission added!')
            showAddPermissionModal.value = false
            newPermissionForm.reset()
            if (response && response.props && response.props.permission) {
                permissions.value.push(response.props.permission as Permission)
            } else {
                fetch('/admin/permissions').then(res => res.json()).then(data => { permissions.value = data })
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message))
        }
    })
}
const confirmDeleteRole = (id: number) => { deletingRoleId.value = id }
const confirmDeletePermission = (id: number) => { deletingPermissionId.value = id }
const deleteRole = () => {
    if (!deletingRoleId.value) return
    useForm<{ id: number; name: string }>({ id: deletingRoleId.value, name: '' }).delete(`/admin/roles/${deletingRoleId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Role deleted!')
            roles.value = roles.value.filter(r => r.id !== deletingRoleId.value)
            deletingRoleId.value = null
        },
        onError: () => toast.error('Failed to delete role!')
    })
}
const deletePermission = () => {
    if (!deletingPermissionId.value) return
    useForm<{ id: number; name: string }>({ id: deletingPermissionId.value, name: '' }).delete(`/admin/permissions/${deletingPermissionId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Permission deleted!')
            permissions.value = permissions.value.filter(p => p.id !== deletingPermissionId.value)
            deletingPermissionId.value = null
        },
        onError: () => toast.error('Failed to delete permission!')
    })
}


const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Roles and Permissions settings',
        href: '/settings/roles-permissions',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Roles & Permissions" />
        <SettingsLayout>
            <div class="max-w-2xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
                <h1 class="text-2xl font-bold mb-6">Roles & Permissions</h1>
                <div class="mb-6 flex gap-4 border-b border-gray-200 dark:border-neutral-800">
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'roles' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'roles'">Roles</button>
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'permissions' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'permissions'">Permissions</button>
                </div>
                <div v-if="activeTab === 'roles'">
                    <p class="mb-4 text-sm text-muted-foreground">Roles let you group permissions and assign them to
                        users. Expand a role to manage its permissions.</p>

                    <!-- School Context Indicator -->
                    <div
                        class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <span class="font-medium">Current School Context:</span>
                            {{ selectedSchool?.name || 'No school selected' }}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            Showing roles for this school and global roles
                        </div>
                    </div>

                    <div class="flex justify-end mb-4">
                        <button @click="showAddRole = true"
                            class="flex items-center gap-2 px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition">
                            <Plus class="w-4 h-4" /> Add Role
                        </button>
                    </div>
                    <div v-if="showAddRole" class="w-lg mb-4">
                        <form @submit.prevent="addRole" class="flex gap-2 items-center ">
                            <!-- School Context Info -->
                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                Creating role for: <span class="font-medium text-blue-600 dark:text-blue-400">{{
                                    selectedSchool?.name || 'No school selected' }}</span>
                            </div>

                            <!-- <Label for="role_name">Role Name</Label> -->
                            <Input id="role_name" v-model="newRoleForm.name" placeholder="Role name"
                                class="rounded border px-3 py-1 text-sm" />
                            <InputError :message="newRoleForm.errors.name" />
                            <InputError :message="newRoleForm.errors.school_id" />
                            <Button type="submit" class="px-3 py-1">Add</Button>
                            <Button type="button" @click="showAddRole = false" variant="outline">Cancel</Button>
                        </form>
                    </div>
                    <div v-if="loading" class="text-center py-8 text-muted-foreground">Loading roles...</div>
                    <div v-else class="space-y-4">
                        <Collapsible v-for="role in filteredRoles" :key="role.id">
                            <CollapsibleTrigger
                                class="w-full flex items-center justify-between px-4 py-3 rounded cursor-pointer bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition">
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-base">{{ capitalize(role.name) }}</span>
                                    <span
                                        class="text-xs px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800">
                                        {{ role.school_name || 'Global' }}
                                    </span>
                                </div>
                                <div class="flex flex-row gap-3 items-center">
                                    <span class="text-xs text-muted-foreground">
                                        {{ Array.isArray(role.permissions) ? role.permissions.length : 0 }} permission{{
                                            Array.isArray(role.permissions) && role.permissions.length === 1 ? '' : 's' }}
                                    </span>
                                    <button
                                        v-if="!['superadmin', 'admin', 'teacher', 'principal'].includes(role.name.toLowerCase())"
                                        @click.stop="confirmDeleteRole(role.id)"
                                        class="ml-2 p-1 rounded hover:bg-red-100 dark:hover:bg-red-900 transition">
                                        <Trash2 class="w-4 h-4 text-red-500" />
                                    </button>
                                </div>

                            </CollapsibleTrigger>
                            <CollapsibleContent
                                class="px-4 py-3 border-t border-gray-200 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    <div v-for="perm in permissions" :key="perm.id" class="flex items-center gap-3">
                                        <Switch :checked="hasPermission(role, perm.id)"
                                            :disabled="updating[`${role.id}-${perm.id}`]"
                                            @update:checked="() => togglePermission(role, perm)" class="size-5" />
                                        <span class="text-xs">{{ capitalize(perm.name) }}</span>
                                        <span v-if="updating[`${role.id}-${perm.id}`]"
                                            class="text-xl text-muted-foreground ml-2">
                                            <Loader class="w-4 h-4 animate-spin" />
                                        </span>
                                        <span v-else-if="updated[`${role.id}-${perm.id}`]"
                                            class="text-xl text-green-600 dark:text-green-400 ml-2">
                                            <Check class="w-4 h-4" />
                                        </span>

                                    </div>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    </div>
                </div>
                <div v-else-if="activeTab === 'permissions'">
                    <p class="mb-4 text-sm text-muted-foreground">Permissions control what users can do. Add, view, or
                        delete permissions here.</p>
                    <div class="flex justify-end mb-4">
                        <button @click="showAddPermissionModal = true"
                            class="flex items-center gap-2 px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition">
                            <Plus class="w-4 h-4" /> Add Permission
                        </button>
                    </div>
                    <Dialog v-model:open="showAddPermissionModal">
                        <DialogContent>
                            <DialogTitle>Add Permission</DialogTitle>
                            <form @submit.prevent="addPermission" class="flex flex-col gap-3">
                                <InputError
                                    :message="newPermissionForm.errors.name || errorMsg || $page.props.errors?.name"
                                    class="mb-2" />
                                <Label for="perm_name">Permission Name</Label>
                                <Input id="perm_name" v-model="newPermissionForm.name" placeholder="Permission name"
                                    class="rounded border px-3 py-2 text-sm" />
                                <InputError :message="newPermissionForm.errors.name" />
                                <div class="flex gap-2 justify-end">
                                    <Button type="button" @click="showAddPermissionModal = false"
                                        variant="outline">Cancel</Button>
                                    <Button type="submit">Add</Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                    <div v-if="loading" class="text-center py-8 text-muted-foreground">Loading permissions...</div>
                    <div v-else class="space-y-2">
                        <div v-for="perm in permissions" :key="perm.id"
                            class="flex items-center gap-3 bg-gray-50 dark:bg-neutral-900 rounded px-4 py-2">
                            <span class="text-sm flex-1">{{ capitalize(perm.name) }}</span>
                            <button @click.stop="confirmDeletePermission(perm.id)"
                                class="ml-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-900 transition">
                                <Trash2 class="w-4 h-4 text-red-500" />
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Delete Role Modal -->
                <div v-if="deletingRoleId !== null"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto">
                        <h3 class="text-lg font-semibold mb-4">Delete Role?</h3>
                        <p class="mb-4">Are you sure you want to delete this role? This action cannot be undone.</p>
                        <div class="flex gap-2 justify-end">
                            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-800"
                                @click="deletingRoleId = null">Cancel</button>
                            <button class="px-4 py-2 rounded bg-red-600 text-white" @click="deleteRole">Delete</button>
                        </div>
                    </div>
                </div>
                <!-- Delete Permission Modal -->
                <div v-if="deletingPermissionId !== null"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto">
                        <h3 class="text-lg font-semibold mb-4">Delete Permission?</h3>
                        <p class="mb-4">Are you sure you want to delete this permission? This action cannot be undone.
                        </p>
                        <div class="flex gap-2 justify-end">
                            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-800"
                                @click="deletingPermissionId = null">Cancel</button>
                            <button class="px-4 py-2 rounded bg-red-600 text-white"
                                @click="deletePermission">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>