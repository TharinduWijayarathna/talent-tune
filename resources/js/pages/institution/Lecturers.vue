<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
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
import { ref, computed } from 'vue';

interface Lecturer {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    department: string;
    status: string;
    totalSessions: number;
    created_at: string;
}

const props = defineProps<{
    lecturers: Lecturer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Lecturers', href: '/institution/lecturers' },
];

const searchQuery = ref('');

const filteredLecturers = computed(() => {
    if (!searchQuery.value) {
        return props.lecturers;
    }
    const query = searchQuery.value.toLowerCase();
    return props.lecturers.filter(lecturer =>
        lecturer.name.toLowerCase().includes(query) ||
        lecturer.email.toLowerCase().includes(query) ||
        (lecturer.employee_id && lecturer.employee_id.toLowerCase().includes(query)) ||
        (lecturer.department && lecturer.department.toLowerCase().includes(query))
    );
});

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this lecturer?')) {
        router.delete(`/institution/lecturers/${id}`);
    }
};

const formatDate = (iso: string) => iso ? new Date(iso).toLocaleDateString() : '';
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
                                        <span v-if="lecturer.employee_id">Employee ID: {{ lecturer.employee_id }}</span>
                                        <span v-if="lecturer.employee_id">•</span>
                                        <span>{{ lecturer.totalSessions }} sessions</span>
                                        <span>•</span>
                                        <span>Joined: {{ formatDate(lecturer.created_at) }}</span>
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

