<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, User, FileText, Link as LinkIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    vivaSessions?: Array<{
        id: number;
        title: string;
        description?: string;
        date: string;
        time: string;
        lecturer: string;
        status: string;
        batch?: string;
        materials?: string[];
        marks?: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Viva Sessions', href: '/student/vivas' },
];

const vivaSessions = computed(() => props.vivaSessions || []);

const getStatusBadge = (status: string) => {
    return status === 'upcoming' ? 'default' : 'secondary';
};
</script>

<template>
    <Head title="Viva Sessions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Viva Sessions</h1>
                    <p class="text-muted-foreground">View and attend your viva sessions</p>
                </div>
            </div>

            <div v-if="vivaSessions.length === 0" class="text-center py-12 text-muted-foreground">
                No viva sessions available
            </div>
            <div v-else class="grid gap-4">
                <Card
                    v-for="viva in vivaSessions"
                    :key="viva.id"
                    class="transition-all hover:shadow-md"
                >
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="space-y-1 flex-1">
                                <div class="flex items-center gap-2">
                                    <CardTitle>{{ viva.title }}</CardTitle>
                                    <Badge :variant="getStatusBadge(viva.status)">
                                        {{ viva.status }}
                                    </Badge>
                                </div>
                                <CardDescription>{{ viva.description }}</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Calendar class="h-4 w-4" />
                                    <span>{{ viva.date }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Clock class="h-4 w-4" />
                                    <span>{{ viva.time }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <User class="h-4 w-4" />
                                    <span>{{ viva.lecturer }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <span>Batch: {{ viva.batch }}</span>
                                </div>
                            </div>

                            <div v-if="viva.materials && viva.materials.length > 0" class="space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium">
                                    <FileText class="h-4 w-4" />
                                    <span>Materials:</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="(material, idx) in viva.materials"
                                        :key="idx"
                                        variant="outline"
                                        class="cursor-pointer hover:bg-accent"
                                    >
                                        <FileText class="h-3 w-3 mr-1" />
                                        {{ material }}
                                    </Badge>
                                </div>
                            </div>

                            <div v-if="viva.marks" class="rounded-lg bg-muted p-3">
                                <div class="text-sm font-medium">Marks Obtained</div>
                                <div class="text-2xl font-bold">{{ viva.marks }}%</div>
                            </div>

                            <div class="flex gap-2">
                                <Button
                                    v-if="viva.status === 'upcoming'"
                                    as-child
                                >
                                    <Link :href="`/student/vivas/${viva.id}/attend`">
                                        Attend Viva
                                    </Link>
                                </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    disabled
                                >
                                    Completed
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

