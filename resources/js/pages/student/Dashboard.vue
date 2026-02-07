<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { GraduationCap, Calendar, BookOpen } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    stats?: {
        upcomingVivas: number;
        completedVivas: number;
        totalSessions: number;
    };
    upcomingVivas?: Array<{
        id: number;
        title: string;
        date: string;
        time: string;
        lecturer: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/student/dashboard',
    },
];

const stats = computed(() => props.stats ?? {
    upcomingVivas: 0,
    completedVivas: 0,
    totalSessions: 0,
});

const upcomingVivas = computed(() => props.upcomingVivas ?? []);
</script>

<template>
    <Head title="Student Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Welcome back!</h1>
                    <p class="text-muted-foreground">Here's an overview of your viva sessions</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Upcoming Vivas</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.upcomingVivas }}</div>
                        <p class="text-xs text-muted-foreground">Scheduled sessions</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completed</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completedVivas }}</div>
                        <p class="text-xs text-muted-foreground">Finished sessions</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Sessions</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalSessions }}</div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Upcoming Vivas -->
            <Card>
                <CardHeader>
                    <CardTitle>Upcoming Viva Sessions</CardTitle>
                    <CardDescription>Your scheduled viva sessions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="upcomingVivas.length === 0" class="text-center py-8 text-muted-foreground">
                        No upcoming viva sessions
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="viva in upcomingVivas"
                            :key="viva.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-1">
                                <h3 class="font-semibold">{{ viva.title }}</h3>
                                <p class="text-sm text-muted-foreground">
                                    {{ viva.date }} at {{ viva.time }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Lecturer: {{ viva.lecturer }}
                                </p>
                            </div>
                            <Button as-child>
                                <Link :href="`/student/vivas/${viva.id}/attend`">
                                    Attend Viva
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <Link href="/student/vivas" class="block">
                    <Card class="cursor-pointer hover:border-primary transition-colors">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5" />
                                View All Viva Sessions
                            </CardTitle>
                            <CardDescription>
                                Browse all available viva sessions
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

