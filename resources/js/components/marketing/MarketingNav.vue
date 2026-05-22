<script setup lang="ts">
import VivaSuiteLogo from '@/components/VivaSuiteLogo.vue';
import { registerInstitution } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject, onUnmounted, ref, watch } from 'vue';

const registerUrl = registerInstitution.url();
const menuOpen = ref(false);
const page = usePage();

const isHome = computed(() => {
    const path = page.url.split('?')[0];
    return path === '/' || path === '';
});

const navItems = computed(() => {
    const prefix = isHome.value ? '' : '/';
    return [
        { label: 'Home', href: isHome.value ? '#' : '/' },
        { label: 'How It Works', href: `${prefix}#how` },
        { label: 'Features', href: `${prefix}#features` },
        { label: 'Workspace', href: `${prefix}#workspace` },
        { label: 'Roles', href: `${prefix}#roles` },
        { label: 'Pricing', href: `${prefix}#pricing` },
    ];
});

function closeMenu() {
    menuOpen.value = false;
}

function toggleMenu() {
    menuOpen.value = !menuOpen.value;
}

const openHowItWorksDemo = inject<(() => void) | null>(
    'openHowItWorksDemo',
    null,
);

function onNavItemClick(
    item: { label: string; href: string },
    event: MouseEvent,
) {
    if (
        isHome.value &&
        item.href === '#how' &&
        openHowItWorksDemo
    ) {
        event.preventDefault();
        openHowItWorksDemo();
    }
    closeMenu();
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
            <VivaSuiteLogo />
        </Link>

        <ul class="nav-links nav-desktop">
            <li v-for="item in navItems" :key="item.href">
                <Link
                    v-if="!isHome && item.href.startsWith('/')"
                    :href="item.href"
                >
                    {{ item.label }}
                </Link>
                <a
                    v-else
                    :href="item.href"
                    @click="onNavItemClick(item, $event)"
                    >{{ item.label }}</a
                >
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
                <VivaSuiteLogo :show-text="true" size="md" />
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
                        v-if="!isHome && item.href.startsWith('/')"
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
                        @click="onNavItemClick(item, $event)"
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
