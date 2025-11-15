<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    GraduationCap,
    UserPlus,
    Search,
    Edit,
    Trash2,
    Mail,
    Building2,
    User,
    Users
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Students', href: '/institution/students' },
];

// Mock data
const students = ref([
    {
        id: 1,
        name: 'Alice Johnson',
        email: 'student1@talenttune.com',
        student_id: 'STU001',
        batch: 'CS-2024',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 5,
        createdAt: '2024-01-15',
    },
    {
        id: 2,
        name: 'Bob Williams',
        email: 'student2@talenttune.com',
        student_id: 'STU002',
        batch: 'CS-2024',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 3,
        createdAt: '2024-01-15',
    },
    {
        id: 3,
        name: 'Charlie Brown',
        email: 'student3@talenttune.com',
        student_id: 'STU003',
        batch: 'CS-2024',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 7,
        createdAt: '2024-01-15',
    },
    {
        id: 4,
        name: 'Diana Martinez',
        email: 'student4@talenttune.com',
        student_id: 'STU004',
        batch: 'CS-2024',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 4,
        createdAt: '2024-01-15',
    },
    {
        id: 5,
        name: 'Ethan Davis',
        email: 'student5@talenttune.com',
        student_id: 'STU005',
        batch: 'CS-2024',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 6,
        createdAt: '2024-01-15',
    },
    {
        id: 6,
        name: 'Fiona Wilson',
        email: 'student6@talenttune.com',
        student_id: 'STU006',
        batch: 'CS-2024',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 5,
        createdAt: '2024-01-15',
    },
    {
        id: 7,
        name: 'Kevin White',
        email: 'student11@talenttune.com',
        student_id: 'STU011',
        batch: 'CS-2023',
        department: 'Computer Science',
        status: 'active',
        completedVivas: 8,
        createdAt: '2023-09-01',
    },
    {
        id: 8,
        name: 'Penelope Hall',
        email: 'student16@talenttune.com',
        student_id: 'STU016',
        batch: 'SE-2024',
        department: 'Software Engineering',
        status: 'active',
        completedVivas: 4,
        createdAt: '2024-01-15',
    },
]);

const searchQuery = ref('');
const selectedBatch = ref<string>('all');
const selectedDepartment = ref<string>('all');

const batches = computed(() => {
    const uniqueBatches = new Set(students.value.map(s => s.batch));
    return Array.from(uniqueBatches);
});

const departments = computed(() => {
    const uniqueDepts = new Set(students.value.map(s => s.department));
    return Array.from(uniqueDepts);
});

const filteredStudents = computed(() => {
    let filtered = students.value;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(student =>
            student.name.toLowerCase().includes(query) ||
            student.email.toLowerCase().includes(query) ||
            student.student_id.toLowerCase().includes(query) ||
            student.batch.toLowerCase().includes(query)
        );
    }

    if (selectedBatch.value !== 'all') {
        filtered = filtered.filter(s => s.batch === selectedBatch.value);
    }

    if (selectedDepartment.value !== 'all') {
        filtered = filtered.filter(s => s.department === selectedDepartment.value);
    }

    return filtered;
});

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this student?')) {
        // In a real app, this would make an API call
        students.value = students.value.filter(s => s.id !== id);
    }
};
</script>

<template>
    <Head title="Manage Students" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Manage Students</h1>
                    <p class="text-muted-foreground">View and manage all students in your institution</p>
                </div>
                <Button as-child>
                    <Link href="/institution/students/add">
                        <UserPlus class="h-4 w-4 mr-2" />
                        Add Student
                    </Link>
                </Button>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by name, email, or student ID..."
                                class="pl-10"
                            />
                        </div>
                        <div>
                            <select
                                v-model="selectedBatch"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                            >
                                <option value="all">All Batches</option>
                                <option v-for="batch in batches" :key="batch" :value="batch">
                                    {{ batch }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="selectedDepartment"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                            >
                                <option value="all">All Departments</option>
                                <option v-for="dept in departments" :key="dept" :value="dept">
                                    {{ dept }}
                                </option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Students List -->
            <Card>
                <CardHeader>
                    <CardTitle>All Students ({{ filteredStudents.length }})</CardTitle>
                    <CardDescription>List of all students enrolled in your institution</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="student in filteredStudents"
                            :key="student.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                    <GraduationCap class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold">{{ student.name }}</h3>
                                        <Badge variant="default" class="text-xs">
                                            {{ student.status }}
                                        </Badge>
                                        <Badge variant="outline" class="text-xs">
                                            {{ student.batch }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <Mail class="h-4 w-4" />
                                            <span>{{ student.email }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Building2 class="h-4 w-4" />
                                            <span>{{ student.department }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>Student ID: {{ student.student_id }}</span>
                                        <span>•</span>
                                        <span>{{ student.completedVivas }} vivas completed</span>
                                        <span>•</span>
                                        <span>Joined: {{ student.createdAt }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/institution/students/${student.id}/edit`">
                                        <Edit class="h-4 w-4 mr-2" />
                                        Edit
                                    </Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="handleDelete(student.id)"
                                    class="text-destructive hover:text-destructive"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div v-if="filteredStudents.length === 0" class="text-center py-12">
                            <Users class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p class="text-muted-foreground">No students found</p>
                            <p class="text-sm text-muted-foreground mt-2">
                                Try adjusting your search or filter criteria
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

