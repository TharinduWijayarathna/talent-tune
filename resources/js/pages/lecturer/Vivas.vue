<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar, Users, FileText, Plus, ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    vivas: Array<{
        id: number;
        title: string;
        description?: string;
        batch: string;
        scheduled_at: string;
        status: string;
        students: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'My Sessions', href: '/lecturer/vivas' },
];

const vivas = computed(() => props.vivas || []);

const statusVariant = (status: string) => {
    switch (status) {
        case 'upcoming': return 'default';
        case 'active': return 'secondary';
        case 'completed': return 'outline';
        case 'cancelled': return 'destructive';
        default: return 'outline';
    }
};
</script>

<template>
    <Head title="My Viva Sessions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">My Viva Sessions</h1>
                    <p class="text-muted-foreground">View and manage all your created viva sessions</p>
                </div>
                <Button as-child>
                    <Link href="/lecturer/vivas/create">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Viva Session
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Sessions</CardTitle>
                    <CardDescription>Viva sessions you have created for your batches</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="vivas.length === 0" class="text-center py-12 text-muted-foreground">
                        <FileText class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p class="font-medium">No viva sessions yet</p>
                        <p class="text-sm mt-1">Create your first viva session to get started</p>
                        <Button as-child class="mt-4">
                            <Link href="/lecturer/vivas/create">
                                <Plus class="h-4 w-4 mr-2" />
                                Create Viva Session
                            </Link>
                        </Button>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="viva in vivas"
                            :key="viva.id"
                            class="flex items-center justify-between rounded-lg border p-4 hover:bg-muted/50 transition-colors"
                        >
                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold truncate">{{ viva.title }}</h3>
                                    <Badge :variant="statusVariant(viva.status)">
                                        {{ viva.status }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Batch: {{ viva.batch }} • {{ viva.scheduled_at }} • {{ viva.students }} student(s)
                                </p>
                                <p v-if="viva.description" class="text-sm text-muted-foreground line-clamp-2">
                                    {{ viva.description }}
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0 ml-4">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/lecturer/vivas/${viva.id}`">
                                        View
                                        <ArrowRight class="h-4 w-4 ml-1" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
