<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Head, usePage } from '@inertiajs/vue3';
import { CheckCircle2, Clock, GraduationCap, Mail } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    institution?: {
        id: number;
        name: string;
        slug: string;
        email?: string;
        contact_person?: string;
    } | null;
}

const props = defineProps<Props>();
const page = usePage();

const institution = computed(
    () => props.institution || (page.props as any).institution,
);
const institutionName = computed(
    () => institution.value?.name || 'Your Institution',
);
</script>

<template>
    <Head :title="`${institutionName} - Activation Pending`" />

    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav
            class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">{{
                            institutionName
                        }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Pending Activation Message -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl">
                    <Card>
                        <CardContent class="pt-12 pb-12">
                            <div class="text-center">
                                <div
                                    class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/20"
                                >
                                    <Clock
                                        class="h-8 w-8 text-yellow-600 dark:text-yellow-400"
                                    />
                                </div>

                                <h1
                                    class="mb-4 text-3xl font-bold tracking-tight"
                                >
                                    Activation Pending
                                </h1>

                                <p class="mb-8 text-lg text-muted-foreground">
                                    Thank you for registering
                                    <strong>{{ institutionName }}</strong> with
                                    TalentTune.
                                </p>

                                <div
                                    class="mb-8 rounded-lg bg-muted p-6 text-left"
                                >
                                    <div class="flex items-start gap-4">
                                        <CheckCircle2
                                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-primary"
                                        />
                                        <div>
                                            <h3 class="mb-2 font-semibold">
                                                What Happens Next?
                                            </h3>
                                            <ul
                                                class="space-y-2 text-sm text-muted-foreground"
                                            >
                                                <li>
                                                    • Our admin team is
                                                    reviewing your registration
                                                </li>
                                                <li>
                                                    • You'll receive an email
                                                    notification once your
                                                    account is activated
                                                </li>
                                                <li>
                                                    • The email will include
                                                    your login credentials
                                                </li>
                                                <li>
                                                    • Once activated, you can
                                                    access your portal at:
                                                    <code
                                                        class="rounded bg-background px-2 py-1"
                                                        >{{
                                                            institution?.slug
                                                        }}.{{
                                                            (page.props as any)
                                                                .baseDomain ||
                                                            'talenttune.com'
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
                                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400"
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
                                                <span v-if="institution?.email">
                                                    We'll send the activation
                                                    notification to
                                                    <strong>{{
                                                        institution.email
                                                    }}</strong
                                                    >.
                                                </span>
                                                <span v-else>
                                                    We'll send the activation
                                                    notification to the email
                                                    address you provided during
                                                    registration.
                                                </span>
                                                <br /><br />
                                                Please check your inbox (and
                                                spam folder) for updates.
                                                Activation typically takes 24-48
                                                hours.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm text-muted-foreground">
                                    <p>
                                        If you have any questions, please
                                        contact our support team.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </div>
</template>
