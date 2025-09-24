<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import AppLogo from './AppLogo.vue';

const { can } = usePermissions();
// Import the chosen icons from lucide‑vue‑next
import {
    LayoutDashboard,
    School,
    Calendar,
    Layers,
    BookOpen,
    UserPlus,
    User,
    GraduationCap,
    CreditCard,
    FileText,
    CalendarCheck,
    ClipboardList,
    ChartPie,
    Award,
    ChartColumn,
} from 'lucide-vue-next';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Manage Schools',
        href: '/schools',
        icon: School,
        permission: 'manage-schools',
    },
    {
        title: 'Manage Academic Years',
        href: '/admin/academic-years',
        icon: Calendar,
        permission: 'manage-academic-years',
    },
    {
        title: 'Manage Classes & Sections',
        href: '/manage/classes-sections',
        icon: Layers,
        permission: 'manage-classes',
    },
    {
        title: 'Manage Subjects',
        href: '/subjects',
        icon: BookOpen,
        permission: 'manage-subjects',
    },
    {
        title: 'Manage Admissions',
        href: '/admissions',
        icon: UserPlus,
        permission: 'manage-admissions',
    },
    {
        title: 'Manage Teachers',
        href: '/teachers',
        icon: User,  // or Users if you want multiple figure
        permission: 'manage-teachers',
    },
    {
        title: 'Manage Students',
        href: '/students',
        icon: GraduationCap,
        permission: 'manage-students',
    },
    {
        title: 'Manage Fees',
        href: '/fees',
        icon: CreditCard,
        permission: 'manage-fees',
    },
    {
        title: 'Manage Papers',
        href: '/papersquestions',
        icon: FileText,
        permission: 'manage-papers',
    },
    {
        title: 'Manage Attendance',
        href: '/attendance',
        icon: CalendarCheck,
        permission: 'manage-attendance',
    },
    {
        title: 'Manage Exams',
        href: '/exams',
        icon: ClipboardList,
        permission: 'manage-exams',
        matchRoutes: [
            '/exams',
            '/exam-types',
            '/exam-papers',
        ],
    },
    {
        title: 'Manage Results',
        href: '/exam-results',
        icon: ChartPie,
        permission: 'manage-exam-results',
    },
    {
        title: 'Manage Reports',
        href: '/reports',
        icon: ChartColumn,
        permission: 'manage-reports',
    },
    {
        title: 'Manage Certificates',
        href: '/certificates',
        icon: Award,
        permission: 'manage-certificates',
    },
];
const footerNavItems: NavItem[] = [];

const filteredNavItems = mainNavItems.filter((item) => {
    // If no permission specified, show by default
    if (!item.permission) return true;
    return can(item.permission);
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-purple-900 text-white">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="filteredNavItems" />
        </SidebarContent>
        <SidebarFooter>
            <SidebarGroup class="px-2 py-0">
                <NavFooter :items="footerNavItems" />
            </SidebarGroup>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
