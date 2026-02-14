<script setup lang="ts">
import { Sun } from 'lucide-vue-next';

// Theme change: dark mode and system preference removed. Application uses light mode only.
</script>

<template>
    <div class="inline-flex items-center gap-2 rounded-lg bg-neutral-100 p-2">
        <Sun class="h-4 w-4 text-neutral-600" />
        <span class="text-sm text-neutral-600">Light mode only (theme switching disabled)</span>
    </div>
</template>
