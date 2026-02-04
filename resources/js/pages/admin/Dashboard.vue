<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Users, GraduationCap, Building2, Activity, TrendingUp, Shield } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

// Mock data
const stats = {
    totalInstitutions: 5,
    totalLecturers: 125,
    totalStudents: 2500,
    activeVivas: 45,
    completedVivas: 320,
    totalUsers: 2630,
};

const recentActivity = [
    { id: 1, type: 'viva_created', message: 'New viva session created by Dr. Smith', time: '2 hours ago', institution: 'University A' },
    { id: 2, type: 'student_added', message: '50 new students added to CS-2024 batch', time: '5 hours ago', institution: 'University B' },
    { id: 3, type: 'lecturer_added', message: 'New lecturer registered: Dr. Johnson', time: '1 day ago', institution: 'University A' },
    { id: 4, type: 'viva_completed', message: 'Database Systems viva completed', time: '1 day ago', institution: 'University C' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                    <p class="text-muted-foreground">Monitor all system activities and statistics</p>
                </div>
                <Badge variant="default" class="flex items-center gap-2">
                    <Shield class="h-4 w-4" />
                    Admin Access
                </Badge>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Institutions</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalInstitutions }}</div>
                        <p class="text-xs text-muted-foreground">Registered institutions</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Lecturers</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalLecturers }}</div>
                        <p class="text-xs text-muted-foreground">Across all institutions</p>
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
                        <CardTitle class="text-sm font-medium">Active Vivas</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeVivas }}</div>
                        <p class="text-xs text-muted-foreground">Currently active</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completed Vivas</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completedVivas }}</div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalUsers }}</div>
                        <p class="text-xs text-muted-foreground">All users</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                    <CardDescription>Latest system activities across all institutions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex items-start justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-1 flex-1">
                                <p class="font-medium">{{ activity.message }}</p>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <span>{{ activity.institution }}</span>
                                    <span>•</span>
                                    <span>{{ activity.time }}</span>
                                </div>
                            </div>
                            <Badge variant="outline">{{ activity.type }}</Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- System Overview -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>System Overview</CardTitle>
                        <CardDescription>Quick statistics</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">System Status</span>
                            <Badge variant="default">Operational</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Database Status</span>
                            <Badge variant="default">Healthy</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Storage Used</span>
                            <span class="text-sm font-medium">45%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common administrative tasks</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Link href="/admin/institutions">
                            <Button variant="outline" class="w-full justify-start">
                                View All Institutions
                            </Button>
                        </Link>
                        <Button variant="outline" class="w-full justify-start">
                            System Settings
                        </Button>
                        <Button variant="outline" class="w-full justify-start">
                            User Management
                        </Button>
                        <Button variant="outline" class="w-full justify-start">
                            Reports & Analytics
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

