<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Add Student', href: '/institution/students/add' },
];

const props = defineProps<{
    batches?: string[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    student_id: '',
    batch: '',
    department: '',
});

const submitForm = () => {
    form.post('/institution/students');
};
</script>

<template>
    <Head title="Add Student" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Add Student</h1>
                    <p class="text-muted-foreground">Register a new student to the system</p>
                </div>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Student Information</CardTitle>
                    <CardDescription>Enter the details of the new student</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Jane Doe"
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
                                    placeholder="jane.doe@university.edu"
                                    required
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="student_id">Student ID</Label>
                                <Input
                                    id="student_id"
                                    v-model="form.student_id"
                                    placeholder="STU001"
                                />
                                <InputError :message="form.errors.student_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="batch">Batch</Label>
                                <select
                                    v-if="props.batches && props.batches.length > 0"
                                    id="batch"
                                    v-model="form.batch"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                                >
                                    <option value="">Select batch</option>
                                    <option v-for="b in props.batches" :key="b" :value="b">{{ b }}</option>
                                </select>
                                <Input
                                    v-else
                                    id="batch"
                                    v-model="form.batch"
                                    placeholder="e.g. CS-2024 (create batches in Batches section first)"
                                />
                                <InputError :message="form.errors.batch" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="department">Department</Label>
                            <Input
                                id="department"
                                v-model="form.department"
                                placeholder="Computer Science"
                            />
                            <InputError :message="form.errors.department" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="password">Password *</Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password *</Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/institution/students">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Add Student
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

