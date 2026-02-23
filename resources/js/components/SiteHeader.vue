<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { login, registerInstitution } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Menu } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    isMainDomain?: boolean;
}>();

const mobileMenuOpen = ref(false);

const navLinkClass =
    'text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded';

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}
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

                <!-- Desktop nav: center + right -->
                <nav
                    class="absolute left-1/2 hidden -translate-x-1/2 items-center gap-6 md:flex"
                    aria-label="Main"
                >
                    <Link href="/" :class="navLinkClass">Home</Link>
                    <Link href="/features" :class="navLinkClass">Features</Link>
                    <Link href="/pricing" :class="navLinkClass">Pricing</Link>
                    <Link href="/about" :class="navLinkClass">About</Link>
                </nav>
                <div class="hidden shrink-0 items-center gap-6 md:flex">
                    <Link
                        v-if="!isMainDomain"
                        :href="login.url()"
                        :class="navLinkClass"
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

                <!-- Mobile: hamburger menu -->
                <Sheet v-model:open="mobileMenuOpen">
                    <SheetTrigger
                        class="inline-flex h-10 w-10 items-center justify-center rounded-md md:hidden hover:bg-muted"
                        aria-label="Open menu"
                    >
                        <Menu class="h-5 w-5" />
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[280px] sm:w-[320px]">
                        <SheetHeader>
                            <SheetTitle class="sr-only">Menu</SheetTitle>
                        </SheetHeader>
                        <nav
                            class="mt-6 flex flex-col gap-1"
                            aria-label="Mobile menu"
                        >
                            <Link
                                href="/"
                                :class="navLinkClass"
                                class="block py-3 text-base"
                                @click="closeMobileMenu"
                            >
                                Home
                            </Link>
                            <Link
                                href="/features"
                                :class="navLinkClass"
                                class="block py-3 text-base"
                                @click="closeMobileMenu"
                            >
                                Features
                            </Link>
                            <Link
                                href="/pricing"
                                :class="navLinkClass"
                                class="block py-3 text-base"
                                @click="closeMobileMenu"
                            >
                                Pricing
                            </Link>
                            <Link
                                href="/about"
                                :class="navLinkClass"
                                class="block py-3 text-base"
                                @click="closeMobileMenu"
                            >
                                About
                            </Link>
                            <Link
                                v-if="!isMainDomain"
                                :href="login.url()"
                                :class="navLinkClass"
                                class="block py-3 text-base"
                                @click="closeMobileMenu"
                            >
                                Sign In
                            </Link>
                            <Link
                                :href="registerInstitution.url()"
                                class="mt-4"
                                @click="closeMobileMenu"
                            >
                                <Button size="lg" class="w-full gap-2">
                                    Get Started
                                    <ArrowRight class="h-4 w-4" />
                                </Button>
                            </Link>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
