<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { login, registerInstitution } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps<{
    isMainDomain?: boolean;
}>();
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-border/40 bg-background/95 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <Link
                    href="/"
                    class="flex shrink-0 items-center gap-2 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    aria-label="TalentTune home"
                >
                    <img
                        src="/images/logo.png"
                        alt="TalentTune"
                        class="h-9 w-auto object-contain"
                    />
                </Link>
                <nav
                    class="absolute left-1/2 flex -translate-x-1/2 items-center gap-6"
                    aria-label="Main"
                >
                    <Link
                        href="/"
                        class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded"
                    >
                        Home
                    </Link>
                    <Link
                        href="/features"
                        class="hidden text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded sm:inline-block"
                    >
                        Features
                    </Link>
                    <Link
                        href="/pricing"
                        class="hidden text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded sm:inline-block"
                    >
                        Pricing
                    </Link>
                    <Link
                        href="/about"
                        class="hidden text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded sm:inline-block"
                    >
                        About
                    </Link>
                </nav>
                <div class="flex shrink-0 items-center gap-6">
                    <Link
                        v-if="!isMainDomain"
                        :href="login.url()"
                        class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded"
                    >
                        Sign In
                    </Link>
                    <Link :href="registerInstitution.url()">
                        <Button size="sm" class="gap-2">
                            Get Started
                            <ArrowRight class="h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>
