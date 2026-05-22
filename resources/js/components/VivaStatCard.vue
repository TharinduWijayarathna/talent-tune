<script setup lang="ts">
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Component } from 'vue';

defineProps<{
    title: string;
    icon?: Component;
}>();
</script>

<template>
    <Card class="viva-stat-card">
        <CardHeader class="viva-stat-card__header">
            <CardTitle class="viva-stat-card__title">{{ title }}</CardTitle>
            <component
                :is="icon"
                v-if="icon"
                class="viva-stat-card__icon"
                aria-hidden="true"
            />
        </CardHeader>
        <CardContent class="viva-stat-card__content">
            <slot />
        </CardContent>
    </Card>
</template>
