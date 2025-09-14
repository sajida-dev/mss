<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute the avatar source
const avatarSrc = computed(() => {
    // Use the profile_photo_url accessor from the User model
    return props.user.profile_photo_url || '/storage/default-profile.png';
});

// Always show avatar since we have a default
const showAvatar = computed(() => true);
</script>

<template>
    <Avatar class="group/menu-item">
        <AvatarImage :src="avatarSrc" :alt="user.name" class="object-cover w-10 h-10 " />
        <AvatarFallback class="text-white  bg-gradient-to-br from-blue-900 to-purple-900 font-semibold">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>
    <div class="grid flex-1 text-left  text-sm leading-tight">
        <span class="truncate font-semibold text-neutral-400">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
        <span v-else class="truncate text-xs text-neutral-400">Administrator</span>
    </div>
</template>
