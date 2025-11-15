<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, GraduationCap, UserPlus, Building2 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/institution/dashboard',
    },
];

// Mock data
const stats = {
    totalLecturers: 25,
    totalStudents: 500,
    activeBatches: 12,
    totalVivas: 150,
};
</script>

<template>
    <Head title="Institution Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Institution Dashboard</h1>
                    <p class="text-muted-foreground">Manage lecturers, students, and batches</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Lecturers</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalLecturers }}</div>
                        <p class="text-xs text-muted-foreground">Active lecturers</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Students</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">Enrolled students</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Batches</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeBatches }}</div>
                        <p class="text-xs text-muted-foreground">Current batches</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Vivas</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalVivas }}</div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <Link href="/institution/lecturers/add" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserPlus class="h-5 w-5" />
                                Add Lecturer
                            </CardTitle>
                            <CardDescription>
                                Register a new lecturer to the system
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>

                <Link href="/institution/students/add" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserPlus class="h-5 w-5" />
                                Add Student
                            </CardTitle>
                            <CardDescription>
                                Register a new student to the system
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>

                <Link href="/institution/lecturers" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-5 w-5" />
                                Manage Lecturers
                            </CardTitle>
                            <CardDescription>
                                View and manage all lecturers
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>

                <Link href="/institution/students" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <GraduationCap class="h-5 w-5" />
                                Manage Students
                            </CardTitle>
                            <CardDescription>
                                View and manage all students
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

