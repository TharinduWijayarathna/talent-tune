<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Add Student', href: '/institution/students/add' },
];

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    studentId: '',
    batch: '',
    department: '',
});

const submitForm = () => {
    // In a real app, this would submit to the backend
    console.log('Submitting student form:', form.value);
    alert('Student added successfully!');
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
                    <Form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Jane Doe"
                                    required
                                />
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
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="studentId">Student ID *</Label>
                                <Input
                                    id="studentId"
                                    v-model="form.studentId"
                                    placeholder="STU001"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="batch">Batch *</Label>
                                <Input
                                    id="batch"
                                    v-model="form.batch"
                                    placeholder="CS-2024"
                                    required
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="department">Department *</Label>
                            <Input
                                id="department"
                                v-model="form.department"
                                placeholder="Computer Science"
                                required
                            />
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
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                            <Button type="submit">
                                Add Student
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

