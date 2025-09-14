<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Search, AlignJustify, Bell, Monitor, Moon, Sun, Search as SearchIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import { useAppearance } from '@/composables/useAppearance';
import ThemeSwitch from '@/components/ThemeSwitch.vue';
import SchoolSwitcher from '@/components/ui/SchoolSwitcher.vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''),
);

const isSuperAdmin = computed(() => {
    const roles = auth.value?.user?.roles || [];
    return roles.some((r: any) => r.name === 'superadmin');
});

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [

];

const notifications = ref([
    { id: 1, title: 'Welcome!', body: 'Thanks for joining the platform.', time: new Date().toISOString(), link: '/dashboard', read: false },
    { id: 2, title: 'Profile Updated', body: 'Your profile was updated successfully.', time: new Date().toISOString(), link: '/settings/profile', read: false },
]);

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

function markNotificationRead(notificationOrId: any) {
    const id = typeof notificationOrId === 'object' ? notificationOrId.id : notificationOrId;
    const n = notifications.value.find(n => n.id === id);
    if (n) n.read = true;
}

const { appearance, updateAppearance } = useAppearance();
const searchQuery = ref('');

const themeTabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

function onSearch() {
    // You can emit or handle search here
    // For now, just log
    if (searchQuery.value.trim()) {
        // Implement your search logic or Inertia visit here
        // router.get(route('search'), { q: searchQuery.value })
        // For now:
        console.log('Search:', searchQuery.value);
    }
}
</script>
<template>
    <div>
        <div class="border-b border-sidebar-border/80 ">
            <div class="mx-auto flex h-16 items-center justify-between px-4 md:max-w-7xl">
                <div class="flex items-center gap-2 flex-1 relative">
                    <form @submit.prevent="onSearch" class="relative w-full max-w-xs hidden md:block">
                        <input v-model="searchQuery" type="text" placeholder="Search..."
                            class="w-full rounded-md border border-gray-200   py-2 pl-9 pr-3 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400  focus:outline-none focus:ring-2 focus:ring-primary/40 transition" />
                        <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-gray-400 " />
                    </form>
                    <ThemeSwitch compact class="ml-2" />
                    <NotificationDropdown :notifications="notifications" @read="markNotificationRead"
                        @mark-as-read="markNotificationRead" />
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button variant="ghost" size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary">
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.profile_photo_url" :src="auth.user.profile_photo_url"
                                        class="w-full h-full object-cover" :alt="auth.user.name" />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth.user.initials || getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <div class="ml-4 min-w-[180px] absolute">
                        <SchoolSwitcher :isSuperAdmin="isSuperAdmin" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
