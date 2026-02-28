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
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Calendar, CreditCard, Loader2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface SubscriptionInfo {
    status: string;
    current_period_end: number;
    cancel_at_period_end: boolean;
}

const props = defineProps<{
    institution: { name: string; subscription_status: string };
    subscription: SubscriptionInfo | null;
}>();

const page = usePage();
const flash = computed(
    () => (page.props.flash as { success?: string; error?: string }) ?? {},
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Payment', href: '/institution/payment' },
];

const cancelling = ref(false);

const periodEndDate = computed(() => {
    const sub = props.subscription;
    if (!sub?.current_period_end) return null;
    return new Date(sub.current_period_end * 1000).toLocaleDateString(
        undefined,
        {
            dateStyle: 'long',
        },
    );
});

const endSubscription = () => {
    if (
        !confirm(
            'Are you sure you want to end your subscription? You will keep access until the end of the current billing period, then you will need to subscribe again to continue using the workspace.',
        )
    ) {
        return;
    }
    cancelling.value = true;
    router.post(
        '/institution/payment/cancel',
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                cancelling.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Payment - TalentTune" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div>
                <h1 class="text-2xl font-bold">Payment</h1>
                <p class="text-muted-foreground">
                    Manage your subscription and billing
                </p>
            </div>

            <div
                v-if="flash.success"
                class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
            >
                {{ flash.error }}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <CreditCard class="h-5 w-5" />
                        Subscription
                    </CardTitle>
                    <CardDescription>
                        Your current plan and billing period
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div v-if="subscription" class="space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <Badge variant="default" class="capitalize">
                                {{ subscription.status }}
                            </Badge>
                            <Badge
                                v-if="subscription.cancel_at_period_end"
                                variant="secondary"
                            >
                                Ending at period end
                            </Badge>
                        </div>

                        <div
                            v-if="periodEndDate"
                            class="flex items-center gap-2 text-sm"
                        >
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            <span v-if="subscription.cancel_at_period_end">
                                Your subscription will end on
                                <strong>{{ periodEndDate }}</strong
                                >. You will keep access until then.
                            </span>
                            <span v-else>
                                Current period ends on
                                <strong>{{ periodEndDate }}</strong>
                            </span>
                        </div>

                        <div
                            v-if="!subscription.cancel_at_period_end"
                            class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950"
                        >
                            <p
                                class="mb-3 flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-200"
                            >
                                <AlertTriangle class="h-4 w-4" />
                                End subscription
                            </p>
                            <p
                                class="mb-4 text-sm text-amber-700 dark:text-amber-300"
                            >
                                If you end your subscription, you will keep
                                access until the end of the current billing
                                period. After that, you will need to complete
                                payment again to access the workspace.
                            </p>
                            <Button
                                variant="outline"
                                class="border-amber-300 text-amber-800 hover:bg-amber-100 dark:border-amber-700 dark:text-amber-200 dark:hover:bg-amber-900"
                                :disabled="cancelling"
                                @click="endSubscription"
                            >
                                <Loader2
                                    v-if="cancelling"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                End subscription at period end
                            </Button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-lg border bg-muted/30 p-6 text-center"
                    >
                        <p class="text-muted-foreground">
                            No active subscription. Complete payment to access
                            the workspace.
                        </p>
                        <Button as-child class="mt-4">
                            <Link href="/institution/complete-subscription">
                                Complete payment
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
