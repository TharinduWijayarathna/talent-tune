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
import { useForm, Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import InputError from '@/components/InputError.vue';

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

const fileInputRef = ref<HTMLInputElement | null>(null);
const uploadedFiles = ref<Array<{ name: string; size: number; file: File }>>([]);

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        Array.from(target.files).forEach((file) => {
            // Check file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                return;
            }
            uploadedFiles.value.push({ name: file.name, size: file.size, file });
        });
        // Reset input to allow selecting the same file again
        target.value = '';
    }
};

const removeFile = (index: number) => {
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
    // Create FormData to handle file uploads
    const formData = new FormData();
    
    // Add form fields
    formData.append('title', form.title);
    formData.append('description', form.description || '');
    formData.append('batch', form.batch);
    formData.append('date', form.date);
    formData.append('time', form.time);
    formData.append('instructions', form.instructions || '');
    
    // Add files
    uploadedFiles.value.forEach((fileItem, index) => {
        formData.append(`lecture_materials[${index}]`, fileItem.file);
    });
    
    // Submit using Inertia's post method with FormData
    form.transform(() => formData).post('/lecturer/vivas', {
        forceFormData: true,
        preserveScroll: true,
    });
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

            <form @submit.prevent="submitForm" class="space-y-6">
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
                                    <InputError :message="form.errors.title" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="batch">Batch *</Label>
                                    <select
                                        id="batch"
                                        v-model="form.batch"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                    >
                                        <option value="">Select a batch</option>
                                        <option v-for="batch in availableBatches" :key="batch" :value="batch">
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
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="date">Date *</Label>
                                    <Input
                                        id="date"
                                        v-model="form.date"
                                        type="date"
                                        required
                                    />
                                    <InputError :message="form.errors.date" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="time">Time *</Label>
                                    <Input
                                        id="time"
                                        v-model="form.time"
                                        type="time"
                                        required
                                    />
                                    <InputError :message="form.errors.time" />
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

