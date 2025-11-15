<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Award, TrendingUp, Calendar, User } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Marks', href: '/student/marks' },
];

// Mock data
const marks = [
    {
        id: 1,
        vivaTitle: 'Data Structures Viva',
        lecturer: 'Dr. Brown',
        date: '2024-01-15',
        marks: 88,
        maxMarks: 100,
        grade: 'A',
        feedback: 'Excellent understanding of algorithms and data structures. Well done!',
    },
    {
        id: 2,
        vivaTitle: 'Operating Systems Viva',
        lecturer: 'Dr. Davis',
        date: '2024-01-10',
        marks: 92,
        maxMarks: 100,
        grade: 'A+',
        feedback: 'Outstanding performance. Strong grasp of OS concepts.',
    },
    {
        id: 3,
        vivaTitle: 'Computer Networks Viva',
        lecturer: 'Dr. Wilson',
        date: '2024-01-05',
        marks: 85,
        maxMarks: 100,
        grade: 'A',
        feedback: 'Good understanding of networking fundamentals.',
    },
    {
        id: 4,
        vivaTitle: 'Programming Languages Viva',
        lecturer: 'Dr. Martinez',
        date: '2023-12-28',
        marks: 90,
        maxMarks: 100,
        grade: 'A+',
        feedback: 'Excellent knowledge of programming paradigms.',
    },
];

const averageMarks = Math.round(marks.reduce((sum, m) => sum + m.marks, 0) / marks.length);
const totalMarks = marks.reduce((sum, m) => sum + m.marks, 0);
const maxTotalMarks = marks.reduce((sum, m) => sum + m.maxMarks, 0);

const getGradeColor = (grade: string) => {
    if (grade === 'A+') return 'text-green-600 dark:text-green-400';
    if (grade === 'A') return 'text-blue-600 dark:text-blue-400';
    if (grade === 'B') return 'text-yellow-600 dark:text-yellow-400';
    return 'text-gray-600 dark:text-gray-400';
};
</script>

<template>
    <Head title="Marks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Marks & Performance</h1>
                    <p class="text-muted-foreground">View your viva session marks and feedback</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Average Marks</CardTitle>
                        <Award class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ averageMarks }}%</div>
                        <p class="text-xs text-muted-foreground">Across all sessions</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Marks</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalMarks }}/{{ maxTotalMarks }}</div>
                        <p class="text-xs text-muted-foreground">{{ marks.length }} sessions completed</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sessions Completed</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ marks.length }}</div>
                        <p class="text-xs text-muted-foreground">Total viva sessions</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Marks List -->
            <Card>
                <CardHeader>
                    <CardTitle>Viva Session Marks</CardTitle>
                    <CardDescription>Detailed marks and feedback for each session</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="mark in marks"
                            :key="mark.id"
                            class="rounded-lg border p-4 space-y-3"
                        >
                            <div class="flex items-start justify-between">
                                <div class="space-y-1 flex-1">
                                    <h3 class="font-semibold text-lg">{{ mark.vivaTitle }}</h3>
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <User class="h-4 w-4" />
                                            <span>{{ mark.lecturer }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-4 w-4" />
                                            <span>{{ mark.date }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold">{{ mark.marks }}/{{ mark.maxMarks }}</div>
                                    <Badge :class="getGradeColor(mark.grade)" class="mt-1">
                                        Grade: {{ mark.grade }}
                                    </Badge>
                                </div>
                            </div>

                            <div v-if="mark.feedback" class="rounded-md bg-muted p-3">
                                <div class="text-sm font-medium mb-1">Feedback:</div>
                                <div class="text-sm text-muted-foreground">{{ mark.feedback }}</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

