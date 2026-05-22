<script setup lang="ts">
import VivaSuiteLogo from '@/components/VivaSuiteLogo.vue';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col border-r border-border bg-[#080b10] p-10 text-[#e8edf8] lg:flex"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-[#080b10] via-[#0e1420] to-[#141c2e]"
            />
            <Link
                :href="home()"
                class="viva-logo-on-dark relative z-20"
            >
                <VivaSuiteLogo size="md" />
            </Link>
            <div v-if="quote" class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <p class="text-lg">&ldquo;{{ quote.message }}&rdquo;</p>
                    <footer class="text-sm text-[#8a9bbf]">
                        {{ quote.author }}
                    </footer>
                </blockquote>
            </div>
        </div>
        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
