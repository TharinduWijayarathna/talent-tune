<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CreditCard, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    institution: { id: number; name: string; slug: string };
    checkoutUrl: string;
}

const props = defineProps<Props>();
const submitting = ref(false);
const page = usePage();

const submit = () => {
    submitting.value = true;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = props.checkoutUrl;
    const csrf = (page.props as { csrfToken?: string }).csrfToken;
    if (csrf) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrf;
        form.appendChild(input);
    }
    const instInput = document.createElement('input');
    instInput.type = 'hidden';
    instInput.name = 'institution_id';
    instInput.value = String(props.institution.id);
    form.appendChild(instInput);
    document.body.appendChild(form);
    form.submit();
};
</script>

<template>
    <Head title="Complete your subscription - TalentTune" />
    <div class="min-h-screen bg-background">
        <nav
            class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <img
                            src="/images/logo.png"
                            alt="TalentTune"
                            class="h-9 w-auto object-contain"
                        />
                    </Link>
                </div>
            </div>
        </nav>
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-lg">
                    <Card>
                        <CardContent class="pt-12 pb-12">
                            <div class="text-center">
                                <div
                                    class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <CreditCard class="h-8 w-8 text-primary" />
                                </div>
                                <h1
                                    class="mb-2 text-2xl font-bold tracking-tight"
                                >
                                    Complete your subscription
                                </h1>
                                <p class="mb-8 text-muted-foreground">
                                    <strong>{{ institution.name }}</strong> –
                                    complete your monthly subscription to
                                    activate the workspace.
                                </p>
                                <p class="mb-6 text-sm text-muted-foreground">
                                    You will be redirected to Stripe to pay
                                    securely.
                                </p>
                                <Button
                                    size="lg"
                                    :disabled="submitting"
                                    @click="submit"
                                >
                                    <Loader2
                                        v-if="submitting"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    <CreditCard v-else class="mr-2 h-4 w-4" />
                                    {{
                                        submitting
                                            ? 'Redirecting...'
                                            : 'Pay with Stripe'
                                    }}
                                </Button>
                                <div class="mt-8">
                                    <Link
                                        href="/"
                                        class="text-sm text-muted-foreground hover:underline"
                                        >Back to home</Link
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </div>
</template>
