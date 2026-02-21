<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Textarea } from '@/components/ui/textarea';
import { TimePicker } from '@/components/ui/time-picker';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { Calendar as CalendarIcon, Clock } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    batches?: string[];
    institutionId?: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'Create Viva Session', href: '/lecturer/vivas/create' },
];

const form = useForm({
    title: '',
    description: '',
    batch: '',
    date: '',
    time: '',
    instructions: '',
});

const availableBatches = computed(() => props.batches || []);

const submitForm = () => {
    form.post('/lecturer/vivas', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Viva Session" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Create Viva Session</h1>
                    <p class="text-muted-foreground">
                        Set up a new viva session for your batch
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Basic Information -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription
                                >Enter the basic details of the viva
                                session</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="title">Viva Title *</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="e.g., Database Systems Viva"
                                        required
                                    />
                                    <InputError :message="form.errors.title" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="batch">Batch *</Label>
                                    <select
                                        id="batch"
                                        v-model="form.batch"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                    >
                                        <option value="">Select a batch</option>
                                        <option
                                            v-for="batch in availableBatches"
                                            :key="batch"
                                            :value="batch"
                                        >
                                            {{ batch }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.batch" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Brief description of the viva session..."
                                    rows="3"
                                />
                                <InputError
                                    :message="form.errors.description"
                                />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Date *</Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                :class="
                                                    cn(
                                                        'w-full justify-start text-left font-normal',
                                                        !form.date &&
                                                            'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <CalendarIcon
                                                    class="mr-2 h-4 w-4"
                                                />
                                                {{
                                                    form.date
                                                        ? format(
                                                              new Date(
                                                                  form.date +
                                                                      'T12:00:00',
                                                              ),
                                                              'PPP',
                                                          )
                                                        : 'Pick a date'
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent
                                            class="w-auto p-0"
                                            align="start"
                                        >
                                            <Calendar
                                                :model-value="
                                                    form.date
                                                        ? new Date(
                                                              form.date +
                                                                  'T12:00:00',
                                                          )
                                                        : undefined
                                                "
                                                @update:model-value="
                                                    (d) => {
                                                        if (d)
                                                            form.date = format(
                                                                d,
                                                                'yyyy-MM-dd',
                                                            );
                                                    }
                                                "
                                            />
                                        </PopoverContent>
                                    </Popover>
                                    <InputError :message="form.errors.date" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Time *</Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                :class="
                                                    cn(
                                                        'w-full justify-start text-left font-normal',
                                                        !form.time &&
                                                            'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <Clock class="mr-2 h-4 w-4" />
                                                {{ form.time || 'Pick a time' }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent
                                            class="w-auto p-0"
                                            align="start"
                                        >
                                            <TimePicker v-model="form.time" />
                                        </PopoverContent>
                                    </Popover>
                                    <InputError :message="form.errors.time" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Instructions (used to generate viva questions) -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Instructions for the viva</CardTitle>
                            <CardDescription
                                >Type instructions that define the scope and
                                focus of this viva. Questions will be
                                generated for students based on these
                                instructions.</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <Label for="instructions">Instructions</Label>
                                <Textarea
                                    id="instructions"
                                    v-model="form.instructions"
                                    placeholder="e.g. Key topics to cover, concepts to assess, question areas (SQL, normalization, indexing...)"
                                    rows="8"
                                />
                                <InputError
                                    :message="form.errors.instructions"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <Button type="button" variant="outline" as-child>
                        <Link href="/lecturer/dashboard">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Create Viva Session
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
