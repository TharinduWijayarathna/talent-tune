<script setup lang="ts">
import MarketingPricing from '@/components/marketing/MarketingPricing.vue';
import { Head, Link } from '@inertiajs/vue3';
import { registerInstitution } from '@/routes';

import '../../../css/marketing-home.css';

defineProps<{
    institution?: { id: number } | null;
}>();

const registerUrl = registerInstitution.url();
</script>

<template>
    <Head title="Pricing - Viva Suite">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Syne:wght@400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap"
        />
    </Head>

    <div class="marketing-page">
        <div class="grid-bg" />

        <nav>
            <Link href="/" class="nav-logo">
                <div class="logo-mark">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path
                            d="M9 2L9 16M2 9H16M4.5 4.5L13.5 13.5M13.5 4.5L4.5 13.5"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
                <span class="logo-text">Viva<span>Suite</span></span>
            </Link>
            <ul class="nav-links">
                <li><Link href="/">Home</Link></li>
                <li><Link href="/#features">Features</Link></li>
                <li><Link href="/pricing">Pricing</Link></li>
                <li><Link href="/about">About</Link></li>
            </ul>
            <Link :href="registerUrl" class="nav-cta">Register Institution</Link>
        </nav>

        <MarketingPricing />

        <footer>
            <div class="footer-copy">
                © {{ new Date().getFullYear() }} Viva Suite. All rights reserved.
            </div>
            <ul class="footer-links">
                <li><Link href="/">Home</Link></li>
                <li><Link :href="registerUrl">Register</Link></li>
            </ul>
        </footer>
    </div>
</template>
