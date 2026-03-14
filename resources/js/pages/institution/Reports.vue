<script setup lang="ts">
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
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle2,
    FileDown,
    GraduationCap,
    Loader2,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface ReportSummary {
    students: number;
    lecturers: number;
    vivas: number;
    completed_submissions: number;
}

const props = withDefaults(
    defineProps<{
        summary?: ReportSummary;
    }>(),
    { summary: undefined },
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Reports', href: '/institution/reports' },
];

const downloading = ref<'students' | 'lecturers' | null>(null);
const dateStr = new Date().toISOString().slice(0, 10);

async function downloadPdf(
    url: string,
    filename: string,
    key: 'students' | 'lecturers',
) {
    downloading.value = key;
    try {
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Download failed');
        const blob = await res.blob();
        const blobUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(blobUrl);
    } finally {
        downloading.value = null;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Reports</h1>
                    <p class="text-muted-foreground">
                        Overview and PDF reports for students (by batch) and
                        lecturers (vivas and submissions).
                    </p>
                </div>
            </div>

            <!-- Summary stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Students
                        </CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <span class="text-2xl font-bold">{{
                            props.summary?.students ?? 0
                        }}</span>
                        <p class="text-xs text-muted-foreground">
                            Enrolled in institution
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Lecturers
                        </CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <span class="text-2xl font-bold">{{
                            props.summary?.lecturers ?? 0
                        }}</span>
                        <p class="text-xs text-muted-foreground">
                            Active lecturers
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Viva Sessions
                        </CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <span class="text-2xl font-bold">{{
                            props.summary?.vivas ?? 0
                        }}</span>
                        <p class="text-xs text-muted-foreground">
                            Total vivas created
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Completed Submissions
                        </CardTitle>
                        <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <span class="text-2xl font-bold">{{
                            props.summary?.completed_submissions ?? 0
                        }}</span>
                        <p class="text-xs text-muted-foreground">
                            Student submissions completed
                        </p>
                    </CardContent>
                </Card>
            </div>

            <h2 class="text-lg font-semibold">Download reports</h2>
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
                        <Button
                            :disabled="!!downloading"
                            class="inline-flex items-center gap-2"
                            @click="
                                downloadPdf(
                                    '/institution/reports/students-pdf',
                                    `students-report-${dateStr}.pdf`,
                                    'students',
                                )
                            "
                        >
                            <Loader2
                                v-if="downloading === 'students'"
                                class="h-4 w-4 animate-spin"
                            />
                            <FileDown v-else class="h-4 w-4" />
                            Download PDF
                        </Button>
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
                        <Button
                            :disabled="!!downloading"
                            class="inline-flex items-center gap-2"
                            @click="
                                downloadPdf(
                                    '/institution/reports/lecturers-pdf',
                                    `lecturers-report-${dateStr}.pdf`,
                                    'lecturers',
                                )
                            "
                        >
                            <Loader2
                                v-if="downloading === 'lecturers'"
                                class="h-4 w-4 animate-spin"
                            />
                            <FileDown v-else class="h-4 w-4" />
                            Download PDF
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
