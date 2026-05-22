<script setup lang="ts">
import { registerInstitution } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        /** Landing page uses in-page anchors; other marketing pages use routes */
        variant?: 'landing' | 'pages';
    }>(),
    {
        variant: 'landing',
    },
);

const registerUrl = registerInstitution.url();
const menuOpen = ref(false);

const navItems = computed(() =>
    props.variant === 'landing'
        ? [
              { label: 'Home', href: '#' },
              { label: 'How It Works', href: '#how' },
              { label: 'Features', href: '#features' },
              { label: 'Workspace', href: '#workspace' },
              { label: 'Roles', href: '#roles' },
              { label: 'Pricing', href: '#pricing' },
          ]
        : [
              { label: 'Home', href: '/' },
              { label: 'Features', href: '/#features' },
              { label: 'Pricing', href: '/pricing' },
              { label: 'About', href: '/about' },
          ],
);

function closeMenu() {
    menuOpen.value = false;
}

function toggleMenu() {
    menuOpen.value = !menuOpen.value;
}

watch(menuOpen, (open) => {
    if (typeof document === 'undefined') {
        return;
    }
    document.body.style.overflow = open ? 'hidden' : '';
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <nav class="marketing-nav" aria-label="Main">
        <Link href="/" class="nav-logo" @click="closeMenu">
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

        <ul class="nav-links nav-desktop">
            <li v-for="item in navItems" :key="item.href">
                <Link v-if="variant === 'pages'" :href="item.href">
                    {{ item.label }}
                </Link>
                <a v-else :href="item.href">{{ item.label }}</a>
            </li>
        </ul>

        <Link :href="registerUrl" class="nav-cta nav-desktop">
            Register Institution
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path
                    d="M2 7H12M8 3L12 7L8 11"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </Link>

        <button
            type="button"
            class="nav-toggle"
            :aria-expanded="menuOpen"
            aria-controls="marketing-mobile-menu"
            :aria-label="menuOpen ? 'Close menu' : 'Open menu'"
            @click="toggleMenu"
        >
            <span class="nav-toggle-bar" :class="{ open: menuOpen }" />
            <span class="nav-toggle-bar" :class="{ open: menuOpen }" />
            <span class="nav-toggle-bar" :class="{ open: menuOpen }" />
        </button>
    </nav>

    <Teleport to="body">
        <div
            v-if="menuOpen"
            class="nav-overlay"
            aria-hidden="true"
            @click="closeMenu"
        />
        <aside
            id="marketing-mobile-menu"
            class="nav-drawer"
            :class="{ open: menuOpen }"
            :aria-hidden="!menuOpen"
        >
            <div class="nav-drawer-header">
                <span class="logo-text">Viva<span>Suite</span></span>
                <button
                    type="button"
                    class="nav-drawer-close"
                    aria-label="Close menu"
                    @click="closeMenu"
                >
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M5 5L15 15M15 5L5 15"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>
            </div>

            <ul class="nav-drawer-links">
                <li v-for="item in navItems" :key="`mobile-${item.href}`">
                    <Link
                        v-if="variant === 'pages'"
                        :href="item.href"
                        class="nav-drawer-link"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </Link>
                    <a
                        v-else
                        :href="item.href"
                        class="nav-drawer-link"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </a>
                </li>
            </ul>

            <div class="nav-drawer-footer">
                <Link
                    :href="registerUrl"
                    class="nav-cta nav-drawer-cta"
                    @click="closeMenu"
                >
                    Register Institution
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path
                            d="M2 7H12M8 3L12 7L8 11"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </Link>
            </div>
        </aside>
    </Teleport>
</template>
