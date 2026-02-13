<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useDomain } from '@/composables/useDomain';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Clock, GraduationCap, Mail } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    institution: {
        id: number;
        name: string;
        slug: string;
    };
}

const props = defineProps<Props>();
const { baseDomain } = useDomain();

const workspaceLoginUrl = computed(() => {
    const slug = props.institution?.slug;
    const domain = baseDomain.value;
    if (!slug || !domain) return '/login';
    const protocol =
        typeof window !== 'undefined' ? window.location.protocol : 'https:';
    return `${protocol}//${slug}.${domain}/login`;
});
</script>

<template>
    <Head title="Registration Submitted - TalentTune" />

    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav
            class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">TalentTune</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Success Message -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl">
                    <Card>
                        <CardContent class="pt-12 pb-12">
                            <div class="text-center">
                                <div
                                    class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20"
                                >
                                    <CheckCircle2
                                        class="h-8 w-8 text-green-600 dark:text-green-400"
                                    />
                                </div>

                                <h1
                                    class="mb-4 text-3xl font-bold tracking-tight"
                                >
                                    Registration Submitted Successfully!
                                </h1>

                                <p class="mb-8 text-lg text-muted-foreground">
                                    Thank you for registering
                                    <strong>{{ institution.name }}</strong> with
                                    TalentTune.
                                </p>

                                <div
                                    class="mb-8 rounded-lg bg-muted p-6 text-left"
                                >
                                    <div class="flex items-start gap-4">
                                        <Clock
                                            class="mt-0.5 h-5 w-5 text-muted-foreground"
                                        />
                                        <div>
                                            <h3 class="mb-2 font-semibold">
                                                What Happens Next?
                                            </h3>
                                            <ul
                                                class="space-y-2 text-sm text-muted-foreground"
                                            >
                                                <li>
                                                    • Our admin team will review
                                                    your registration
                                                </li>
                                                <li>
                                                    • You'll receive an email
                                                    notification once your
                                                    account is activated
                                                </li>
                                                <li>
                                                    • Once activated, access
                                                    your portal at:
                                                    <code
                                                        class="rounded bg-background px-2 py-1"
                                                        >{{
                                                            institution.slug
                                                        }}.{{
                                                            baseDomain
                                                        }}</code
                                                    >
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="mb-8 rounded-lg bg-blue-50 p-6 text-left dark:bg-blue-900/20"
                                >
                                    <div class="flex items-start gap-4">
                                        <Mail
                                            class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400"
                                        />
                                        <div>
                                            <h3
                                                class="mb-2 font-semibold text-blue-900 dark:text-blue-100"
                                            >
                                                Check Your Email
                                            </h3>
                                            <p
                                                class="text-sm text-blue-800 dark:text-blue-200"
                                            >
                                                We've sent a confirmation email
                                                with your registration details.
                                                Please check your inbox (and
                                                spam folder) for updates.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-col justify-center gap-4 sm:flex-row"
                                >
                                    <Link href="/">
                                        <Button variant="outline">
                                            Back to Home
                                        </Button>
                                    </Link>
                                    <a :href="workspaceLoginUrl">
                                        <Button
                                            >Sign In (your workspace)</Button
                                        >
                                    </a>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </div>
</template>
