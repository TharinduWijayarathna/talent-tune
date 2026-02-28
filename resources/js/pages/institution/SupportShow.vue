<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
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
import { ArrowLeft, MessageSquare, Shield } from 'lucide-vue-next';

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
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    ticket: Ticket;
    replies: Reply[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Support', href: '/institution/support' },
    {
        title: props.ticket.subject,
        href: `/institution/support/${props.ticket.id}`,
    },
];

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
    <Head :title="`Support: ${props.ticket.subject}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <Button variant="ghost" size="sm" as-child>
                    <Link href="/institution/support" class="gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back to tickets
                    </Link>
                </Button>
                <Badge
                    :variant="statusVariant(props.ticket.status)"
                    class="capitalize"
                >
                    {{ props.ticket.status }}
                </Badge>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ props.ticket.subject }}</CardTitle>
                    <CardDescription>
                        Opened {{ formatDateTime(props.ticket.created_at) }}
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

                    <p
                        v-else
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        No replies yet. Our team will respond as soon as
                        possible.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
