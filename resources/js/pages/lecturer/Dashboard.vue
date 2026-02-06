<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Calendar, Users, FileText, Plus } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    stats?: {
        totalSessions: number;
        activeSessions: number;
        totalStudents: number;
        completedSessions: number;
    };
    recentSessions?: Array<{
        id: number;
        title: string;
        batch: string;
        date: string;
        students: number;
        status: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/lecturer/dashboard',
    },
];

const stats = computed(() => props.stats || {
    totalSessions: 0,
    activeSessions: 0,
    totalStudents: 0,
    completedSessions: 0,
});

const recentSessions = computed(() => props.recentSessions || []);
</script>

<template>
    <Head title="Lecturer Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Welcome back, Lecturer!</h1>
                    <p class="text-muted-foreground">Manage your viva sessions and students</p>
                </div>
                <Button as-child>
                    <Link href="/lecturer/vivas/create">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Viva Session
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Sessions</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalSessions }}</div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Sessions</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeSessions }}</div>
                        <p class="text-xs text-muted-foreground">Upcoming</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Students</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">Across all batches</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completed</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completedSessions }}</div>
                        <p class="text-xs text-muted-foreground">Finished sessions</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Sessions -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Viva Sessions</CardTitle>
                    <CardDescription>Your latest viva session activities</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recentSessions.length === 0" class="text-center py-8 text-muted-foreground">
                        No recent viva sessions
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="session in recentSessions"
                            :key="session.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-1">
                                <h3 class="font-semibold">{{ session.title }}</h3>
                                <p class="text-sm text-muted-foreground">
                                    Batch: {{ session.batch }} • Date: {{ session.date }} • Students: {{ session.students }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" as-child>
                                    <Link :href="`/lecturer/vivas/${session.id}`">
                                        View
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <Link href="/lecturer/vivas/create" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Plus class="h-5 w-5" />
                                Create New Viva Session
                            </CardTitle>
                            <CardDescription>
                                Set up a new viva session for your batch
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>

                <Link href="/lecturer/vivas" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Manage Sessions
                            </CardTitle>
                            <CardDescription>
                                View and manage all your viva sessions
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

