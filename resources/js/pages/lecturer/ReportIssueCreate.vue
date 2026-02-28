<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/lecturer/dashboard' },
    { title: 'Report Issue', href: '/lecturer/issues' },
    { title: 'New Issue', href: '/lecturer/issues/create' },
];

const form = useForm({
    subject: '',
    body: '',
});

const submitForm = () => {
    form.post('/lecturer/issues');
};
</script>

<template>
    <Head title="Report Issue" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div>
                <h1 class="text-2xl font-bold">Report an issue</h1>
                <p class="text-muted-foreground">
                    Your institution admin will review and may escalate to
                    TalentTune support
                </p>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Submit an issue</CardTitle>
                    <CardDescription
                        >Describe the issue you are
                        experiencing</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="subject">Subject *</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                placeholder="Brief summary of the issue"
                                required
                            />
                            <InputError :message="form.errors.subject" />
                        </div>
                        <div class="space-y-2">
                            <Label for="body">Description *</Label>
                            <Textarea
                                id="body"
                                v-model="form.body"
                                placeholder="Describe the issue in detail..."
                                rows="6"
                                required
                            />
                            <InputError :message="form.errors.body" />
                        </div>
                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/lecturer/issues">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Submit
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
