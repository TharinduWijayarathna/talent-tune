<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        /** Show "Viva Suite" wordmark beside the mark */
        showText?: boolean;
        /** Mark + text scale */
        size?: 'sm' | 'md' | 'lg';
        /** Wrap in a home link */
        href?: string | null;
    }>(),
    {
        showText: true,
        size: 'md',
        href: null,
    },
);

const sizeClasses = {
    sm: {
        mark: 'h-7 w-7 rounded-md [&_svg]:h-3.5 [&_svg]:w-3.5',
        text: 'text-sm',
    },
    md: {
        mark: 'h-9 w-9 rounded-lg [&_svg]:h-[18px] [&_svg]:w-[18px]',
        text: 'text-lg',
    },
    lg: {
        mark: 'h-11 w-11 rounded-lg [&_svg]:h-5 [&_svg]:w-5',
        text: 'text-xl',
    },
} as const;
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        :href="href ?? undefined"
        class="viva-logo"
        :class="href ? 'viva-logo--link' : undefined"
    >
        <div
            class="viva-logo-mark"
            :class="sizeClasses[size].mark"
            aria-hidden="true"
        >
            <svg viewBox="0 0 18 18" fill="none">
                <path
                    d="M9 2L9 16M2 9H16M4.5 4.5L13.5 13.5M13.5 4.5L4.5 13.5"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                />
            </svg>
        </div>
        <span
            v-if="showText"
            class="viva-logo-text group-data-[collapsible=icon]:hidden"
            :class="sizeClasses[size].text"
        >
            Viva<span>Suite</span>
        </span>
    </component>
</template>
