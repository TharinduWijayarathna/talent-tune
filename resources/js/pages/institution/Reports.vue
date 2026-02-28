<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileDown, GraduationCap, Users } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Reports', href: '/institution/reports' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reports</h1>
                <p class="text-muted-foreground">
                    Download PDF reports for students (by batch) and lecturers
                    (vivas and submissions).
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5" />
                            Students Report
                        </CardTitle>
                        <CardDescription>
                            Students grouped by batch with completed vivas and
                            join date.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Link
                            :href="'/institution/reports/students-pdf'"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <FileDown class="h-4 w-4" />
                            Download PDF
                        </Link>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5" />
                            Lecturers Report
                        </CardTitle>
                        <CardDescription>
                            Lecturers with total vivas created and completed
                            student submissions.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Link
                            :href="'/institution/reports/lecturers-pdf'"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <FileDown class="h-4 w-4" />
                            Download PDF
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
