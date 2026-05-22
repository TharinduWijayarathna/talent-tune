<script setup lang="ts">
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import { Head } from '@inertiajs/vue3';

import '../../../css/marketing-home.css';

defineProps<{
    title: string;
}>();
</script>

<template>
    <Head :title="title">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Syne:wght@400;500;600;700;800&display=swap"
        />
    </Head>

    <div class="marketing-page">
        <div class="grid-bg" />
        <MarketingNav />
        <main class="marketing-main">
            <slot />
        </main>
        <MarketingFooter />
    </div>
</template>
