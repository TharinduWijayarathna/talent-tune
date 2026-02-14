<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    CreditCard,
    FileText,
    GraduationCap,
    Layers,
    LayoutGrid,
    Plus,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

// Determine role based on user's role from auth data
const currentRole = computed(() => {
    const user = page.props.auth?.user as any;
    return user?.role || null;
});

const mainNavItems = computed<NavItem[]>(() => {
    const role = currentRole.value;

    if (role === 'student') {
        return [
            {
                title: 'Dashboard',
                href: '/student/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Viva Sessions',
                href: '/student/vivas',
                icon: BookOpen,
            },
        ];
    }

    if (role === 'lecturer') {
        return [
            {
                title: 'Dashboard',
                href: '/lecturer/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Create Viva',
                href: '/lecturer/vivas/create',
                icon: Plus,
            },
            {
                title: 'My Sessions',
                href: '/lecturer/vivas',
                icon: FileText,
            },
        ];
    }

    if (role === 'institution') {
        return [
            {
                title: 'Dashboard',
                href: '/institution/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Batches',
                href: '/institution/batches',
                icon: Layers,
            },
            {
                title: 'Lecturers',
                href: '/institution/lecturers',
                icon: Users,
            },
            {
                title: 'Students',
                href: '/institution/students',
                icon: GraduationCap,
            },
        ];
    }

    if (role === 'admin') {
        return [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Institutions',
                href: '/admin/institutions',
                icon: Building2,
            },
            {
                title: 'User Management',
                href: '/admin/users/students',
                icon: Users,
                children: [
                    {
                        title: 'Admin Management',
                        href: '/admin/users/institution-admins',
                    },
                    {
                        title: 'Student Management',
                        href: '/admin/users/students',
                    },
                    {
                        title: 'Lecturer Management',
                        href: '/admin/users/lecturers',
                    },
                ],
            },
            {
                title: 'Payments',
                href: '/admin/payments',
                icon: CreditCard,
            },
        ];
    }

    // Default navigation
    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link
                            :href="mainNavItems[0]?.href || dashboard()"
                            class="flex w-full items-center justify-center"
                        >
                            <AppLogo sidebar />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
