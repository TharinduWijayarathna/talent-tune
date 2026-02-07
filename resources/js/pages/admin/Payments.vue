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
import {
    Building2,
    Check,
    ChevronLeft,
    ChevronRight,
    Clock,
    CreditCard,
    DollarSign,
    Search,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface InstitutionRef {
    id: number;
    name: string;
    slug: string;
}

interface PaymentItem {
    id: number;
    amount: number;
    currency: string;
    status: string;
    gateway: string | null;
    external_id: string | null;
    paid_at: string | null;
    created_at: string;
    institution: InstitutionRef | null;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPayments {
    data: PaymentItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    first_page_url: string;
    last_page_url: string;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginatorLink[];
}

interface Props {
    payments: PaginatedPayments;
    stats: {
        total: number;
        completed: number;
        pending: number;
        total_amount: number;
    };
    filters: { search?: string; status?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Payments', href: '/admin/payments' },
];

const searchQuery = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        '/admin/payments',
        {
            search: searchQuery.value || undefined,
            status: statusFilter.value || undefined,
            page: 1,
        },
        { preserveState: true },
    );
};

function formatAmount(amount: number, currency: string): string {
    const value = amount / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency || 'USD',
    }).format(value);
}

const statusVariant: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    completed: 'default',
    pending: 'secondary',
    failed: 'destructive',
    refunded: 'outline',
};
</script>

<template>
    <Head title="Manage Payments - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Payment Management</h1>
                    <p class="text-muted-foreground">
                        View and manage payments across the platform
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Payments</CardTitle
                        >
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Completed</CardTitle
                        >
                        <Check class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.completed }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Pending</CardTitle
                        >
                        <Clock class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ stats.pending }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Revenue</CardTitle
                        >
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatAmount(stats.total_amount, 'USD') }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative min-w-[200px] flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by institution name or slug..."
                        class="pl-10"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <select
                    v-model="statusFilter"
                    class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
                <Button @click="applyFilters">Apply</Button>
            </div>

            <!-- Payments List -->
            <Card>
                <CardHeader>
                    <CardTitle>Payments</CardTitle>
                    <CardDescription
                        >Payment history and transactions</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="payment in payments.data"
                            :key="payment.id"
                            class="flex items-start justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="font-semibold">
                                            {{
                                                formatAmount(
                                                    payment.amount,
                                                    payment.currency,
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ payment.gateway ?? '—' }}
                                            <span
                                                v-if="payment.external_id"
                                                class="ml-2"
                                                >{{ payment.external_id }}</span
                                            >
                                        </p>
                                    </div>
                                    <Badge
                                        :variant="
                                            statusVariant[payment.status] ??
                                            'secondary'
                                        "
                                    >
                                        {{ payment.status }}
                                    </Badge>
                                </div>
                                <div
                                    v-if="payment.institution"
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Building2 class="h-4 w-4" />
                                    {{ payment.institution.name }}
                                </div>
                                <div
                                    class="flex gap-4 text-xs text-muted-foreground"
                                >
                                    <span
                                        >Created
                                        {{
                                            new Date(
                                                payment.created_at,
                                            ).toLocaleString()
                                        }}</span
                                    >
                                    <span v-if="payment.paid_at"
                                        >Paid
                                        {{
                                            new Date(
                                                payment.paid_at,
                                            ).toLocaleString()
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="payments.data.length === 0"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <CreditCard
                                class="mx-auto mb-4 h-12 w-12 opacity-50"
                            />
                            <p>No payments found.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="payments.last_page > 1"
                        class="mt-6 flex items-center justify-between border-t pt-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            Page {{ payments.current_page }} of
                            {{ payments.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-if="payments.prev_page_url"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="payments.prev_page_url">
                                    <ChevronLeft class="mr-1 h-4 w-4" />
                                    Previous
                                </Link>
                            </Button>
                            <Button
                                v-if="payments.next_page_url"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="payments.next_page_url">
                                    Next
                                    <ChevronRight class="ml-1 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
