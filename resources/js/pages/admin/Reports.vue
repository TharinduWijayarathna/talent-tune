<script setup lang="ts">
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
import { Head } from '@inertiajs/vue3';
import { CreditCard, FileDown, Loader2, TrendingDown } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Reports', href: '/admin/reports' },
];

const downloading = ref<'payments' | 'profit-loss' | null>(null);

async function downloadPdf(
    url: string,
    filename: string,
    key: 'payments' | 'profit-loss',
) {
    downloading.value = key;
    try {
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Download failed');
        const blob = await res.blob();
        const blobUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(blobUrl);
    } finally {
        downloading.value = null;
    }
}

const today = new Date().toISOString().slice(0, 10);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Reports</h1>
                    <p class="text-muted-foreground">
                        Download PDF reports for payments and profit &amp; loss.
                    </p>
                </div>
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
                        <Button
                            :disabled="!!downloading"
                            class="inline-flex items-center gap-2"
                            @click="
                                downloadPdf(
                                    '/admin/reports/payments-pdf',
                                    `talenttune-payments-report-${today}.pdf`,
                                    'payments',
                                )
                            "
                        >
                            <Loader2
                                v-if="downloading === 'payments'"
                                class="h-4 w-4 animate-spin"
                            />
                            <FileDown v-else class="h-4 w-4" />
                            Download PDF
                        </Button>
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
                        <Button
                            :disabled="!!downloading"
                            class="inline-flex items-center gap-2"
                            @click="
                                downloadPdf(
                                    '/admin/reports/profit-loss-pdf',
                                    `talenttune-profit-loss-report-${today}.pdf`,
                                    'profit-loss',
                                )
                            "
                        >
                            <Loader2
                                v-if="downloading === 'profit-loss'"
                                class="h-4 w-4 animate-spin"
                            />
                            <FileDown v-else class="h-4 w-4" />
                            Download PDF
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
