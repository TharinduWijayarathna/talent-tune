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

const props = defineProps<{
    lecturerId: number;
    lecturer: {
        id: number;
        name: string;
        email: string;
        employee_id: string | null;
        department: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Lecturers', href: '/institution/lecturers' },
    { title: 'Edit Lecturer', href: '#' },
];

const form = useForm({
    name: props.lecturer.name,
    email: props.lecturer.email,
    employee_id: props.lecturer.employee_id ?? '',
    department: props.lecturer.department ?? '',
});

const submitForm = () => {
    form.put(`/institution/lecturers/${props.lecturerId}`);
};
</script>

<template>
    <Head title="Edit Lecturer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Edit Lecturer</h1>
                    <p class="text-muted-foreground">
                        Update lecturer information
                    </p>
                </div>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Lecturer Information</CardTitle>
                    <CardDescription
                        >Update the details of the lecturer</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Full Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Dr. John Smith"
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
                                    placeholder="john.smith@university.edu"
                                    required
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="employee_id">Employee ID</Label>
                                <Input
                                    id="employee_id"
                                    v-model="form.employee_id"
                                    placeholder="EMP001"
                                />
                                <InputError
                                    :message="form.errors.employee_id"
                                />
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
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/institution/lecturers"
                                    >Cancel</Link
                                >
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update Lecturer
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
