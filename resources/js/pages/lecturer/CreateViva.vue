<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { TimePicker } from '@/components/ui/time-picker';
import { Upload, X, FileText, Plus, Calendar as CalendarIcon, Clock } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Form } from '@inertiajs/vue3';
import { format } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'Create Viva Session', href: '/lecturer/vivas/create' },
];

const form = ref({
    title: '',
    description: '',
    batch: '',
    date: undefined as Date | undefined,
    time: '',
    instructions: '',
    materials: [] as File[],
});

const dateOpen = ref(false);
const timeOpen = ref(false);

const formattedTime = computed(() => {
    if (!form.value.time) return 'Pick a time';
    const [hours, minutes] = form.value.time.split(':');
    const h = parseInt(hours);
    const m = parseInt(minutes);
    const period = h >= 12 ? 'PM' : 'AM';
    const displayHour = h === 0 ? 12 : h > 12 ? h - 12 : h;
    return `${displayHour}:${String(m).padStart(2, '0')} ${period}`;
});

const uploadedFiles = ref<Array<{ name: string; size: number }>>([]);
const fileInputRef = ref<HTMLInputElement | null>(null);

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        Array.from(target.files).forEach((file) => {
            form.value.materials.push(file);
            uploadedFiles.value.push({
                name: file.name,
                size: file.size,
            });
        });
    }
};

const removeFile = (index: number) => {
    form.value.materials.splice(index, 1);
    uploadedFiles.value.splice(index, 1);
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const submitForm = () => {
    // In a real app, this would submit to the backend
    console.log('Submitting form:', form.value);
    alert('Viva session created successfully!');
};
</script>

<template>
    <Head title="Create Viva Session" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Create Viva Session</h1>
                    <p class="text-muted-foreground">Set up a new viva session for your batch</p>
                </div>
            </div>

            <Form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Basic Information -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Enter the basic details of the viva session</CardDescription>
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
                                </div>
                                <div class="space-y-2">
                                    <Label for="batch">Batch *</Label>
                                    <Input
                                        id="batch"
                                        v-model="form.batch"
                                        placeholder="e.g., CS-2024"
                                        required
                                    />
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
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="date">Date *</Label>
                                    <Popover v-model:open="dateOpen">
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="date"
                                                type="button"
                                                variant="outline"
                                                :class="!form.date && 'text-muted-foreground'"
                                                class="w-full justify-start text-left font-normal"
                                            >
                                                <CalendarIcon class="mr-2 h-4 w-4" />
                                                <span>{{ form.date ? format(form.date, 'PPP') : 'Pick a date' }}</span>
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0" align="start">
                                            <Calendar
                                                v-model:model-value="form.date"
                                                @update:model-value="dateOpen = false"
                                            />
                                        </PopoverContent>
                                    </Popover>
                                </div>
                                <div class="space-y-2">
                                    <Label for="time">Time *</Label>
                                    <Popover v-model:open="timeOpen">
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="time"
                                                type="button"
                                                variant="outline"
                                                :class="!form.time && 'text-muted-foreground'"
                                                class="w-full justify-start text-left font-normal"
                                            >
                                                <Clock class="mr-2 h-4 w-4" />
                                                <span>{{ formattedTime }}</span>
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0" align="start">
                                            <TimePicker
                                                v-model:model-value="form.time"
                                            />
                                        </PopoverContent>
                                    </Popover>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Instructions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Instructions</CardTitle>
                            <CardDescription>Provide instructions for students</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <Label for="instructions">Instructions</Label>
                                <Textarea
                                    id="instructions"
                                    v-model="form.instructions"
                                    placeholder="Enter instructions for students..."
                                    rows="8"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Lecture Materials -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Lecture Materials</CardTitle>
                            <CardDescription>Upload materials and resources (Dropbox integration)</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Dropbox Upload Area -->
                            <div
                                class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-primary transition-colors"
                                @click="() => fileInputRef?.click()"
                            >
                                <Upload class="h-8 w-8 mx-auto mb-2 text-muted-foreground" />
                                <p class="text-sm font-medium mb-1">Click to upload or drag and drop</p>
                                <p class="text-xs text-muted-foreground">PDF, DOC, DOCX, PPT, PPTX (Max 10MB)</p>
                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    multiple
                                    accept=".pdf,.doc,.docx,.ppt,.pptx"
                                    class="hidden"
                                    @change="handleFileUpload"
                                />
                            </div>

                            <!-- Uploaded Files List -->
                            <div v-if="uploadedFiles.length > 0" class="space-y-2">
                                <div class="text-sm font-medium">Uploaded Files:</div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(file, index) in uploadedFiles"
                                        :key="index"
                                        class="flex items-center justify-between rounded-lg border p-3"
                                    >
                                        <div class="flex items-center gap-2 flex-1">
                                            <FileText class="h-4 w-4 text-muted-foreground" />
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium truncate">{{ file.name }}</div>
                                                <div class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</div>
                                            </div>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            @click="removeFile(index)"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dropbox Integration Note -->
                            <div class="rounded-lg bg-muted p-3 text-sm text-muted-foreground">
                                <strong>Note:</strong> You can also connect your Dropbox account to import files directly.
                                <Button type="button" variant="link" size="sm" class="p-0 h-auto ml-1">
                                    Connect Dropbox
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <Button type="button" variant="outline">
                        Cancel
                    </Button>
                    <Button type="submit">
                        Create Viva Session
                    </Button>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>

