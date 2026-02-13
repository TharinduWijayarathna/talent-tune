<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { CreditCard, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    institution: { id: number; name: string; slug: string };
}

defineProps<Props>();
const submitting = ref(false);
const page = usePage();

const checkout = () => {
    submitting.value = true;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/institution/complete-subscription/checkout';
    const csrf = (page.props as { csrfToken?: string }).csrfToken;
    if (csrf) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrf;
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
};
</script>

<template>
    <Head title="Complete your subscription - TalentTune" />
    <AppLayout :breadcrumbs="[]">
        <div class="flex h-full flex-1 flex-col items-center justify-center p-4">
            <Card class="w-full max-w-lg">
                <CardContent class="pt-12 pb-12">
                    <div class="text-center">
                        <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                            <CreditCard class="h-8 w-8 text-primary" />
                        </div>
                        <h1 class="mb-2 text-2xl font-bold tracking-tight">Complete your subscription</h1>
                        <p class="mb-6 text-muted-foreground">
                            Your institution <strong>{{ institution.name }}</strong> has been activated. Complete your monthly subscription payment to access the dashboard and all features.
                        </p>
                        <p class="mb-8 text-sm text-muted-foreground">
                            You will be redirected to Stripe to pay securely.
                        </p>
                        <Button size="lg" :disabled="submitting" @click="checkout">
                            <Loader2 v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
                            <CreditCard v-else class="mr-2 h-4 w-4" />
                            {{ submitting ? 'Redirecting...' : 'Pay with Stripe' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
