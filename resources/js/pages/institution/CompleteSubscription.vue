<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { logout } from '@/routes';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    CreditCard,
    Loader2,
    Lock,
    LogOut,
    Shield,
} from 'lucide-vue-next';
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

const handleLogout = () => {
    router.post(logout.url());
};
</script>

<template>
    <Head title="Complete payment - TalentTune" />

    <div class="min-h-svh bg-gradient-to-b from-background to-muted/30">
        <!-- Header -->
        <header
            class="sticky top-0 z-50 border-b bg-background/80 backdrop-blur-md"
        >
            <div class="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-3">
                    <img
                        src="/images/logo.png"
                        alt="TalentTune"
                        class="h-9 w-auto object-contain"
                    />
                    <span class="hidden text-sm font-medium text-muted-foreground sm:inline">
                        {{ institution.name }}
                    </span>
                </Link>
                <Button variant="ghost" size="sm" @click="handleLogout">
                    <LogOut class="mr-2 h-4 w-4" />
                    Sign out
                </Button>
            </div>
        </header>

        <!-- Main content -->
        <main class="container mx-auto px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-xl">
                <div class="text-center">
                    <div
                        class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-primary/10"
                    >
                        <CreditCard class="h-10 w-10 text-primary" />
                    </div>
                    <h1 class="mb-3 text-3xl font-bold tracking-tight sm:text-4xl">
                        Complete payment to access your workspace
                    </h1>
                    <p class="mb-12 text-lg text-muted-foreground">
                        <strong>{{ institution.name }}</strong> has been
                        activated. Complete your subscription payment to unlock
                        the dashboard and all features.
                    </p>
                </div>

                <Card class="overflow-hidden border-2 shadow-lg">
                    <CardContent class="p-0">
                        <div class="border-b bg-muted/30 px-6 py-4">
                            <h2 class="font-semibold">Monthly subscription</h2>
                            <p class="text-sm text-muted-foreground">
                                Secure payment via Stripe
                            </p>
                        </div>
                        <div class="space-y-6 p-6">
                            <ul class="space-y-4">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Check class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Full dashboard access</span
                                        >
                                        <p class="text-sm text-muted-foreground">
                                            Manage batches, lecturers, and
                                            students
                                        </p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Shield class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Secure payment</span
                                        >
                                        <p class="text-sm text-muted-foreground">
                                            Powered by Stripe – your data is
                                            protected
                                        </p>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Lock class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Instant access</span
                                        >
                                        <p class="text-sm text-muted-foreground">
                                            Redirect to dashboard immediately
                                            after payment
                                        </p>
                                    </div>
                                </li>
                            </ul>

                            <div class="pt-4">
                                <Button
                                    size="lg"
                                    class="w-full"
                                    :disabled="submitting"
                                    @click="checkout"
                                >
                                    <Loader2
                                        v-if="submitting"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    <ArrowRight
                                        v-else
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{
                                        submitting
                                            ? 'Redirecting to payment...'
                                            : 'Continue to payment'
                                    }}
                                </Button>
                                <p class="mt-4 text-center text-xs text-muted-foreground">
                                    You will be redirected to Stripe to complete
                                    payment securely.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <p class="mt-8 text-center text-sm text-muted-foreground">
                    Questions? Contact
                    <a
                        href="mailto:support@talenttune.com"
                        class="underline hover:text-foreground"
                        >support@talenttune.com</a
                    >
                </p>
            </div>
        </main>
    </div>
</template>
