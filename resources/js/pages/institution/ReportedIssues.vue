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
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, Eye, GraduationCap, User } from 'lucide-vue-next';
import { ref } from 'vue';

interface IssueItem {
    id: number;
    subject: string;
    status: string;
    reporter_name: string | null;
    reporter_role: string | null;
    created_at: string;
    support_ticket_id: number | null;
}

const props = defineProps<{
    issues: IssueItem[];
    filters: { status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Reported Issues', href: '/institution/reported-issues' },
];

const statusFilter = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get('/institution/reported-issues', {
        status: statusFilter.value || undefined,
    });
};

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

const reporterRoleLabel = (role: string | null) => {
    if (role === 'student') return 'Student';
    if (role === 'lecturer') return 'Lecturer';
    return role ?? '';
};

const formatDate = (iso: string) =>
    iso
        ? new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' })
        : '';
</script>

<template>
    <Head title="Reported Issues" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Reported Issues</h1>
                    <p class="text-muted-foreground">
                        Issues reported by students and lecturers
                    </p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <div class="mb-4 flex flex-wrap gap-4">
                        <select
                            v-model="statusFilter"
                            class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="escalated">Escalated</option>
                        </select>
                        <Button @click="applyFilters">Apply</Button>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Issues ({{ props.issues.length }})</CardTitle>
                    <CardDescription
                        >Review and escalate to TalentTune support when
                        needed</CardDescription
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
                                    <AlertTriangle
                                        class="h-6 w-6 text-primary"
                                    />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/institution/reported-issues/${issue.id}`"
                                            class="font-semibold hover:underline"
                                        >
                                            {{ issue.subject }}
                                        </Link>
                                        <Badge
                                            :variant="
                                                statusVariant(issue.status)
                                            "
                                            class="capitalize"
                                        >
                                            {{ issue.status }}
                                        </Badge>
                                        <Badge
                                            v-if="issue.support_ticket_id"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            Escalated
                                        </Badge>
                                    </div>
                                    <div
                                        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                                    >
                                        <span
                                            v-if="issue.reporter_name"
                                            class="flex items-center gap-1"
                                        >
                                            <User class="h-4 w-4" />
                                            {{ issue.reporter_name }}
                                        </span>
                                        <span
                                            v-if="issue.reporter_role"
                                            class="flex items-center gap-1"
                                        >
                                            <GraduationCap class="h-4 w-4" />
                                            {{
                                                reporterRoleLabel(
                                                    issue.reporter_role,
                                                )
                                            }}
                                        </span>
                                        <span
                                            >Reported
                                            {{
                                                formatDate(issue.created_at)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link
                                    :href="`/institution/reported-issues/${issue.id}`"
                                >
                                    <Eye class="mr-2 h-4 w-4" />
                                    View
                                </Link>
                            </Button>
                        </div>

                        <div
                            v-if="props.issues.length === 0"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <AlertTriangle
                                class="mx-auto mb-4 h-12 w-12 opacity-50"
                            />
                            <p>No reported issues match your filters.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
