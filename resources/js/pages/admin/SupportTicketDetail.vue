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
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    MessageSquare,
    Shield,
    User,
} from 'lucide-vue-next';

interface InstitutionRef {
    id: number;
    name: string;
    slug: string;
}

interface Reply {
    id: number;
    body: string;
    user_name: string;
    is_staff: boolean;
    created_at: string;
}

interface Ticket {
    id: number;
    subject: string;
    body: string;
    status: string;
    institution: InstitutionRef | null;
    user_name: string | null;
    user_email: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    ticket: Ticket;
    replies: Reply[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Support Tickets', href: '/admin/support' },
    {
        title: props.ticket.subject,
        href: `/admin/support/${props.ticket.id}`,
    },
];

const replyForm = useForm({
    body: '',
});

const statusForm = useForm({
    status: props.ticket.status as string,
});

const submitReply = () => {
    replyForm.post(`/admin/support/${props.ticket.id}/reply`, {
        preserveScroll: true,
        onSuccess: () => replyForm.reset('body'),
    });
};

const submitStatus = () => {
    statusForm.patch(`/admin/support/${props.ticket.id}/status`, {
        preserveScroll: true,
    });
};

const statusVariant = (status: string) => {
    switch (status) {
        case 'open':
            return 'default';
        case 'answered':
            return 'secondary';
        case 'closed':
            return 'outline';
        default:
            return 'outline';
    }
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
    <Head :title="`Support: ${props.ticket.subject} - Admin`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <Button variant="ghost" size="sm" as-child>
                    <Link href="/admin/support" class="gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back to tickets
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <form
                        class="flex items-center gap-2"
                        @submit.prevent="submitStatus"
                    >
                        <select
                            v-model="statusForm.status"
                            class="flex h-9 w-[130px] rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option value="open">Open</option>
                            <option value="answered">Answered</option>
                            <option value="closed">Closed</option>
                        </select>
                        <Button
                            type="submit"
                            size="sm"
                            variant="outline"
                            :disabled="statusForm.processing"
                        >
                            Update
                        </Button>
                    </form>
                    <Badge
                        :variant="statusVariant(props.ticket.status)"
                        class="capitalize"
                    >
                        {{ props.ticket.status }}
                    </Badge>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ props.ticket.subject }}</CardTitle>
                    <CardDescription class="flex flex-wrap gap-x-4 gap-y-1">
                        <span
                            v-if="props.ticket.institution"
                            class="flex items-center gap-1"
                        >
                            <Building2 class="h-4 w-4" />
                            {{ props.ticket.institution.name }}
                        </span>
                        <span
                            v-if="props.ticket.user_name"
                            class="flex items-center gap-1"
                        >
                            <User class="h-4 w-4" />
                            {{ props.ticket.user_name }}
                            <span
                                v-if="props.ticket.user_email"
                                class="text-muted-foreground"
                            >
                                ({{ props.ticket.user_email }})
                            </span>
                        </span>
                        <span>
                            Opened
                            {{ formatDateTime(props.ticket.created_at) }}
                        </span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        class="rounded-lg border bg-muted/30 p-4 whitespace-pre-wrap"
                    >
                        {{ props.ticket.body }}
                    </div>

                    <div v-if="props.replies.length > 0" class="space-y-4 pt-4">
                        <h3 class="text-sm font-medium text-muted-foreground">
                            Replies ({{ props.replies.length }})
                        </h3>
                        <div
                            v-for="reply in props.replies"
                            :key="reply.id"
                            class="flex gap-3 rounded-lg border p-4"
                            :class="
                                reply.is_staff
                                    ? 'border-primary/30 bg-primary/5'
                                    : ''
                            "
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                                :class="
                                    reply.is_staff
                                        ? 'bg-primary/20 text-primary'
                                        : 'bg-muted text-muted-foreground'
                                "
                            >
                                <Shield v-if="reply.is_staff" class="h-4 w-4" />
                                <MessageSquare v-else class="h-4 w-4" />
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{
                                        reply.user_name
                                    }}</span>
                                    <Badge
                                        v-if="reply.is_staff"
                                        variant="secondary"
                                        class="text-xs"
                                    >
                                        Support
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        {{ formatDateTime(reply.created_at) }}
                                    </span>
                                </div>
                                <p class="text-sm whitespace-pre-wrap">
                                    {{ reply.body }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="props.ticket.status !== 'closed'"
                        class="rounded-lg border bg-muted/20 p-4"
                    >
                        <h3 class="mb-3 text-sm font-medium">
                            Reply to ticket
                        </h3>
                        <form @submit.prevent="submitReply" class="space-y-3">
                            <div class="space-y-2">
                                <Label for="body">Message</Label>
                                <Textarea
                                    id="body"
                                    v-model="replyForm.body"
                                    placeholder="Type your response..."
                                    rows="4"
                                    required
                                />
                                <InputError :message="replyForm.errors.body" />
                            </div>
                            <Button
                                type="submit"
                                :disabled="replyForm.processing"
                            >
                                Send reply
                            </Button>
                        </form>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
