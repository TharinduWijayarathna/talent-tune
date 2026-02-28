<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    Check,
    GraduationCap,
    User,
} from 'lucide-vue-next';

interface Issue {
    id: number;
    subject: string;
    body: string;
    status: string;
    reporter_name: string | null;
    reporter_email: string | null;
    reporter_role: string | null;
    created_at: string;
    support_ticket_id: number | null;
}

const props = defineProps<{
    issue: Issue;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Reported Issues', href: '/institution/reported-issues' },
    {
        title: props.issue.subject,
        href: `/institution/reported-issues/${props.issue.id}`,
    },
];

const escalateForm = useForm({
    message: '',
});

const submitEscalate = () => {
    escalateForm.post(
        `/institution/reported-issues/${props.issue.id}/escalate`,
        { preserveScroll: true },
    );
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

const markReviewed = () => {
    router.post(
        `/institution/reported-issues/${props.issue.id}/reviewed`,
        {},
        { preserveScroll: true },
    );
};

const formatDateTime = (iso: string) =>
    iso
        ? new Date(iso).toLocaleString(undefined, {
              dateStyle: 'medium',
              timeStyle: 'short',
          })
        : '';
</script>

<template>
    <Head :title="`Issue: ${props.issue.subject}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <Button variant="ghost" size="sm" as-child>
                    <Link href="/institution/reported-issues" class="gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back to issues
                    </Link>
                </Button>
                <Badge
                    :variant="statusVariant(props.issue.status)"
                    class="capitalize"
                >
                    {{ props.issue.status }}
                </Badge>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ props.issue.subject }}</CardTitle>
                    <CardDescription class="flex flex-wrap gap-x-4 gap-y-1">
                        <span
                            v-if="props.issue.reporter_name"
                            class="flex items-center gap-1"
                        >
                            <User class="h-4 w-4" />
                            {{ props.issue.reporter_name }}
                        </span>
                        <span
                            v-if="props.issue.reporter_role"
                            class="flex items-center gap-1"
                        >
                            <GraduationCap class="h-4 w-4" />
                            {{
                                reporterRoleLabel(props.issue.reporter_role)
                            }}
                        </span>
                        <span
                            v-if="props.issue.reporter_email"
                            class="text-muted-foreground"
                        >
                            {{ props.issue.reporter_email }}
                        </span>
                        <span>
                            Reported
                            {{ formatDateTime(props.issue.created_at) }}
                        </span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        class="rounded-lg border bg-muted/30 p-4 whitespace-pre-wrap"
                    >
                        {{ props.issue.body }}
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-if="
                                props.issue.status === 'pending' &&
                                !props.issue.support_ticket_id
                            "
                            variant="outline"
                            size="sm"
                            @click="markReviewed"
                        >
                            <Check class="mr-2 h-4 w-4" />
                            Mark as reviewed
                        </Button>
                        <Button
                            v-if="props.issue.support_ticket_id"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link
                                :href="`/institution/support/${props.issue.support_ticket_id}`"
                                class="gap-2"
                            >
                                <ArrowUpRight class="h-4 w-4" />
                                View support ticket
                            </Link>
                        </Button>
                    </div>

                    <div
                        v-if="
                            !props.issue.support_ticket_id &&
                            props.issue.status !== 'escalated'
                        "
                        class="rounded-lg border bg-muted/20 p-4"
                    >
                        <h3 class="mb-3 text-sm font-medium">
                            Escalate to TalentTune support
                        </h3>
                        <p class="mb-3 text-sm text-muted-foreground">
                            Report this issue to TalentTune admin. A support
                            ticket will be created with the issue details.
                        </p>
                        <form
                            @submit.prevent="submitEscalate"
                            class="space-y-3"
                        >
                            <div class="space-y-2">
                                <Label for="message"
                                    >Additional note (optional)</Label
                                >
                                <Textarea
                                    id="message"
                                    v-model="escalateForm.message"
                                    placeholder="Add any context for TalentTune support..."
                                    rows="3"
                                />
                                <InputError
                                    :message="escalateForm.errors.message"
                                />
                            </div>
                            <Button
                                type="submit"
                                :disabled="escalateForm.processing"
                            >
                                <ArrowUpRight class="mr-2 h-4 w-4" />
                                Escalate to support
                            </Button>
                        </form>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
