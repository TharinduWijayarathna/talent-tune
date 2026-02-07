<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Building2,
    ChevronLeft,
    ChevronRight,
    Mail,
    Search,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface InstitutionRef {
    id: number;
    name: string;
    slug: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string;
    student_id: string | null;
    employee_id: string | null;
    batch: string | null;
    department: string | null;
    institution: InstitutionRef | null;
    email_verified_at: string | null;
    created_at: string;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedUsers {
    data: UserItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    first_page_url: string;
    last_page_url: string;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginatorLink[];
}

interface Props {
    users: PaginatedUsers;
    filters: { search?: string; role?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Users', href: '/admin/users' },
];

const searchQuery = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? '');

const applyFilters = () => {
    router.get(
        '/admin/users',
        {
            search: searchQuery.value || undefined,
            role: roleFilter.value || undefined,
            page: 1,
        },
        { preserveState: true },
    );
};

const roleLabels: Record<string, string> = {
    student: 'Student',
    lecturer: 'Lecturer',
    institution: 'Institution',
    admin: 'Admin',
};
</script>

<template>
    <Head title="Manage Users - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">User Management</h1>
                    <p class="text-muted-foreground">
                        Manage all users across the platform
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Users</CardTitle
                        >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ users.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >This page</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ users.from ?? 0 }} – {{ users.to ?? 0 }} of
                            {{ users.total }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative min-w-[200px] flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, email, student/employee ID..."
                        class="pl-10"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <select
                    v-model="roleFilter"
                    class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <option value="">All roles</option>
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="institution">Institution</option>
                    <option value="admin">Admin</option>
                </select>
                <Button @click="applyFilters">Apply</Button>
            </div>

            <!-- Users List -->
            <Card>
                <CardHeader>
                    <CardTitle>Users</CardTitle>
                    <CardDescription
                        >All registered users (students, lecturers,
                        institutions)</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="user in users.data"
                            :key="user.id"
                            class="flex items-start justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <h3 class="font-semibold">
                                            {{ user.name }}
                                        </h3>
                                        <p
                                            class="flex items-center gap-1 text-sm text-muted-foreground"
                                        >
                                            <Mail class="inline h-4 w-4" />
                                            {{ user.email }}
                                        </p>
                                    </div>
                                    <Badge variant="secondary">{{
                                        roleLabels[user.role] ?? user.role
                                    }}</Badge>
                                </div>
                                <div
                                    class="flex flex-wrap gap-4 text-sm text-muted-foreground"
                                >
                                    <span v-if="user.student_id"
                                        >Student ID: {{ user.student_id }}</span
                                    >
                                    <span v-if="user.employee_id"
                                        >Employee ID:
                                        {{ user.employee_id }}</span
                                    >
                                    <span v-if="user.batch"
                                        >Batch: {{ user.batch }}</span
                                    >
                                    <span v-if="user.department"
                                        >Dept: {{ user.department }}</span
                                    >
                                </div>
                                <div
                                    v-if="user.institution"
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Building2 class="h-4 w-4" />
                                    {{ user.institution.name }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Joined
                                    {{
                                        new Date(
                                            user.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="users.data.length === 0"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <Users class="mx-auto mb-4 h-12 w-12 opacity-50" />
                            <p>No users found.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="users.last_page > 1"
                        class="mt-6 flex items-center justify-between border-t pt-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            Page {{ users.current_page }} of
                            {{ users.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-if="users.prev_page_url"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="users.prev_page_url">
                                    <ChevronLeft class="mr-1 h-4 w-4" />
                                    Previous
                                </Link>
                            </Button>
                            <Button
                                v-if="users.next_page_url"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="users.next_page_url">
                                    Next
                                    <ChevronRight class="ml-1 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
