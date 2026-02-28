<script setup lang="ts">
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
import { CreditCard, FileDown, TrendingDown } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Reports', href: '/admin/reports' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reports</h1>
                <p class="text-muted-foreground">
                    Download PDF reports for payments and profit &amp; loss.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            Payments Report
                        </CardTitle>
                        <CardDescription>
                            All payments with institution, amount, status and
                            date.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Link
                            :href="'/admin/reports/payments-pdf'"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <FileDown class="h-4 w-4" />
                            Download PDF
                        </Link>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingDown class="h-5 w-5" />
                            Profit &amp; Loss Report
                        </CardTitle>
                        <CardDescription>
                            Revenue ($99 per payment), costs (API + Google TTS
                            $45 per subscriber), and profit/loss.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Link
                            :href="'/admin/reports/profit-loss-pdf'"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <FileDown class="h-4 w-4" />
                            Download PDF
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
