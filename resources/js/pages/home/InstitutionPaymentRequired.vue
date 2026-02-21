<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, usePage } from '@inertiajs/vue3';
import { CreditCard, GraduationCap, LogIn } from 'lucide-vue-next';

interface Props {
    institution: {
        name: string;
        slug: string;
        login_url: string;
    };
}

const props = defineProps<Props>();
const page = usePage();
const baseDomain = (page.props as { baseDomain?: string }).baseDomain ?? '';
const loginUrl =
    props.institution.login_url ||
    `https://${props.institution.slug}.${baseDomain}/login`;
</script>

<template>
    <Head :title="`${institution.name} - Payment required`" />

    <div class="min-h-screen bg-background">
        <nav
            class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">{{
                            institution.name
                        }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl">
                    <Card>
                        <CardContent class="pt-12 pb-12">
                            <div class="text-center">
                                <div
                                    class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <CreditCard class="h-8 w-8 text-primary" />
                                </div>
                                <h1
                                    class="mb-4 text-3xl font-bold tracking-tight"
                                >
                                    Complete payment to access the workspace
                                </h1>
                                <p class="mb-8 text-lg text-muted-foreground">
                                    <strong>{{ institution.name }}</strong> has
                                    been activated. You must complete the
                                    subscription payment before you can access
                                    this workspace.
                                </p>
                                <div
                                    class="mb-8 rounded-lg bg-muted p-6 text-left"
                                >
                                    <p class="text-sm text-muted-foreground">
                                        Log in as the institution admin below.
                                        After payment is completed, the
                                        dashboard and all features will be
                                        available.
                                    </p>
                                </div>
                                <a :href="loginUrl">
                                    <Button size="lg">
                                        <LogIn class="mr-2 h-4 w-4" />
                                        Log in as institution admin
                                    </Button>
                                </a>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </div>
</template>
