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
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronDown,
    ChevronRight,
    FileText,
    Lock,
    MessageSquare,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface AnswerItem {
    question: string;
    answer: string;
    score_1_10?: number;
    feedback?: string;
    correctPoints?: string[];
    improvements?: string[];
}

interface Submission {
    id: number;
    student_name: string;
    student_email?: string | null;
    status: string;
    total_score: number | null;
    feedback: string | null;
    answers: AnswerItem[];
    document_path: string | null;
    completed_at: string | null;
}

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
    submissions?: Submission[];
}>();

const submissions = computed(() => props.submissions ?? []);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'My Sessions', href: '/lecturer/vivas' },
    { title: props.viva.title, href: '#' },
];

const openSubmissionId = ref<number | null>(null);

const closeViva = () => {
    if (!confirm('Close this viva? Students will no longer be able to attend.'))
        return;
    router.post(`/lecturer/vivas/${props.viva.id}/close`);
};

const completedCount = () =>
    submissions.value.filter((s) => s.status === 'completed').length;
const inProgressCount = () =>
    submissions.value.filter((s) => s.status === 'in_progress').length;
const pendingCount = () =>
    submissions.value.filter((s) => s.status === 'pending').length;
</script>

<template>
    <Head :title="`Viva: ${viva.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <Button variant="ghost" size="sm" as-child>
                    <Link href="/lecturer/vivas">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to sessions
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-2xl">{{
                                viva.title
                            }}</CardTitle>
                            <CardDescription class="mt-1">
                                Batch: {{ viva.batch }} •
                                {{ viva.scheduled_at }}
                            </CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="
                                    viva.status === 'upcoming'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ viva.status }}
                            </Badge>
                            <Button
                                v-if="viva.status !== 'completed'"
                                variant="destructive"
                                size="sm"
                                @click="closeViva"
                            >
                                <Lock class="mr-2 h-4 w-4" />
                                Close viva
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="viva.description">
                        <h4 class="mb-1 text-sm font-medium">Description</h4>
                        <p class="text-sm text-muted-foreground">
                            {{ viva.description }}
                        </p>
                    </div>
                    <div v-if="viva.instructions">
                        <h4 class="mb-1 text-sm font-medium">
                            Instructions for students
                        </h4>
                        <p
                            class="text-sm whitespace-pre-wrap text-muted-foreground"
                        >
                            {{ viva.instructions }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Attendees / Submissions -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Attendees ({{ submissions.length }})
                    </CardTitle>
                    <CardDescription>
                        Students who joined this viva. Expand a row to view
                        questions, answers, and scores.
                    </CardDescription>
                    <div
                        v-if="submissions.length > 0"
                        class="mt-2 flex flex-wrap gap-3 text-sm text-muted-foreground"
                    >
                        <span>
                            <Badge variant="secondary" class="mr-1">{{
                                completedCount()
                            }}</Badge>
                            completed
                        </span>
                        <span>
                            <Badge variant="outline" class="mr-1">{{
                                inProgressCount()
                            }}</Badge>
                            in progress
                        </span>
                        <span>
                            <Badge variant="outline" class="mr-1">{{
                                pendingCount()
                            }}</Badge>
                            pending
                        </span>
                    </div>
                </CardHeader>
                <CardContent>
                    <p
                        v-if="submissions.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No students have attended this viva yet.
                    </p>
                    <div v-else class="space-y-2">
                        <Collapsible
                            v-for="sub in submissions"
                            :key="sub.id"
                            :open="openSubmissionId === sub.id"
                            @update:open="
                                (open) =>
                                    (openSubmissionId = open ? sub.id : null)
                            "
                        >
                            <div
                                class="rounded-lg border bg-card transition-colors hover:bg-muted/50"
                            >
                                <CollapsibleTrigger
                                    class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left"
                                >
                                    <div
                                        class="flex min-w-0 flex-1 items-center gap-3"
                                    >
                                        <ChevronRight
                                            class="h-4 w-4 shrink-0 text-muted-foreground transition-transform"
                                            :class="
                                                openSubmissionId === sub.id
                                                    ? 'rotate-90'
                                                    : ''
                                            "
                                        />
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">
                                                {{ sub.student_name }}
                                            </p>
                                            <p
                                                v-if="sub.student_email"
                                                class="truncate text-xs text-muted-foreground"
                                            >
                                                {{ sub.student_email }}
                                            </p>
                                        </div>
                                        <Badge
                                            :variant="
                                                sub.status === 'completed'
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                        >
                                            {{ sub.status.replace('_', ' ') }}
                                        </Badge>
                                        <span
                                            v-if="
                                                sub.status === 'completed' &&
                                                sub.total_score != null
                                            "
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            Score: {{ sub.total_score }}
                                        </span>
                                        <span
                                            v-if="sub.completed_at"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ sub.completed_at }}
                                        </span>
                                    </div>
                                    <ChevronDown
                                        class="h-4 w-4 shrink-0 text-muted-foreground"
                                    />
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <div class="border-t bg-muted/30 px-4 py-4">
                                        <div
                                            v-if="sub.feedback"
                                            class="mb-4 rounded-md border bg-background p-3 text-sm"
                                        >
                                            <p
                                                class="font-medium text-muted-foreground"
                                            >
                                                Overall feedback
                                            </p>
                                            <p class="mt-1">
                                                {{ sub.feedback }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="
                                                !sub.answers ||
                                                sub.answers.length === 0
                                            "
                                            class="text-sm text-muted-foreground"
                                        >
                                            No answers recorded yet.
                                        </div>
                                        <div v-else class="space-y-4">
                                            <div
                                                v-for="(
                                                    item, idx
                                                ) in sub.answers"
                                                :key="idx"
                                                class="rounded-lg border bg-background p-4"
                                            >
                                                <div
                                                    class="flex items-start gap-2"
                                                >
                                                    <MessageSquare
                                                        class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                                    />
                                                    <div
                                                        class="min-w-0 flex-1 space-y-2"
                                                    >
                                                        <p
                                                            class="text-sm font-medium text-muted-foreground"
                                                        >
                                                            Q{{ idx + 1 }}
                                                        </p>
                                                        <p
                                                            class="text-sm font-medium"
                                                        >
                                                            {{ item.question }}
                                                        </p>
                                                        <p
                                                            class="text-sm whitespace-pre-wrap"
                                                        >
                                                            {{ item.answer }}
                                                        </p>
                                                        <div
                                                            v-if="
                                                                item.score_1_10 !=
                                                                    null &&
                                                                item.score_1_10 !==
                                                                    undefined
                                                            "
                                                            class="flex items-center gap-2 text-sm"
                                                        >
                                                            <Badge
                                                                variant="outline"
                                                            >
                                                                Score:
                                                                {{
                                                                    item.score_1_10
                                                                }}/10
                                                            </Badge>
                                                        </div>
                                                        <p
                                                            v-if="item.feedback"
                                                            class="text-sm text-muted-foreground"
                                                        >
                                                            {{ item.feedback }}
                                                        </p>
                                                        <div
                                                            v-if="
                                                                item
                                                                    .correctPoints
                                                                    ?.length
                                                            "
                                                            class="text-sm"
                                                        >
                                                            <span
                                                                class="font-medium text-muted-foreground"
                                                            >
                                                                Correct points:
                                                            </span>
                                                            <ul
                                                                class="mt-1 list-inside list-disc"
                                                            >
                                                                <li
                                                                    v-for="(
                                                                        pt, i
                                                                    ) in item.correctPoints"
                                                                    :key="i"
                                                                >
                                                                    {{ pt }}
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div
                                                            v-if="
                                                                item
                                                                    .improvements
                                                                    ?.length
                                                            "
                                                            class="text-sm"
                                                        >
                                                            <span
                                                                class="font-medium text-muted-foreground"
                                                            >
                                                                Improvements:
                                                            </span>
                                                            <ul
                                                                class="mt-1 list-inside list-disc"
                                                            >
                                                                <li
                                                                    v-for="(
                                                                        imp, i
                                                                    ) in item.improvements"
                                                                    :key="i"
                                                                >
                                                                    {{ imp }}
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-if="sub.document_path"
                                            class="mt-4 flex items-center gap-2 text-sm text-muted-foreground"
                                        >
                                            <FileText class="h-4 w-4" />
                                            Document uploaded
                                        </div>
                                    </div>
                                </CollapsibleContent>
                            </div>
                        </Collapsible>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
