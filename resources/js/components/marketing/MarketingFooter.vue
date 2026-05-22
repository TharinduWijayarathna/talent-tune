<script setup lang="ts">
import { registerInstitution } from '@/routes';
import { Link } from '@inertiajs/vue3';

const registerUrl = registerInstitution.url();
</script>

<template>
    <footer>
        <div class="footer-copy">
            © {{ new Date().getFullYear() }} Viva Suite. All rights reserved.
        </div>
        <ul class="footer-links">
            <li><Link href="/">Home</Link></li>
            <li><Link href="/features">Features</Link></li>
            <li><Link href="/pricing">Pricing</Link></li>
            <li><Link href="/about">About</Link></li>
            <li><Link :href="registerUrl">Register</Link></li>
        </ul>
    </footer>
</template>
