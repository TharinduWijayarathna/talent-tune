<script setup lang="ts">
import { useInstitution } from '@/composables/useInstitution';

const { institutionLogo, institutionName } = useInstitution();

const appLogoUrl = '/images/logo.png';

withDefaults(
    defineProps<{
        /** When true, show only the logo at full width (no text). Used in sidebar. */
        sidebar?: boolean;
    }>(),
    { sidebar: false },
);
</script>

<template>
    <img
        v-if="!institutionLogo"
        :src="appLogoUrl"
        alt="TalentTune"
        :class="
            sidebar
                ? 'h-auto max-h-10 w-full max-w-full object-contain'
                : 'h-8 w-8 object-contain'
        "
    />
    <img
        v-else
        :src="institutionLogo"
        :alt="institutionName"
        :class="
            sidebar
                ? 'h-auto max-h-10 w-full max-w-full object-contain rounded'
                : 'h-8 w-8 rounded object-contain'
        "
    />
    <div
        v-if="!sidebar"
        class="ml-1 grid flex-1 text-left text-sm"
    >
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            institutionName
        }}</span>
    </div>
</template>
