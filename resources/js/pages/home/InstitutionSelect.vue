<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import type { Institution } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Building2, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    institutions: Array<{
        id: number;
        name: string;
        slug: string;
        logo_url?: string | null;
    }>;
}

const props = defineProps<Props>();

const searchQuery = ref('');

const filteredInstitutions = computed(() => {
    if (!searchQuery.value) {
        return props.institutions;
    }

    const query = searchQuery.value.toLowerCase();
    return props.institutions.filter(
        (inst) =>
            inst.name.toLowerCase().includes(query) ||
            inst.slug.toLowerCase().includes(query),
    );
});

const selectInstitution = (institution: Institution) => {
    // Redirect to institution subdomain or path
    const host = window.location.host;
    const protocol = window.location.protocol;

    // Check if we're on a subdomain already
    const parts = host.split('.');

    if (parts.length >= 3 || host.includes('.test')) {
        // Replace subdomain
        const baseDomain = parts.slice(-2).join('.');
        const newHost = `${institution.slug}.${baseDomain}`;
        window.location.href = `${protocol}//${newHost}/`;
    } else {
        // Use path-based routing
        router.visit(`/${institution.slug}/`);
    }
};
</script>

<template>
    <Head title="Select Your Institution - TalentTune" />

    <div class="min-h-screen bg-background">
        <!-- Navigation -->
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
                    <div class="flex items-center gap-4">
                        <Link
                            :href="'/login'"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 sm:py-32">
            <div
                class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-background to-background"
            />
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <h1
                        class="mb-6 text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl"
                    >
                        Select Your
                        <span
                            class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent"
                        >
                            Institution
                        </span>
                    </h1>
                    <p
                        class="mb-10 text-lg text-muted-foreground sm:text-xl lg:text-2xl"
                    >
                        Choose your institution to access TalentTune's
                        AI-powered viva management platform.
                    </p>

                    <!-- Search -->
                    <div class="mx-auto mb-8 max-w-md">
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground"
                            />
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search institutions..."
                                class="pl-10"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Institutions Grid -->
        <section class="py-12">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    v-if="filteredInstitutions.length === 0"
                    class="py-12 text-center"
                >
                    <Building2
                        class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                    />
                    <p class="text-lg text-muted-foreground">
                        No institutions found matching your search.
                    </p>
                </div>

                <div
                    v-else
                    class="mx-auto grid max-w-6xl gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="institution in filteredInstitutions"
                        :key="institution.id"
                        class="cursor-pointer transition-shadow hover:shadow-lg"
                        @click="selectInstitution(institution)"
                    >
                        <CardHeader>
                            <div class="mb-4 flex items-center gap-4">
                                <div
                                    v-if="institution.logo_url"
                                    class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-muted"
                                >
                                    <img
                                        :src="institution.logo_url"
                                        :alt="institution.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10"
                                >
                                    <Building2 class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <CardTitle class="text-lg">{{
                                        institution.name
                                    }}</CardTitle>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">{{
                                    institution.slug
                                }}</span>
                                <ArrowRight
                                    class="h-4 w-4 text-muted-foreground"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="mt-20 border-t bg-background py-12">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>
                        &copy; {{ new Date().getFullYear() }} TalentTune. All
                        rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
