<script setup lang="ts">
import VivaSuiteLogo from '@/components/VivaSuiteLogo.vue';
import { useInstitution } from '@/composables/useInstitution';

const { institutionLogo, institutionName } = useInstitution();

withDefaults(
    defineProps<{
        /** Sidebar: wordmark when expanded (icon when collapsed). */
        sidebar?: boolean;
    }>(),
    { sidebar: false },
);
</script>

<template>
    <img
        v-if="institutionLogo"
        :src="institutionLogo"
        :alt="institutionName ?? 'Institution'"
        :class="
            sidebar
                ? 'h-auto max-h-10 w-full max-w-full rounded object-contain'
                : 'h-8 w-8 rounded object-contain'
        "
    />
    <VivaSuiteLogo
        v-else
        :show-text="!institutionName"
        :size="sidebar ? 'md' : 'sm'"
        :class="sidebar ? 'min-w-0 shrink' : undefined"
    />
    <span
        v-if="sidebar && institutionName"
        class="viva-logo-text truncate text-sm font-extrabold tracking-tight group-data-[collapsible=icon]:hidden"
    >
        {{ institutionName }}
    </span>
    <div
        v-if="!sidebar && institutionName"
        class="ml-1 grid flex-1 text-left text-sm"
    >
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            institutionName
        }}</span>
    </div>
</template>
