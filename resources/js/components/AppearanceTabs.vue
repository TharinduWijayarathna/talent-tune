<script setup lang="ts">
import { useAppearance, type Appearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

const { appearance, updateAppearance } = useAppearance();

const tabs: { value: Appearance; label: string; icon: typeof Sun }[] = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
];

const active = computed(() => appearance.value);

function select(value: Appearance) {
    updateAppearance(value);
}
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg border border-border bg-muted/50 p-1"
    >
        <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors"
            :class="
                active === tab.value
                    ? 'bg-background text-foreground shadow-sm'
                    : 'text-muted-foreground hover:text-foreground'
            "
            @click="select(tab.value)"
        >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label }}
        </button>
    </div>
</template>
