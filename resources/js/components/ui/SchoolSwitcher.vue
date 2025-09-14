<script setup lang="ts">
import { useSchoolStore } from '@/stores/school';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ChevronDown, Check } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const schoolStore = useSchoolStore();
const { schools, selectedSchool } = storeToRefs(schoolStore);

defineProps<{ isSuperAdmin: boolean }>();

const emit = defineEmits(['switched']);

// RULE: Always use Inertia router methods for navigation and form submissions in Inertia-powered Vue apps.
function switchSchool(school: any) {
    if (school.id !== selectedSchool.value?.id) {
        schoolStore.setSchool(school);
        router.post('/admin/set-active-school', { school_id: school.id }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['schools', 'activeSchoolId'] });
                emit('switched', school);
            },
            onError: (errors) => {
                console.error('School switch failed:', errors); // Debug log
            }
        });
    } else {
        console.log('School already selected, no switch needed'); // Debug log
    }
}

function getInitials(name: string) {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function handleSchoolAdded(event: CustomEvent) {
    const newSchool = event.detail;
    // Only add if not already present
    if (!schools.value.some(s => s.id === newSchool.id)) {
        schools.value.push(newSchool);
        // If this is the first school, select it
        if (schools.value.length === 1) {
            schoolStore.setSchool(newSchool);
        }
    }
}

watch(schools, (newSchools) => {
    if (newSchools.length === 0) {
        schoolStore.setSchool(null);
    } else if (newSchools.length === 1) {
        schoolStore.setSchool(newSchools[0]);
    } else if (selectedSchool.value && !newSchools.some(s => s.id === selectedSchool.value?.id)) {
        schoolStore.setSchool(newSchools[0]);
    }
});

let pollInterval: number | undefined;
onMounted(() => {
    window.addEventListener('school-added', handleSchoolAdded as EventListener);

});
onUnmounted(() => {
    window.removeEventListener('school-added', handleSchoolAdded as EventListener);
    if (pollInterval) clearInterval(pollInterval);
});
</script>
<template>
    <div v-if="isSuperAdmin" class="fixed z-50" style="top:72px; right:2rem;">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <div
                    class="flex items-center gap-3 bg-white dark:bg-neutral-900 shadow-lg rounded-xl px-4 py-2 cursor-pointer min-w-[220px] border border-gray-200 dark:border-neutral-700">
                    <Avatar class="h-10 w-10">
                        <AvatarImage
                            :src="selectedSchool?.logo_url ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s'"
                            :alt="selectedSchool?.name ?? 'No School Selected'" />
                    </Avatar>
                    <div class="flex flex-col min-w-0">
                        <span class="font-semibold text-base truncate">
                            {{ selectedSchool?.name || (schools.length === 0 ? 'No school' : '') }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ selectedSchool?.phone || '' }}
                        </span>
                    </div>
                    <ChevronDown class="ml-auto text-gray-400 dark:text-gray-500" />
                </div>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-72 mt-2 p-0">
                <template v-if="schools.length === 0">
                    <div class="px-4 py-3 text-center text-gray-400 dark:text-gray-500 text-sm">No school</div>
                </template>
                <template v-else>
                    <DropdownMenuItem v-for="school in schools" :key="school.id" @click="switchSchool(school)"
                        :class="[school.id === selectedSchool?.id ? 'bg-primary/10 dark:bg-primary/20' : '', 'flex items-center gap-3 px-4 py-2 cursor-pointer']">
                        <Avatar class="h-8 w-8">
                            <AvatarImage
                                :src="school.logo_url ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSVJvdr9q2sYXdV5Qn8j47CV7i1nDNK-pIew&s'"
                                :alt="school.name" />
                            <!-- <AvatarFallback>{{ getInitials(school.name) }}</AvatarFallback> -->
                        </Avatar>
                        <div class="flex flex-col min-w-0">
                            <span class="font-medium truncate">{{ school.name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ school.phone || ''
                            }}</span>
                        </div>
                        <Check v-if="school.id === selectedSchool?.id" class="ml-auto text-primary" />
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>