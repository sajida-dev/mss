<template>
    <Dialog :model-value="modelValue" @update:modelValue="val => $emit('update:modelValue', val)">
        <div v-if="user" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div
                class="relative z-50 bg-white dark:bg-neutral-900 p-6 rounded-lg shadow-lg min-w-[400px] max-w-md mx-auto w-full">
                <h2 class="text-lg font-bold mb-4 text-center">Assign Role</h2>
                <!-- User Info -->
                <div class="flex items-center gap-3 mb-4 border-b pb-3">
                    <img v-if="user.profile_photo_url" :src="user.profile_photo_url" alt="User Avatar"
                        class="w-10 h-10 rounded-full border object-cover" />
                    <div>
                        <div class="font-semibold text-base">{{ user.name || 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ user.email || 'N/A' }}</div>
                    </div>
                </div>
                <!-- Current School Context -->
                <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <div class="flex items-center justify-between gap-2">
                        <span class="block text-xs font-semibold text-purple-700 dark:text-purple-300">Current School
                            Context:</span>
                        <span class="text-sm text-purple-800 dark:text-purple-200">
                            {{ school?.name || 'No school selected' }}</span>
                    </div>
                    <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">Roles will be assigned for this
                        school</div>
                </div>

                <!-- Role Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Select Role to Assign</label>
                    <select v-model="roleName"
                        class="w-full px-3 py-2 rounded border dark:bg-neutral-800 dark:text-white focus:ring-2 focus:ring-purple-400">
                        <option value="" disabled>Select a role for {{ school?.name || 'selected school' }}</option>
                        <option v-for="role in filteredRoles" :key="role.id" :value="role.name">{{ role.name }}</option>
                    </select>
                    <div v-if="filteredRoles.length === 0" class="text-xs text-orange-600 dark:text-orange-400 mt-1">No
                        roles available for the selected school. Please select a different school or create roles for
                        this school.</div>
                    <div v-if="assignRoleError" class="text-xs text-red-500 mt-1">{{ assignRoleError }}</div>
                </div>
                <!-- Action Buttons -->
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="onCancel" type="button"
                        class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-neutral-600">Cancel</button>
                    <button @click="onAssign" type="button"
                        :disabled="!roleName || filteredRoles.length === 0 || assigningRole"
                        class="px-4 py-2 rounded bg-purple-600 text-white hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg v-if="assigningRole" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ assigningRole ? 'Assigning...' : 'Assign Role' }}
                    </button>
                </div>
                <!-- Assigned Roles by School -->
                <div class="my-4 border-t pt-4">
                    <span class="block text-xs font-semibold text-gray-500 mb-2">Current School Affiliations</span>
                    <div v-if="user && user.roles_by_school && Object.keys(user.roles_by_school).length">
                        <div class="space-y-2">
                            <div v-for="(roles, schoolName) in user.roles_by_school" :key="schoolName"
                                class="flex items-center justify-between px-2 pt-1 bg-gray-50 dark:bg-neutral-800 rounded">
                                <span class="font-medium text-sm">{{ schoolName }}</span>

                                <span v-if="roles.length"
                                    class="text-xs px-2.5 py-1 rounded-full font-medium shadow-sm bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800 mr-1 mb-1">{{
                                        getRoleNames(roles) }}</span>
                                <span v-else class="italic text-xs text-gray-400">No roles</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="italic text-xs text-gray-400 p-2 bg-gray-50 dark:bg-neutral-800 rounded">No
                        school affiliations yet</div>
                </div>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Dialog from './dialog/Dialog.vue';

const props = defineProps({
    modelValue: Boolean,
    user: { type: Object, default: null },
    school: { type: Object, default: null },
    roles: { type: Array, default: () => [] },
    assigningRole: Boolean,
    assignRoleError: String
});
const emit = defineEmits(['update:modelValue', 'assign', 'cancel']);

const roleName = ref('');

const filteredRoles = computed(() => {
    if (!props.roles || !props.school) return [];
    return props.roles.filter(r => r.school_id === props.school.id);
});

watch(() => props.modelValue, (val) => {
    if (!val) roleName.value = '';
});

function getRoleNames(roles) {
    return roles.map(r => r.name).join(', ');
}
function hasOtherSchoolAffiliations(user) {
    if (!user || !user.roles_by_school) return false;
    return Object.keys(user.roles_by_school).some(schoolName => props.school && schoolName !== props.school.name && user.roles_by_school[schoolName].length > 0);
}
function onAssign() {
    emit('assign', roleName.value);
}
function onCancel() {
    emit('cancel');
    emit('update:modelValue', false);
}
</script>