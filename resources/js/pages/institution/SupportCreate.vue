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
    { title: 'Dashboard', href: '/institution/dashboard' },
    { title: 'Support', href: '/institution/support' },
    { title: 'New Ticket', href: '/institution/support/create' },
];

const form = useForm({
    subject: '',
    body: '',
});

const submitForm = () => {
    form.post('/institution/support');
};
</script>

<template>
    <Head title="New Support Ticket" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div>
                <h1 class="text-2xl font-bold">New support ticket</h1>
                <p class="text-muted-foreground">
                    Describe your issue and we’ll get back to you
                </p>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Submit a ticket</CardTitle>
                    <CardDescription
                        >Provide a subject and details so we can help
                        you</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="subject">Subject *</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                placeholder="Brief summary of your request"
                                required
                            />
                            <InputError :message="form.errors.subject" />
                        </div>
                        <div class="space-y-2">
                            <Label for="body">Message *</Label>
                            <Textarea
                                id="body"
                                v-model="form.body"
                                placeholder="Describe your issue or question in detail..."
                                rows="6"
                                required
                            />
                            <InputError :message="form.errors.body" />
                        </div>
                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/institution/support">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Submit ticket
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
