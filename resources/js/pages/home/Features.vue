<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import SiteHeader from '@/components/SiteHeader.vue';
import { registerInstitution } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Brain,
    Building2,
    Clock,
    Mic,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const isMainDomain = computed(() => !page.props.institution);

const features = [
    {
        icon: Brain,
        title: 'AI Question Generation',
        description:
            'Generate intelligent, context-aware questions using advanced AI models tailored to your subject matter. Supports multiple difficulty levels and topics.',
        color: 'from-blue-500 to-cyan-500',
    },
    {
        icon: Mic,
        title: 'Voice-Based Interaction',
        description:
            'Natural voice interaction with high-quality text-to-speech and speech recognition for seamless viva sessions. Real-time transcription available.',
        color: 'from-purple-500 to-pink-500',
    },
    {
        icon: BarChart3,
        title: 'Automated Evaluation',
        description:
            'Get instant, detailed feedback and scoring powered by AI, ensuring consistent and objective assessments with comprehensive analytics.',
        color: 'from-green-500 to-emerald-500',
    },
    {
        icon: Users,
        title: 'Multi-Tenant Architecture',
        description:
            'Each institution gets its own isolated environment with custom branding, dedicated subdomain, and complete data separation.',
        color: 'from-orange-500 to-red-500',
    },
    {
        icon: Clock,
        title: 'Session Scheduling',
        description:
            'Easy scheduling and management of viva sessions with calendar integration, automated reminders, and batch management.',
        color: 'from-indigo-500 to-blue-500',
    },
    {
        icon: Shield,
        title: 'Secure & Reliable',
        description:
            'Enterprise-grade security with data encryption, secure authentication, two-factor auth, and compliance with educational standards.',
        color: 'from-teal-500 to-green-500',
    },
];
</script>

<template>
    <Head title="Features - TalentTune" />
    <div class="min-h-screen bg-background">
        <SiteHeader :is-main-domain="isMainDomain" />
        <main class="py-16 sm:py-24">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <Badge variant="outline" class="mb-4">Features</Badge>
                    <h1
                        class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Everything Your Institution Needs
                    </h1>
                    <p class="text-lg text-muted-foreground">
                        Powerful features designed to make viva examinations
                        efficient, fair, and insightful.
                    </p>
                </div>
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group border-2 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br transition-transform group-hover:scale-110"
                                :class="feature.color"
                            >
                                <component
                                    :is="feature.icon"
                                    class="h-6 w-6 text-white"
                                />
                            </div>
                            <CardTitle
                                class="transition-colors group-hover:text-primary"
                            >
                                {{ feature.title }}
                            </CardTitle>
                            <CardDescription
                                class="text-base leading-relaxed"
                            >
                                {{ feature.description }}
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </div>
                <div class="mt-16 flex justify-center">
                    <Link :href="registerInstitution.url()">
                        <Button size="lg" class="gap-2">
                            Get Started with TalentTune
                            <Building2 class="h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
