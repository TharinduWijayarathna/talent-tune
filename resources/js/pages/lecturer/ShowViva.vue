<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Lock } from 'lucide-vue-next';

const props = defineProps<{
    viva: {
        id: number;
        title: string;
        description?: string;
        batch: string;
        scheduled_at: string;
        instructions?: string;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'My Sessions', href: '/lecturer/vivas' },
    { title: props.viva.title, href: '#' },
];

const closeViva = () => {
    if (!confirm('Close this viva? Students will no longer be able to attend.')) return;
    router.post(`/lecturer/vivas/${props.viva.id}/close`);
};
</script>

<template>
    <Head :title="`Viva: ${viva.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <Button variant="ghost" size="sm" as-child>
                    <Link href="/lecturer/vivas">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to sessions
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-2xl">{{ viva.title }}</CardTitle>
                            <CardDescription class="mt-1">
                                Batch: {{ viva.batch }} • {{ viva.scheduled_at }}
                            </CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge :variant="viva.status === 'upcoming' ? 'default' : 'secondary'">
                                {{ viva.status }}
                            </Badge>
                            <Button
                                v-if="viva.status !== 'completed'"
                                variant="destructive"
                                size="sm"
                                @click="closeViva"
                            >
                                <Lock class="h-4 w-4 mr-2" />
                                Close viva
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="viva.description">
                        <h4 class="text-sm font-medium mb-1">Description</h4>
                        <p class="text-sm text-muted-foreground">{{ viva.description }}</p>
                    </div>
                    <div v-if="viva.instructions">
                        <h4 class="text-sm font-medium mb-1">Instructions for students</h4>
                        <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ viva.instructions }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
