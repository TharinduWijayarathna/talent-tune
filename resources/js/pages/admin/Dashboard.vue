<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
    Activity,
    BarChart3,
    Building2,
    DollarSign,
    GraduationCap,
    PieChart,
    Shield,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];

interface DashboardStats {
    totalInstitutions: number;
    totalLecturers: number;
    totalStudents: number;
    activeVivas: number;
    completedVivas: number;
    totalUsers: number;
}

interface RecentActivityItem {
    id: string;
    type: string;
    message: string;
    time: string;
    institution: string;
}

interface ChartData {
    revenueByDay: {
        labels: string[];
        series: number[][];
        totalRevenue: number;
    };
    usersByRole: { labels: string[]; series: number[] };
    vivasByStatus: { labels: string[]; series: number[] };
    paymentsByStatus: { labels: string[]; series: number[] };
}

const props = withDefaults(
    defineProps<{
        stats: DashboardStats;
        recentActivity: RecentActivityItem[];
        charts?: ChartData;
    }>(),
    {
        charts: () => ({
            revenueByDay: { labels: [], series: [[]], totalRevenue: 0 },
            usersByRole: { labels: [], series: [] },
            vivasByStatus: { labels: [], series: [] },
            paymentsByStatus: { labels: [], series: [] },
        }),
    },
);

const chartTheme = {
    colors: ['#0ea5e9', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'],
    fontFamily: 'inherit',
};

const revenueChartOptions = computed(() => ({
    chart: { type: 'area', zoom: { enabled: false }, toolbar: { show: false } },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
    xaxis: { categories: props.charts.revenueByDay.labels },
    yaxis: { labels: { formatter: (v: number) => `$${v}` } },
    colors: ['#10b981'],
    tooltip: { y: { formatter: (v: number) => `$${v}` } },
    theme: chartTheme,
}));

const revenueChartSeries = computed(() => [
    { name: 'Revenue', data: props.charts.revenueByDay.series[0] ?? [] },
]);

const usersByRoleOptions = computed(() => ({
    chart: { type: 'donut' },
    labels: props.charts.usersByRole.labels,
    colors: ['#0ea5e9', '#8b5cf6'],
    legend: { position: 'bottom' },
    theme: chartTheme,
}));

const usersByRoleSeries = computed(() => props.charts.usersByRole.series);

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

const paymentsByStatusOptions = computed(() => ({
    chart: { type: 'donut' },
    labels: props.charts.paymentsByStatus.labels,
    colors: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
    legend: { position: 'bottom' },
    theme: chartTheme,
}));

const paymentsByStatusSeries = computed(
    () => props.charts.paymentsByStatus.series,
);
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                    <p class="text-muted-foreground">
                        Monitor all system activities and statistics
                    </p>
                </div>
                <Badge variant="default" class="flex items-center gap-2">
                    <Shield class="h-4 w-4" />
                    Admin Access
                </Badge>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Institutions</CardTitle
                        >
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.totalInstitutions }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Registered institutions
                        </p>
                    </CardContent>
                </Card>

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
                            Across all institutions
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
                            >Active Vivas</CardTitle
                        >
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.activeVivas }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Currently active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Completed Vivas</CardTitle
                        >
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.completedVivas }}
                        </div>
                        <p class="text-xs text-muted-foreground">All time</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Users</CardTitle
                        >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.totalUsers }}
                        </div>
                        <p class="text-xs text-muted-foreground">All users</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Analytics Charts -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <DollarSign class="h-5 w-5" />
                            Revenue (last 30 days)
                        </CardTitle>
                        <CardDescription>
                            Daily completed payment revenue
                            <span
                                v-if="charts.revenueByDay.totalRevenue >= 0"
                                class="mt-1 block font-medium text-foreground"
                            >
                                Total: ${{
                                    charts.revenueByDay.totalRevenue.toLocaleString()
                                }}
                            </span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="area"
                            height="280"
                            :options="revenueChartOptions"
                            :series="revenueChartSeries"
                        />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5" />
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
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5" />
                            Vivas by status
                        </CardTitle>
                        <CardDescription
                            >Upcoming, active, and completed</CardDescription
                        >
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
                            Payments by status
                        </CardTitle>
                        <CardDescription
                            >Completed, pending, failed,
                            refunded</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="donut"
                            height="280"
                            :options="paymentsByStatusOptions"
                            :series="paymentsByStatusSeries"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                    <CardDescription
                        >Latest system activities across all
                        institutions</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex items-start justify-between rounded-lg border p-4"
                        >
                            <div class="flex-1 space-y-1">
                                <p class="font-medium">
                                    {{ activity.message }}
                                </p>
                                <div
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
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
                            <span class="text-sm text-muted-foreground"
                                >System Status</span
                            >
                            <Badge variant="default">Operational</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Database Status</span
                            >
                            <Badge variant="default">Healthy</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Storage Used</span
                            >
                            <span class="text-sm font-medium">45%</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription
                            >Common administrative tasks</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Link href="/admin/institutions">
                            <Button
                                variant="outline"
                                class="w-full justify-start"
                            >
                                View All Institutions
                            </Button>
                        </Link>
                        <Button variant="outline" class="w-full justify-start">
                            System Settings
                        </Button>
                        <Link href="/admin/users">
                            <Button
                                variant="outline"
                                class="w-full justify-start"
                            >
                                User Management
                            </Button>
                        </Link>
                        <Button variant="outline" class="w-full justify-start">
                            Reports & Analytics
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
