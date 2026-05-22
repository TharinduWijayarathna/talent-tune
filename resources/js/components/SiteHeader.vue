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
import {
    ArrowRight,
    Building2,
    CreditCard,
    Home,
    Menu,
    Sparkles,
} from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    isMainDomain?: boolean;
}>();

const mobileMenuOpen = ref(false);

const navLinkClass =
    'text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded';

const mobileNavItems = [
    { href: '/', label: 'Home', icon: Home },
    { href: '/features', label: 'Features', icon: Sparkles },
    { href: '/pricing', label: 'Pricing', icon: CreditCard },
    { href: '/about', label: 'About', icon: Building2 },
];

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-border/40 bg-background/95 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative flex h-14 items-center justify-between sm:h-16">
                <Link
                    href="/"
                    class="flex shrink-0 items-center gap-2 rounded-md focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                    aria-label="Viva Suite home"
                >
                    <img
                        src="/images/logo.png"
                        alt="Viva Suite"
                        class="h-9 w-auto object-contain"
                    />
                </Link>

                <!-- Desktop nav: center + right -->
                <nav
                    class="absolute left-1/2 hidden -translate-x-1/2 items-center gap-6 lg:flex"
                    aria-label="Main"
                >
                    <Link href="/" :class="navLinkClass">Home</Link>
                    <Link href="/features" :class="navLinkClass">Features</Link>
                    <Link href="/pricing" :class="navLinkClass">Pricing</Link>
                    <Link href="/about" :class="navLinkClass">About</Link>
                </nav>
                <div class="hidden shrink-0 items-center gap-4 lg:flex lg:gap-6">
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
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-border/60 bg-background text-foreground hover:bg-muted lg:hidden"
                        aria-label="Open menu"
                    >
                        <Menu class="h-5 w-5" />
                    </SheetTrigger>
                    <SheetContent
                        side="right"
                        class="z-[200] flex w-[min(100vw-2rem,340px)] flex-col border-l border-border bg-background px-0"
                    >
                        <SheetHeader
                            class="border-b border-border/40 px-6 pt-6 pr-14 pb-4"
                        >
                            <div class="flex items-center gap-2">
                                <img
                                    src="/images/logo.png"
                                    alt="Viva Suite"
                                    class="h-8 w-auto"
                                />
                                <SheetTitle class="text-base font-semibold">
                                    Menu
                                </SheetTitle>
                            </div>
                        </SheetHeader>
                        <nav
                            class="flex flex-1 flex-col gap-0.5 px-4 py-6"
                            aria-label="Mobile menu"
                        >
                            <Link
                                v-for="item in mobileNavItems"
                                :key="item.href"
                                :href="item.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-3.5 text-base font-medium text-foreground/90 transition-colors hover:bg-muted/60 hover:text-foreground active:bg-muted/80"
                                @click="closeMobileMenu"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-5 w-5 shrink-0 text-primary/80"
                                />
                                {{ item.label }}
                            </Link>
                            <Link
                                v-if="!isMainDomain"
                                :href="login.url()"
                                class="mt-2 flex items-center gap-3 rounded-lg px-3 py-3.5 text-base font-medium text-muted-foreground transition-colors hover:bg-muted/60 hover:text-foreground"
                                @click="closeMobileMenu"
                            >
                                Sign In
                            </Link>
                        </nav>
                        <div class="border-t border-border/40 p-4">
                            <Link
                                :href="registerInstitution.url()"
                                class="block"
                                @click="closeMobileMenu"
                            >
                                <Button
                                    size="lg"
                                    class="w-full gap-2 shadow-md transition-all hover:shadow-lg"
                                >
                                    Get Started
                                    <ArrowRight class="h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    </header>
</template>
