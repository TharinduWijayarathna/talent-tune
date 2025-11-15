<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Users,
    UserPlus,
    Search,
    Edit,
    Trash2,
    Mail,
    Building2,
    User
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Lecturers', href: '/institution/lecturers' },
];

// Mock data
const lecturers = ref([
    {
        id: 1,
        name: 'Dr. John Smith',
        email: 'lecturer1@talenttune.com',
        employee_id: 'EMP001',
        department: 'Computer Science',
        status: 'active',
        totalSessions: 12,
        createdAt: '2024-01-15',
    },
    {
        id: 2,
        name: 'Dr. Sarah Johnson',
        email: 'lecturer2@talenttune.com',
        employee_id: 'EMP002',
        department: 'Software Engineering',
        status: 'active',
        totalSessions: 8,
        createdAt: '2024-01-20',
    },
    {
        id: 3,
        name: 'Dr. Michael Williams',
        email: 'lecturer3@talenttune.com',
        employee_id: 'EMP003',
        department: 'Web Development',
        status: 'active',
        totalSessions: 15,
        createdAt: '2024-01-10',
    },
    {
        id: 4,
        name: 'Dr. Emily Brown',
        email: 'lecturer4@talenttune.com',
        employee_id: 'EMP004',
        department: 'Data Structures',
        status: 'active',
        totalSessions: 10,
        createdAt: '2024-01-18',
    },
    {
        id: 5,
        name: 'Dr. David Davis',
        email: 'lecturer5@talenttune.com',
        employee_id: 'EMP005',
        department: 'Operating Systems',
        status: 'active',
        totalSessions: 9,
        createdAt: '2024-01-22',
    },
]);

const searchQuery = ref('');

const filteredLecturers = computed(() => {
    if (!searchQuery.value) {
        return lecturers.value;
    }

    const query = searchQuery.value.toLowerCase();
    return lecturers.value.filter(lecturer =>
        lecturer.name.toLowerCase().includes(query) ||
        lecturer.email.toLowerCase().includes(query) ||
        lecturer.employee_id.toLowerCase().includes(query) ||
        lecturer.department.toLowerCase().includes(query)
    );
});

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this lecturer?')) {
        // In a real app, this would make an API call
        lecturers.value = lecturers.value.filter(l => l.id !== id);
    }
};
</script>

<template>
    <Head title="Manage Lecturers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Manage Lecturers</h1>
                    <p class="text-muted-foreground">View and manage all lecturers in your institution</p>
                </div>
                <Button as-child>
                    <Link href="/institution/lecturers/add">
                        <UserPlus class="h-4 w-4 mr-2" />
                        Add Lecturer
                    </Link>
                </Button>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by name, email, employee ID, or department..."
                                class="pl-10"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Lecturers List -->
            <Card>
                <CardHeader>
                    <CardTitle>All Lecturers ({{ filteredLecturers.length }})</CardTitle>
                    <CardDescription>List of all lecturers registered in your institution</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="lecturer in filteredLecturers"
                            :key="lecturer.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                    <User class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold">{{ lecturer.name }}</h3>
                                        <Badge variant="default" class="text-xs">
                                            {{ lecturer.status }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <Mail class="h-4 w-4" />
                                            <span>{{ lecturer.email }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Building2 class="h-4 w-4" />
                                            <span>{{ lecturer.department }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>Employee ID: {{ lecturer.employee_id }}</span>
                                        <span>•</span>
                                        <span>{{ lecturer.totalSessions }} sessions</span>
                                        <span>•</span>
                                        <span>Joined: {{ lecturer.createdAt }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/institution/lecturers/${lecturer.id}/edit`">
                                        <Edit class="h-4 w-4 mr-2" />
                                        Edit
                                    </Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="handleDelete(lecturer.id)"
                                    class="text-destructive hover:text-destructive"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div v-if="filteredLecturers.length === 0" class="text-center py-12">
                            <Users class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p class="text-muted-foreground">No lecturers found</p>
                            <p class="text-sm text-muted-foreground mt-2">
                                Try adjusting your search criteria
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

