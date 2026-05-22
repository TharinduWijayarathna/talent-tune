<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 18 18"
        fill="none"
        :class="className"
        v-bind="$attrs"
    >
        <path
            d="M9 2L9 16M2 9H16M4.5 4.5L13.5 13.5M13.5 4.5L4.5 13.5"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
        />
    </svg>
</template>
