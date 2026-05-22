<script setup lang="ts">
import MarketingNav from '@/components/marketing/MarketingNav.vue';
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

        <MarketingNav variant="pages" />

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
