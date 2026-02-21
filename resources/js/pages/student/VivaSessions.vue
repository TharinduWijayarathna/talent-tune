<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, FileText, User } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    vivaSessions?: Array<{
        id: number;
        title: string;
        description?: string;
        date: string;
        time: string;
        scheduled_at?: string;
        lecturer: string;
        status: string;
        batch?: string;
        materials?: string[];
        marks?: number;
        grade?: string | null;
        can_attend?: boolean;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Viva Sessions', href: '/student/vivas' },
];

const vivaSessions = computed(() => props.vivaSessions || []);

const getStatusBadge = (viva: { status: string; can_attend?: boolean }) => {
    if (viva.status === 'completed') return 'secondary';
    if (viva.can_attend) return 'default';
    return 'outline';
};

// Format scheduled_at (ISO) in user's local time for display
const formatScheduledLocal = (viva: {
    date?: string;
    time?: string;
    scheduled_at?: string;
}) => {
    if (viva.scheduled_at) {
        try {
            const d = new Date(viva.scheduled_at);
            if (!Number.isNaN(d.getTime())) {
                return {
                    date: d.toLocaleDateString(undefined, {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                    }),
                    time: d.toLocaleTimeString(undefined, {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true,
                    }),
                };
            }
        } catch {
            // fallback
        }
    }
    return { date: viva.date ?? '', time: viva.time ?? '' };
};

const attendLabel = (viva: {
    status: string;
    can_attend?: boolean;
    date?: string;
    time?: string;
    scheduled_at?: string;
}) => {
    if (viva.status === 'completed') return 'Closed by lecturer';
    if (viva.can_attend) return 'Attend Viva';
    const { date, time } = formatScheduledLocal(viva);
    return `Opens on ${date} at ${time}`;
};
</script>

<template>
    <Head title="Viva Sessions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Viva Sessions</h1>
                    <p class="text-muted-foreground">
                        View and attend your viva sessions
                    </p>
                </div>
            </div>

            <div
                v-if="vivaSessions.length === 0"
                class="py-12 text-center text-muted-foreground"
            >
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
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <CardTitle>{{ viva.title }}</CardTitle>
                                    <Badge :variant="getStatusBadge(viva)">
                                        {{
                                            viva.status === 'completed'
                                                ? 'Closed'
                                                : viva.can_attend
                                                  ? 'Open'
                                                  : 'Upcoming'
                                        }}
                                    </Badge>
                                </div>
                                <CardDescription>{{
                                    viva.description
                                }}</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Calendar class="h-4 w-4" />
                                    <span>{{
                                        formatScheduledLocal(viva).date
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Clock class="h-4 w-4" />
                                    <span>{{
                                        formatScheduledLocal(viva).time
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <User class="h-4 w-4" />
                                    <span>{{ viva.lecturer }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <span>Batch: {{ viva.batch }}</span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    viva.materials && viva.materials.length > 0
                                "
                                class="space-y-2"
                            >
                                <div
                                    class="flex items-center gap-2 text-sm font-medium"
                                >
                                    <FileText class="h-4 w-4" />
                                    <span>Materials:</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="(
                                            material, idx
                                        ) in viva.materials"
                                        :key="idx"
                                        variant="outline"
                                        class="cursor-pointer hover:bg-accent"
                                    >
                                        <FileText class="mr-1 h-3 w-3" />
                                        {{ material }}
                                    </Badge>
                                </div>
                            </div>

                            <div
                                v-if="viva.marks != null || viva.grade"
                                class="rounded-lg bg-muted p-3"
                            >
                                <div class="text-sm font-medium">
                                    Result
                                </div>
                                <div class="text-2xl font-bold">
                                    <template v-if="viva.grade">
                                        Grade: {{ viva.grade }}
                                        <span
                                            v-if="viva.marks != null"
                                            class="text-lg font-normal text-muted-foreground"
                                        >
                                            ({{ viva.marks }}%)
                                        </span>
                                    </template>
                                    <template v-else-if="viva.marks != null">
                                        {{ viva.marks }}%
                                    </template>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Button v-if="viva.can_attend" as-child>
                                    <Link
                                        :href="`/student/vivas/${viva.id}/attend`"
                                    >
                                        Attend Viva
                                    </Link>
                                </Button>
                                <Button v-else variant="outline" disabled>
                                    {{ attendLabel(viva) }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
