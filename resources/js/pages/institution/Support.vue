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
import { MessageSquarePlus, Plus, Ticket } from 'lucide-vue-next';

interface TicketItem {
    id: number;
    subject: string;
    status: string;
    replies_count: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    tickets: TicketItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Support', href: '/institution/support' },
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

const formatDate = (iso: string) =>
    iso ? new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' }) : '';
</script>

<template>
    <Head title="Support" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Support</h1>
                    <p class="text-muted-foreground">
                        Submit and track support tickets
                    </p>
                </div>
                <Button as-child>
                    <Link href="/institution/support/create">
                        <Plus class="mr-2 h-4 w-4" />
                        New Ticket
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Your support tickets</CardTitle>
                    <CardDescription
                        >View and manage your submitted tickets</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="ticket in props.tickets"
                            :key="ticket.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <Ticket class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/institution/support/${ticket.id}`"
                                            class="font-semibold hover:underline"
                                        >
                                            {{ ticket.subject }}
                                        </Link>
                                        <Badge
                                            :variant="statusVariant(ticket.status)"
                                            class="capitalize"
                                        >
                                            {{ ticket.status }}
                                        </Badge>
                                    </div>
                                    <div
                                        class="flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span
                                            >{{ ticket.replies_count }}
                                            {{ ticket.replies_count === 1 ? 'reply' : 'replies' }}</span
                                        >
                                        <span>•</span>
                                        <span
                                            >Updated
                                            {{ formatDate(ticket.updated_at) }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/institution/support/${ticket.id}`">
                                    View
                                </Link>
                            </Button>
                        </div>

                        <div
                            v-if="props.tickets.length === 0"
                            class="py-12 text-center"
                        >
                            <MessageSquarePlus
                                class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                            />
                            <p class="text-muted-foreground">
                                No support tickets yet
                            </p>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Submit a ticket if you need help from our team
                            </p>
                            <Button as-child class="mt-4">
                                <Link href="/institution/support/create">
                                    <Plus class="mr-2 h-4 w-4" />
                                    New Ticket
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
