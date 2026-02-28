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
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Eye, Mail, Search, Ticket, User } from 'lucide-vue-next';
import { ref } from 'vue';

interface InstitutionRef {
    id: number;
    name: string;
    slug: string;
}

interface TicketItem {
    id: number;
    subject: string;
    status: string;
    institution: InstitutionRef | null;
    user_name: string | null;
    user_email: string | null;
    replies_count: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    tickets: TicketItem[];
    filters: { search?: string; status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Support Tickets', href: '/admin/support' },
];

const searchQuery = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get('/admin/support', {
        search: searchQuery.value || undefined,
        status: statusFilter.value || undefined,
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

const formatDate = (iso: string) =>
    iso
        ? new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' })
        : '';
</script>

<template>
    <Head title="Support Tickets - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Support tickets</h1>
                    <p class="text-muted-foreground">
                        View and respond to institution support requests
                    </p>
                </div>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <div class="mb-4 flex flex-wrap gap-4">
                        <div class="relative min-w-[200px] flex-1">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search subject or message..."
                                class="pl-10"
                                @keydown.enter="applyFilters"
                            />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option value="">All statuses</option>
                            <option value="open">Open</option>
                            <option value="answered">Answered</option>
                            <option value="closed">Closed</option>
                        </select>
                        <Button @click="applyFilters">Apply</Button>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Tickets ({{ props.tickets.length }})</CardTitle>
                    <CardDescription
                        >Submitted by institution admins</CardDescription
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
                                            :href="`/admin/support/${ticket.id}`"
                                            class="font-semibold hover:underline"
                                        >
                                            {{ ticket.subject }}
                                        </Link>
                                        <Badge
                                            :variant="
                                                statusVariant(ticket.status)
                                            "
                                            class="capitalize"
                                        >
                                            {{ ticket.status }}
                                        </Badge>
                                    </div>
                                    <div
                                        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                                    >
                                        <span
                                            v-if="ticket.institution"
                                            class="flex items-center gap-1"
                                        >
                                            <Building2 class="h-4 w-4" />
                                            {{ ticket.institution.name }}
                                        </span>
                                        <span
                                            v-if="ticket.user_name"
                                            class="flex items-center gap-1"
                                        >
                                            <User class="h-4 w-4" />
                                            {{ ticket.user_name }}
                                        </span>
                                        <span
                                            v-if="ticket.user_email"
                                            class="flex items-center gap-1"
                                        >
                                            <Mail class="h-4 w-4" />
                                            {{ ticket.user_email }}
                                        </span>
                                        <span
                                            >{{ ticket.replies_count }}
                                            {{
                                                ticket.replies_count === 1
                                                    ? 'reply'
                                                    : 'replies'
                                            }}</span
                                        >
                                        <span>•</span>
                                        <span
                                            >Updated
                                            {{
                                                formatDate(ticket.updated_at)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/admin/support/${ticket.id}`">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View
                                </Link>
                            </Button>
                        </div>

                        <div
                            v-if="props.tickets.length === 0"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <Ticket class="mx-auto mb-4 h-12 w-12 opacity-50" />
                            <p>No support tickets match your filters.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
