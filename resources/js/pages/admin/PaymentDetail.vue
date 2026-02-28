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
import {
    ArrowLeft,
    Building2,
    CreditCard,
    ExternalLink,
    FileJson,
} from 'lucide-vue-next';

interface InstitutionRef {
    id: number;
    name: string;
    slug: string;
    email: string | null;
}

interface PaymentDetail {
    id: number;
    amount: number;
    currency: string;
    status: string;
    gateway: string | null;
    external_id: string | null;
    paid_at: string | null;
    created_at: string;
    updated_at: string;
    metadata: Record<string, unknown> | null;
    institution: InstitutionRef | null;
}

const props = defineProps<{ payment: PaymentDetail }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Payments', href: '/admin/payments' },
    { title: `Payment #${props.payment.id}`, href: '#' },
];

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
    <Head :title="`Payment #${payment.id} - Admin`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/admin/payments">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold">
                            Payment #{{ payment.id }}
                        </h1>
                        <p class="text-muted-foreground">
                            Full payment details and metadata
                        </p>
                    </div>
                </div>
                <Badge :variant="statusVariant[payment.status] ?? 'secondary'">
                    {{ payment.status }}
                </Badge>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Payment summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            Payment summary
                        </CardTitle>
                        <CardDescription
                            >Amount and gateway info</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Amount</span>
                            <span class="font-semibold">
                                {{
                                    formatAmount(
                                        payment.amount,
                                        payment.currency,
                                    )
                                }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Currency</span>
                            <span>{{ payment.currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Gateway</span>
                            <span>{{ payment.gateway ?? '—' }}</span>
                        </div>
                        <div
                            v-if="payment.external_id"
                            class="flex items-center justify-between gap-2"
                        >
                            <span class="text-muted-foreground"
                                >External ID</span
                            >
                            <span
                                class="max-w-[180px] truncate font-mono text-sm"
                                :title="payment.external_id"
                            >
                                {{ payment.external_id }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created</span>
                            <span class="text-sm">
                                {{
                                    new Date(
                                        payment.created_at,
                                    ).toLocaleString()
                                }}
                            </span>
                        </div>
                        <div
                            v-if="payment.paid_at"
                            class="flex justify-between"
                        >
                            <span class="text-muted-foreground">Paid at</span>
                            <span class="text-sm">
                                {{ new Date(payment.paid_at).toLocaleString() }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground"
                                >Last updated</span
                            >
                            <span class="text-sm">
                                {{
                                    new Date(
                                        payment.updated_at,
                                    ).toLocaleString()
                                }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Institution -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            Institution
                        </CardTitle>
                        <CardDescription>Paying institution</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <template v-if="payment.institution">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Name</span>
                                <span class="font-medium">{{
                                    payment.institution.name
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Slug</span>
                                <span class="font-mono text-sm">{{
                                    payment.institution.slug
                                }}</span>
                            </div>
                            <div
                                v-if="payment.institution.email"
                                class="flex justify-between"
                            >
                                <span class="text-muted-foreground">Email</span>
                                <a
                                    :href="`mailto:${payment.institution.email}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ payment.institution.email }}
                                </a>
                            </div>
                            <Link
                                :href="`/admin/institutions/${payment.institution.id}/edit`"
                            >
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                >
                                    <ExternalLink class="mr-2 h-4 w-4" />
                                    View institution
                                </Button>
                            </Link>
                        </template>
                        <p v-else class="text-sm text-muted-foreground">
                            No institution linked.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Metadata -->
            <Card
                v-if="
                    payment.metadata && Object.keys(payment.metadata).length > 0
                "
            >
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileJson class="h-5 w-5" />
                        Metadata
                    </CardTitle>
                    <CardDescription
                        >Gateway or custom metadata</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <pre
                        class="overflow-x-auto rounded-lg border bg-muted/50 p-4 text-xs"
                        >{{ JSON.stringify(payment.metadata, null, 2) }}</pre
                    >
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
