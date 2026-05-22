<script setup lang="ts">
import { registerInstitution } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const registerUrl = registerInstitution.url();
const page = usePage();

const isHome = computed(() => {
    const path = page.url.split('?')[0];
    return path === '/' || path === '';
});

const pricingHref = computed(() => (isHome.value ? '#pricing' : '/#pricing'));
</script>

<template>
    <footer>
        <div class="footer-copy">
            © {{ new Date().getFullYear() }} Viva Suite. All rights reserved.
        </div>
        <ul class="footer-links">
            <li>
                <Link v-if="!isHome" href="/#pricing">Pricing</Link>
                <a v-else :href="pricingHref">Pricing</a>
            </li>
            <li>
                <Link v-if="!isHome" href="/#features">Features</Link>
                <a v-else href="#features">Features</a>
            </li>
            <li><Link :href="registerUrl">Register</Link></li>
        </ul>
    </footer>
</template>
