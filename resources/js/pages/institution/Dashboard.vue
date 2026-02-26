<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts';
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
import {
    BarChart3,
    Building2,
    GraduationCap,
    PieChart,
    TrendingUp,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface InstitutionCharts {
    vivasByStatus: { labels: string[]; series: number[] };
    usersByRole: { labels: string[]; series: number[] };
    vivasOverTime: { labels: string[]; series: number[][] };
}

const props = withDefaults(
    defineProps<{
        stats?: {
            totalLecturers: number;
            totalStudents: number;
            activeBatches: number;
            totalVivas: number;
        };
        charts?: InstitutionCharts;
    }>(),
    {
        stats: () => ({
            totalLecturers: 0,
            totalStudents: 0,
            activeBatches: 0,
            totalVivas: 0,
        }),
        charts: () => ({
            vivasByStatus: { labels: [], series: [] },
            usersByRole: { labels: [], series: [] },
            vivasOverTime: { labels: [], series: [[]] },
        }),
    },
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/institution/dashboard',
    },
];

const chartTheme = {
    colors: ['#0ea5e9', '#8b5cf6', '#10b981'],
    fontFamily: 'inherit',
};

const vivasByStatusOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { horizontal: false, columnWidth: '60%' } },
    dataLabels: { enabled: true },
    xaxis: { categories: props.charts.vivasByStatus.labels },
    colors: ['#0ea5e9', '#8b5cf6', '#10b981'],
    theme: chartTheme,
}));

const vivasByStatusSeries = computed(() => [
    { name: 'Vivas', data: props.charts.vivasByStatus.series },
]);

const usersByRoleOptions = computed(() => ({
    chart: { type: 'donut' },
    labels: props.charts.usersByRole.labels,
    colors: ['#0ea5e9', '#8b5cf6'],
    legend: { position: 'bottom' },
    theme: chartTheme,
}));

const usersByRoleSeries = computed(() => props.charts.usersByRole.series);

const vivasOverTimeOptions = computed(() => ({
    chart: { type: 'area', zoom: { enabled: false }, toolbar: { show: false } },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
    xaxis: { categories: props.charts.vivasOverTime.labels },
    colors: ['#10b981'],
    theme: chartTheme,
}));

const vivasOverTimeSeries = computed(() => [
    { name: 'Vivas created', data: props.charts.vivasOverTime.series[0] ?? [] },
]);
</script>

<template>
    <Head title="Institution Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Institution Dashboard</h1>
                    <p class="text-muted-foreground">
                        Manage lecturers, students, and batches
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Lecturers</CardTitle
                        >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.totalLecturers }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Active lecturers
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Students</CardTitle
                        >
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.totalStudents }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Enrolled students
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Active Batches</CardTitle
                        >
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.activeBatches }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Current batches
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Vivas</CardTitle
                        >
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.totalVivas }}
                        </div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5" />
                            Vivas by status
                        </CardTitle>
                        <CardDescription>Upcoming, active, and completed</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="bar"
                            height="280"
                            :options="vivasByStatusOptions"
                            :series="vivasByStatusSeries"
                        />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <PieChart class="h-5 w-5" />
                            Users by role
                        </CardTitle>
                        <CardDescription>Lecturers vs students</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="donut"
                            height="280"
                            :options="usersByRoleOptions"
                            :series="usersByRoleSeries"
                        />
                    </CardContent>
                </Card>
            </div>
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5" />
                        Vivas created (last 30 days)
                    </CardTitle>
                    <CardDescription>New viva sessions over time</CardDescription>
                </CardHeader>
                <CardContent>
                    <VueApexCharts
                        type="area"
                        height="280"
                        :options="vivasOverTimeOptions"
                        :series="vivasOverTimeSeries"
                    />
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <Link href="/institution/lecturers/add" class="block">
                    <Card
                        class="cursor-pointer transition-colors hover:border-primary"
                    >
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
                    <Card
                        class="cursor-pointer transition-colors hover:border-primary"
                    >
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
                    <Card
                        class="cursor-pointer transition-colors hover:border-primary"
                    >
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
                    <Card
                        class="cursor-pointer transition-colors hover:border-primary"
                    >
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
