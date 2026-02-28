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
import { AlertTriangle, Plus } from 'lucide-vue-next';

interface IssueItem {
    id: number;
    subject: string;
    status: string;
    created_at: string;
}

const props = defineProps<{
    issues: IssueItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'Report Issue', href: '/lecturer/issues' },
];

const statusVariant = (status: string) => {
    switch (status) {
        case 'pending':
            return 'default';
        case 'reviewed':
            return 'secondary';
        case 'escalated':
            return 'outline';
        default:
            return 'outline';
    }
};

const formatDate = (iso: string) =>
    iso ? new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' }) : '';
</script>

<template>
    <Head title="Report Issue" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Report Issue</h1>
                    <p class="text-muted-foreground">
                        Report issues to your institution admin
                    </p>
                </div>
                <Button as-child>
                    <Link href="/lecturer/issues/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Report Issue
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Your reported issues</CardTitle>
                    <CardDescription
                        >Issues you have submitted for review</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="issue in props.issues"
                            :key="issue.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <AlertTriangle class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">{{
                                            issue.subject
                                        }}</span>
                                        <Badge
                                            :variant="statusVariant(issue.status)"
                                            class="capitalize"
                                        >
                                            {{ issue.status }}
                                        </Badge>
                                    </div>
                                    <div
                                        class="text-sm text-muted-foreground"
                                    >
                                        Reported {{ formatDate(issue.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="props.issues.length === 0"
                            class="py-12 text-center"
                        >
                            <AlertTriangle
                                class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                            />
                            <p class="text-muted-foreground">
                                No issues reported yet
                            </p>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Report an issue if you need help from your
                                institution
                            </p>
                            <Button as-child class="mt-4">
                                <Link href="/lecturer/issues/create">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Report Issue
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
