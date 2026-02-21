<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import SiteHeader from '@/components/SiteHeader.vue';
import { registerInstitution } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Building2, Target, Zap } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const isMainDomain = computed(() => !page.props.institution);
</script>

<template>
    <Head title="About - TalentTune" />
    <div class="min-h-screen bg-background">
        <SiteHeader :is-main-domain="isMainDomain" />
        <main class="py-16 sm:py-24">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl">
                    <Badge variant="outline" class="mb-4">About</Badge>
                    <h1
                        class="mb-6 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Built for education, powered by AI
                    </h1>
                    <p class="mb-8 text-lg leading-relaxed text-muted-foreground">
                        TalentTune is an AI-powered viva management platform
                        designed for universities and colleges. We help
                        institutions run fairer, faster, and more consistent
                        oral examinations while giving students clearer
                        feedback and lecturers more time to teach.
                    </p>

                    <div class="space-y-8">
                        <div class="flex gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                            >
                                <Target class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <h2 class="mb-2 font-semibold">Our mission</h2>
                                <p class="text-muted-foreground">
                                    To make viva examinations scalable, consistent,
                                    and insightful so every student gets a fair
                                    assessment and every institution can focus
                                    on learning outcomes.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                            >
                                <Zap class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <h2 class="mb-2 font-semibold">What we do</h2>
                                <p class="text-muted-foreground">
                                    We combine AI question generation, voice
                                    interaction, and automated evaluation in one
                                    platform. Institutions get their own
                                    subdomain and branding while we handle
                                    security, updates, and scale.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                            >
                                <Building2 class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <h2 class="mb-2 font-semibold">For institutions</h2>
                                <p class="text-muted-foreground">
                                    Whether you're a single department or a
                                    large university, TalentTune adapts. Register
                                    your institution, get verified, and start
                                    running vivas with AI support in days.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12">
                        <Link :href="registerInstitution.url()">
                            <Button size="lg" class="gap-2">
                                Register your institution
                                <Building2 class="h-4 w-4" />
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
