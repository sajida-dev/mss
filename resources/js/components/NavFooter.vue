<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
const page = usePage();
</script>

<template>
    <SidebarGroup :class="`text-white group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton class=" text-neutral-100 hover:text-neutral-200 " as-child
                        :is-active="item.href === page.url" :tooltip="item.title">
                        <Link :href="item.href">
                        <component :is="item.icon" class="size-4 text-white" />
                        <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
