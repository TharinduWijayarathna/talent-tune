<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useInstitution } from '@/composables/useInstitution';

const { institutionLogo, institutionName } = useInstitution();
</script>

<template>
    <div
        v-if="!institutionLogo"
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <img
        v-else
        :src="institutionLogo"
        :alt="institutionName"
        class="h-8 w-8 rounded"
    />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            institutionName
        }}</span>
    </div>
</template>
