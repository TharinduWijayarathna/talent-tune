<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Layers, Plus, Trash2, GraduationCap } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';

interface BatchItem {
    id: number;
    name: string;
    students_count: number;
    created_at: string;
}

defineProps<{
    batches: BatchItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Batches', href: '/institution/batches' },
];

const form = useForm({
    name: '',
});

const submitBatch = () => {
    form.post('/institution/batches', {
        preserveScroll: true,
        onSuccess: () => form.reset('name'),
    });
};

const handleDelete = (id: number, name: string) => {
    if (confirm(`Remove batch "${name}"? Students in this batch will keep their batch value; only the batch definition will be removed.`)) {
        router.delete(`/institution/batches/${id}`);
    }
};

const formatDate = (iso: string) => iso ? new Date(iso).toLocaleDateString() : '';
</script>

<template>
    <Head title="Manage Batches" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Manage Batches</h1>
                    <p class="text-muted-foreground">Create batches for grouping students; vivas are scheduled per batch</p>
                </div>
            </div>

            <!-- Add batch form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Plus class="h-5 w-5" />
                        Add Batch
                    </CardTitle>
                    <CardDescription>Create a new batch (e.g. CS-2024, SE-2024). Students and viva sessions are assigned to batches.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitBatch" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px] space-y-2">
                            <Label for="batch-name">Batch name</Label>
                            <Input
                                id="batch-name"
                                v-model="form.name"
                                placeholder="e.g. CS-2024"
                                class="max-w-xs"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <Button type="submit" :disabled="form.processing">
                            Add Batch
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Batches list -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Layers class="h-5 w-5" />
                        All Batches ({{ batches.length }})
                    </CardTitle>
                    <CardDescription>Batches used for organizing students and scheduling viva sessions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="batch in batches"
                            :key="batch.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                    <Layers class="h-6 w-6 text-primary" />
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ batch.name }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <GraduationCap class="h-4 w-4" />
                                        <span>{{ batch.students_count }} student{{ batch.students_count === 1 ? '' : 's' }}</span>
                                        <span>•</span>
                                        <span>Created {{ formatDate(batch.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="handleDelete(batch.id, batch.name)"
                                class="text-destructive hover:text-destructive"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>

                        <div v-if="batches.length === 0" class="text-center py-12">
                            <Layers class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <p class="text-muted-foreground">No batches yet</p>
                            <p class="text-sm text-muted-foreground mt-2">
                                Add a batch above to group students (e.g. by year or course). Lecturers will select a batch when creating viva sessions.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
