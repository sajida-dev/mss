<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },

];

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';

// Add User Management link for admins
const user = page.props.auth?.user as any;
const userRoles = (user && Array.isArray(user.roles)) ? user.roles : [];
if (userRoles.some((r: any) => r.name === 'superadmin')) {
    sidebarNavItems.push(
        {
            title: 'Roles & Permissions',
            href: '/settings/roles-permissions',
            permission: 'read-roles',
        },
        {
            title: 'User Management',
            href: '/settings/users',
            permission: 'read-users',
        },
        {
            title: 'Add New User',
            href: '/settings/add-user',
            permission: 'create-users',
        },
    );
}

const filteredSidebarNavItems = sidebarNavItems.filter((item) => {
    if (!item.permission) return true; // Show items without a permission requirement
    return can(item.permission);
});
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-y-0 lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in filteredSidebarNavItems" :key="item.href" variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]" as-child>
                        <Link :href="item.href">
                        {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-5xl">
                <section class="max-w-4xl space-y-6">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
