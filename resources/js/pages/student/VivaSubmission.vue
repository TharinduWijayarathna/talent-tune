<script setup lang="ts">
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
import { FileText, Headphones, MessageSquare, User } from 'lucide-vue-next';

interface AnswerItem {
    question: string;
    answer: string;
    voice_path?: string | null;
    feedback?: string | null;
}

const props = defineProps<{
    viva: {
        id: number;
        title: string;
        lecturer: string;
    };
    submission: {
        id: number;
        status: string;
        total_score: number | null;
        grade: string | null;
        feedback: string | null;
        answers: AnswerItem[];
        document_path: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Viva Sessions', href: '/student/vivas' },
    { title: props.viva.title, href: '#' },
];
</script>

<template>
    <Head :title="`My answers: ${viva.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center gap-2">
                <Button variant="ghost" size="sm" as-child>
                    <Link href="/student/vivas">
                        <span class="sr-only">Back</span>
                        ← Back to sessions
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ viva.title }}</CardTitle>
                    <CardDescription class="flex items-center gap-2">
                        <User class="h-4 w-4" />
                        {{ viva.lecturer }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Result -->
                    <div
                        v-if="
                            submission.grade != null ||
                            submission.total_score != null
                        "
                        class="rounded-lg bg-muted p-4"
                    >
                        <div class="text-sm font-medium text-muted-foreground">
                            Your result
                        </div>
                        <div class="mt-1 flex flex-wrap items-baseline gap-3">
                            <span
                                v-if="submission.grade"
                                class="text-2xl font-bold"
                            >
                                Grade: {{ submission.grade }}
                            </span>
                            <span
                                v-if="submission.total_score != null"
                                class="text-lg text-muted-foreground"
                            >
                                Score: {{ submission.total_score }}%
                            </span>
                        </div>
                    </div>

                    <!-- Overall feedback -->
                    <div
                        v-if="submission.feedback"
                        class="rounded-lg border bg-background p-4"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Overall feedback
                        </p>
                        <p class="mt-1 whitespace-pre-wrap">
                            {{ submission.feedback }}
                        </p>
                    </div>

                    <!-- Document uploaded -->
                    <div
                        v-if="submission.document_path"
                        class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
                    >
                        <FileText class="h-4 w-4" />
                        <a
                            :href="`/student/viva-submissions/${submission.id}/document`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-primary underline hover:no-underline"
                        >
                            View document
                        </a>
                        <span class="text-muted-foreground">·</span>
                        <a
                            :href="`/student/viva-submissions/${submission.id}/document?download=1`"
                            download
                            class="text-primary underline hover:no-underline"
                        >
                            Download
                        </a>
                    </div>

                    <!-- Your answers -->
                    <div>
                        <h3 class="mb-3 text-sm font-semibold">Your answers</h3>
                        <div
                            v-if="
                                !submission.answers ||
                                submission.answers.length === 0
                            "
                            class="text-sm text-muted-foreground"
                        >
                            No answers recorded.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="(item, idx) in submission.answers"
                                :key="idx"
                                class="rounded-lg border bg-background p-4"
                            >
                                <div class="flex items-start gap-2">
                                    <MessageSquare
                                        class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                    />
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <p
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            Question {{ idx + 1 }}
                                        </p>
                                        <p class="font-medium">
                                            {{ item.question }}
                                        </p>
                                        <p class="text-sm whitespace-pre-wrap">
                                            {{ item.answer }}
                                        </p>
                                        <div
                                            v-if="item.voice_path"
                                            class="mt-2 flex items-center gap-2 rounded-md border bg-muted/50 p-2"
                                        >
                                            <Headphones
                                                class="h-4 w-4 shrink-0 text-muted-foreground"
                                            />
                                            <span
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Your recording:
                                            </span>
                                            <audio
                                                :src="`/student/viva-submissions/${submission.id}/voice/${idx}`"
                                                controls
                                                class="h-8 max-w-full flex-1"
                                            />
                                        </div>
                                        <p
                                            v-if="item.feedback"
                                            class="mt-2 text-sm text-muted-foreground"
                                        >
                                            {{ item.feedback }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
