<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface InstitutionOption {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        institution_id: number | null;
        student_id: string | null;
        employee_id: string | null;
        batch: string | null;
        department: string | null;
    };
    institutions: InstitutionOption[];
    returnSection: string;
}>();

const sectionRoutes: Record<string, string> = {
    institution_admins: '/admin/users/institution-admins',
    students: '/admin/users/students',
    lecturers: '/admin/users/lecturers',
};

const backUrl = computed(() => sectionRoutes[props.returnSection] ?? '/admin/users/students');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'User Management', href: '/admin/users/students' },
    { title: 'Edit User', href: '#' },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    institution_id: props.user.institution_id ?? '',
    student_id: props.user.student_id ?? '',
    employee_id: props.user.employee_id ?? '',
    batch: props.user.batch ?? '',
    department: props.user.department ?? '',
    password: '',
    password_confirmation: '',
    return_section: props.returnSection,
});

const submitForm = () => {
    const payload: Record<string, unknown> = {
        name: form.name,
        email: form.email,
        role: form.role,
        return_section: form.return_section,
    };
    // Institution: only for non-admin roles
    if (showInstitution.value) {
        payload.institution_id =
            form.institution_id === '' ? null : Number(form.institution_id);
    } else {
        payload.institution_id = null;
    }
    // Role-specific fields only
    if (form.role === 'student') {
        payload.student_id = form.student_id || null;
        payload.batch = form.batch || null;
        payload.department = form.department || null;
        payload.employee_id = null;
    } else if (form.role === 'lecturer') {
        payload.employee_id = form.employee_id || null;
        payload.department = form.department || null;
        payload.student_id = null;
        payload.batch = null;
    } else if (form.role === 'institution') {
        payload.student_id = null;
        payload.employee_id = null;
        payload.batch = null;
        payload.department = null;
    } else {
        payload.student_id = null;
        payload.employee_id = null;
        payload.batch = null;
        payload.department = null;
    }
    if (form.password) {
        payload.password = form.password;
        payload.password_confirmation = form.password_confirmation;
    }
    form.transform(() => payload).put(`/admin/users/${props.user.id}`);
};

const roleOptions = [
    { value: 'student', label: 'Student' },
    { value: 'lecturer', label: 'Lecturer' },
    { value: 'institution', label: 'Institution' },
    { value: 'admin', label: 'Admin' },
];

// Fields shown per role: institution is for student, lecturer, institution only
const showInstitution = computed(() =>
    ['student', 'lecturer', 'institution'].includes(form.role),
);
const showStudentFields = computed(() => form.role === 'student');
const showLecturerFields = computed(() => form.role === 'lecturer');
const showDepartment = computed(() =>
    ['student', 'lecturer'].includes(form.role),
);
</script>

<template>
    <Head title="Edit User - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Edit User</h1>
                    <p class="text-muted-foreground">
                        Update user information and role
                    </p>
                </div>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>User Information</CardTitle>
                    <CardDescription>
                        Update the details of the user. Fields shown depend on the selected role. Leave password blank to keep the current password.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Full name"
                                    required
                                />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email">Email Address *</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="user@example.com"
                                    required
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Role *</Label>
                            <select
                                id="role"
                                v-model="form.role"
                                required
                                class="flex h-9 w-full max-w-xs rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <option
                                    v-for="opt in roleOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role" />
                        </div>

                        <!-- Institution: for student, lecturer, institution roles only -->
                        <div v-if="showInstitution" class="space-y-2">
                            <Label for="institution_id">Institution *</Label>
                            <select
                                id="institution_id"
                                v-model="form.institution_id"
                                required
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <option value="">Select institution</option>
                                <option
                                    v-for="inst in institutions"
                                    :key="inst.id"
                                    :value="String(inst.id)"
                                >
                                    {{ inst.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.institution_id" />
                        </div>

                        <!-- Student-only: Student ID, Batch -->
                        <div
                            v-if="showStudentFields"
                            class="grid gap-4 md:grid-cols-2"
                        >
                            <div class="space-y-2">
                                <Label for="student_id">Student ID</Label>
                                <Input
                                    id="student_id"
                                    v-model="form.student_id"
                                    placeholder="e.g. STU001"
                                />
                                <InputError :message="form.errors.student_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="batch">Batch</Label>
                                <Input
                                    id="batch"
                                    v-model="form.batch"
                                    placeholder="e.g. CS-2024"
                                />
                                <InputError :message="form.errors.batch" />
                            </div>
                        </div>

                        <!-- Lecturer-only: Employee ID -->
                        <div v-if="showLecturerFields" class="space-y-2">
                            <Label for="employee_id">Employee ID</Label>
                            <Input
                                id="employee_id"
                                v-model="form.employee_id"
                                placeholder="e.g. EMP001"
                            />
                            <InputError :message="form.errors.employee_id" />
                        </div>

                        <!-- Department: for student and lecturer -->
                        <div v-if="showDepartment" class="space-y-2">
                            <Label for="department">Department</Label>
                            <Input
                                id="department"
                                v-model="form.department"
                                placeholder="e.g. Computer Science"
                            />
                            <InputError :message="form.errors.department" />
                        </div>

                        <div class="border-t pt-4 space-y-4">
                            <p class="text-sm text-muted-foreground">
                                Leave password blank to keep the current password.
                            </p>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="password">New Password</Label>
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="form.errors.password" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="password_confirmation">Confirm New Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="backUrl">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update User
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
