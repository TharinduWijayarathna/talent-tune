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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronDown,
    ChevronRight,
    FileText,
    Headphones,
    Lock,
    MessageSquare,
    User,
    UserPlus,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface AnswerItem {
    question: string;
    answer: string;
    voice_path?: string | null;
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
    grade: string | null;
    feedback: string | null;
    answers: AnswerItem[];
    document_path: string | null;
    completed_at: string | null;
    allowed_after_close?: boolean;
}

interface StudentOption {
    id: number;
    name: string;
    email: string | null;
    has_attended?: boolean;
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

// Format ISO scheduled_at (UTC) in user's local time for display
const formatScheduledLocal = (isoString: string) => {
    if (!isoString) return '';
    const d = new Date(isoString);
    if (Number.isNaN(d.getTime())) return isoString;
    return d.toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

// One-time participation after viva closed
const addLateOpen = ref(false);
const lateStudents = ref<StudentOption[]>([]);
const selectedLateStudentId = ref<number | null>(null);
const loadingLateStudents = ref(false);
const addingLate = ref(false);
const lateSearch = ref('');
let lateSearchTimeout: ReturnType<typeof setTimeout> | null = null;

const fetchStudentsForLateParticipation = async (search?: string) => {
    loadingLateStudents.value = true;
    selectedLateStudentId.value = null;
    try {
        const url = new URL(
            `/lecturer/vivas/${props.viva.id}/students-for-late-participation`,
            window.location.origin,
        );
        if (search && search.trim())
            url.searchParams.set('search', search.trim());
        const r = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await r.json();
        lateStudents.value = data.students ?? [];
    } finally {
        loadingLateStudents.value = false;
    }
};

const onLateSearchInput = () => {
    if (lateSearchTimeout) clearTimeout(lateSearchTimeout);
    lateSearchTimeout = setTimeout(() => {
        fetchStudentsForLateParticipation(lateSearch.value);
    }, 250);
};

const openAddLateDialog = () => {
    addLateOpen.value = true;
    lateSearch.value = '';
    fetchStudentsForLateParticipation();
};

const addLateStudent = () => {
    if (selectedLateStudentId.value == null) return;
    addingLate.value = true;
    router.post(
        `/lecturer/vivas/${props.viva.id}/add-late-student`,
        { student_id: selectedLateStudentId.value },
        {
            preserveScroll: true,
            onFinish: () => {
                addingLate.value = false;
                addLateOpen.value = false;
                selectedLateStudentId.value = null;
            },
        },
    );
};
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
                                {{ formatScheduledLocal(viva.scheduled_at) }}
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

            <!-- Add student for one-time participation (when viva is closed) -->
            <Card v-if="viva.status === 'completed'">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <UserPlus class="h-5 w-5" />
                        One-time participation
                    </CardTitle>
                    <CardDescription>
                        Add a student from this batch to participate once after
                        the viva has closed. They will appear in the attendees
                        list and can complete the viva one time.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Dialog
                        :open="addLateOpen"
                        @update:open="addLateOpen = $event"
                    >
                        <DialogTrigger as-child>
                            <Button
                                variant="outline"
                                @click="openAddLateDialog"
                            >
                                <UserPlus class="mr-2 h-4 w-4" />
                                Add student for one-time participation
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Add student</DialogTitle>
                                <DialogDescription>
                                    Only students from this viva's batch can
                                    participate. Search and choose a student.
                                    They can attend once; you can add the same
                                    student again for a re-do.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium"
                                        >Search students (batch:
                                        {{ viva.batch }})</label
                                    >
                                    <Input
                                        v-model="lateSearch"
                                        type="search"
                                        placeholder="Search by name or email…"
                                        class="w-full"
                                        @input="onLateSearchInput"
                                    />
                                </div>
                                <p
                                    v-if="loadingLateStudents"
                                    class="text-sm text-muted-foreground"
                                >
                                    Loading students…
                                </p>
                                <p
                                    v-else-if="lateStudents.length === 0"
                                    class="text-sm text-muted-foreground"
                                >
                                    No students found. Try a different search or
                                    ensure the batch has students.
                                </p>
                                <div
                                    v-else
                                    class="max-h-56 space-y-1 overflow-y-auto rounded-md border border-input p-1"
                                >
                                    <button
                                        v-for="s in lateStudents"
                                        :key="s.id"
                                        type="button"
                                        class="flex w-full flex-col items-start gap-0.5 rounded-sm px-2 py-2 text-left text-sm transition-colors hover:bg-muted focus-visible:bg-muted focus-visible:outline-none"
                                        :class="{
                                            'bg-primary/10 ring-1 ring-primary':
                                                selectedLateStudentId === s.id,
                                        }"
                                        @click="selectedLateStudentId = s.id"
                                    >
                                        <span class="font-medium">{{
                                            s.name
                                        }}</span>
                                        <span
                                            v-if="s.email"
                                            class="text-muted-foreground"
                                            >{{ s.email }}</span
                                        >
                                        <Badge
                                            v-if="s.has_attended"
                                            variant="secondary"
                                            class="mt-1 text-xs"
                                        >
                                            Add again for re-do
                                        </Badge>
                                    </button>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    variant="outline"
                                    @click="addLateOpen = false"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    :disabled="
                                        selectedLateStudentId == null ||
                                        addingLate
                                    "
                                    @click="addLateStudent"
                                >
                                    {{ addingLate ? 'Adding…' : 'Add student' }}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
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
                                        <Badge
                                            v-if="sub.allowed_after_close"
                                            variant="outline"
                                            class="border-amber-500/50 text-amber-700 dark:text-amber-400"
                                        >
                                            One-time
                                        </Badge>
                                        <span
                                            v-if="
                                                sub.status === 'completed' &&
                                                (sub.total_score != null ||
                                                    sub.grade)
                                            "
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            <template v-if="sub.grade">
                                                Grade: {{ sub.grade }}
                                                <template
                                                    v-if="
                                                        sub.total_score != null
                                                    "
                                                >
                                                    ({{ sub.total_score }})
                                                </template>
                                            </template>
                                            <template
                                                v-else-if="
                                                    sub.total_score != null
                                                "
                                            >
                                                Score: {{ sub.total_score }}
                                            </template>
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
                                            v-if="
                                                sub.status === 'completed' &&
                                                (sub.grade ||
                                                    sub.total_score != null)
                                            "
                                            class="mb-4 flex flex-wrap items-center gap-4 rounded-md border bg-background p-3 text-sm"
                                        >
                                            <span
                                                v-if="sub.grade"
                                                class="font-semibold"
                                            >
                                                Grade: {{ sub.grade }}
                                            </span>
                                            <span
                                                v-if="sub.total_score != null"
                                                class="text-muted-foreground"
                                            >
                                                Total score:
                                                {{ sub.total_score }}
                                            </span>
                                        </div>
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
                                                                item.voice_path
                                                            "
                                                            class="mt-2 flex items-center gap-2 rounded-md border bg-muted/50 p-2"
                                                        >
                                                            <Headphones
                                                                class="h-4 w-4 shrink-0 text-muted-foreground"
                                                            />
                                                            <span
                                                                class="text-xs font-medium text-muted-foreground"
                                                            >
                                                                Hear student's
                                                                voice:
                                                            </span>
                                                            <audio
                                                                :src="`/lecturer/viva-submissions/${sub.id}/voice/${idx}`"
                                                                controls
                                                                class="h-8 max-w-full flex-1"
                                                            />
                                                        </div>
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
                                            class="mt-4 flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
                                        >
                                            <FileText class="h-4 w-4" />
                                            <a
                                                :href="`/lecturer/viva-submissions/${sub.id}/document`"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-primary underline hover:no-underline"
                                            >
                                                View document
                                            </a>
                                            <span class="text-muted-foreground"
                                                >·</span
                                            >
                                            <a
                                                :href="`/lecturer/viva-submissions/${sub.id}/document?download=1`"
                                                download
                                                class="text-primary underline hover:no-underline"
                                            >
                                                Download
                                            </a>
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
