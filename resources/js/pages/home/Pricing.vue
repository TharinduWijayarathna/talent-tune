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
import SiteHeader from '@/components/SiteHeader.vue';
import { registerInstitution } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const isMainDomain = computed(() => !page.props.institution);

const plans = [
    {
        name: 'Starter',
        description: 'For small departments and pilot programs.',
        price: 'Contact us',
        features: [
            'Up to 100 students',
            'AI question generation',
            'Basic analytics',
            'Email support',
        ],
        cta: 'Get in touch',
        highlighted: false,
    },
    {
        name: 'Institution',
        description: 'For universities and colleges.',
        price: 'Custom',
        features: [
            'Unlimited students',
            'All AI features',
            'Multi-tenant subdomain',
            'Priority support',
            'Custom branding',
        ],
        cta: 'Get Started',
        highlighted: true,
    },
];
</script>

<template>
    <Head title="Pricing - TalentTune" />
    <div class="min-h-screen bg-background">
        <SiteHeader :is-main-domain="isMainDomain" />
        <main class="py-16 sm:py-24">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <Badge variant="outline" class="mb-4">Pricing</Badge>
                    <h1
                        class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Simple, transparent pricing
                    </h1>
                    <p class="text-lg text-muted-foreground">
                        Choose the right plan for your institution. Volume and
                        custom needs? We'll tailor a solution.
                    </p>
                </div>
                <div
                    class="mx-auto grid max-w-4xl gap-8 sm:grid-cols-2"
                >
                    <Card
                        v-for="(plan, index) in plans"
                        :key="index"
                        :class="[
                            'flex flex-col',
                            plan.highlighted
                                ? 'border-primary shadow-lg ring-2 ring-primary/20'
                                : 'border-2',
                        ]"
                    >
                        <CardHeader>
                            <CardTitle class="text-xl">{{ plan.name }}</CardTitle>
                            <CardDescription>{{ plan.description }}</CardDescription>
                            <div class="mt-4 text-2xl font-bold">
                                {{ plan.price }}
                            </div>
                        </CardHeader>
                        <CardContent class="flex flex-1 flex-col">
                            <ul class="mb-6 space-y-3">
                                <li
                                    v-for="(feature, i) in plan.features"
                                    :key="i"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <Check class="h-4 w-4 shrink-0 text-primary" />
                                    {{ feature }}
                                </li>
                            </ul>
                            <Link
                                :href="registerInstitution.url()"
                                class="mt-auto"
                            >
                                <Button
                                    :variant="plan.highlighted ? 'default' : 'outline'"
                                    class="w-full"
                                >
                                    {{ plan.cta }}
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </main>
    </div>
</template>
