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
import { useDomain } from '@/composables/useDomain';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface InstitutionForm {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    contact_person: string | null;
    phone: string | null;
    address: string | null;
    primary_color: string | null;
    is_active: boolean;
    subscription_status?: string | null;
}

const props = defineProps<{
    institution: InstitutionForm;
}>();

const { baseDomain } = useDomain();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Institutions', href: '/admin/institutions' },
    { title: 'Edit Institution', href: '#' },
];

const form = useForm({
    name: props.institution.name,
    email: props.institution.email ?? '',
    contact_person: props.institution.contact_person ?? '',
    phone: props.institution.phone ?? '',
    address: props.institution.address ?? '',
    primary_color: props.institution.primary_color ?? '#3b82f6',
    is_active: props.institution.is_active,
});

const submit = () => {
    form.put(`/admin/institutions/${props.institution.slug}`);
};
</script>

<template>
    <Head title="Edit Institution - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Edit Institution</h1>
                    <p class="text-muted-foreground">
                        Update institution details
                    </p>
                </div>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Institution Details</CardTitle>
                    <CardDescription>
                        Update the institution information. The subdomain (slug) cannot be changed.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="slug">Subdomain (read-only)</Label>
                            <div class="flex items-center gap-2 rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground">
                                <span>{{ institution.slug }}.{{ baseDomain }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Institution Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., University of Technology"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email">Contact Email *</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="contact@university.edu"
                                    required
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="contact_person">Contact Person *</Label>
                                <Input
                                    id="contact_person"
                                    v-model="form.contact_person"
                                    type="text"
                                    placeholder="Dr. Jane Smith"
                                    required
                                />
                                <InputError :message="form.errors.contact_person" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                placeholder="+1 234 567 8900"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Textarea
                                id="address"
                                v-model="form.address"
                                placeholder="123 Campus Drive, City, Country"
                                rows="2"
                            />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="space-y-2">
                            <Label for="primary_color">Primary Color</Label>
                            <div class="flex gap-2">
                                <input
                                    id="primary_color"
                                    v-model="form.primary_color"
                                    type="color"
                                    class="h-10 w-14 cursor-pointer rounded border border-input"
                                />
                                <Input
                                    v-model="form.primary_color"
                                    type="text"
                                    placeholder="#3b82f6"
                                    class="flex-1 font-mono text-sm"
                                />
                            </div>
                            <InputError :message="form.errors.primary_color" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-input"
                            />
                            <Label for="is_active" class="cursor-pointer font-normal">
                                Institution is active (can access the platform)
                            </Label>
                        </div>
                        <InputError :message="form.errors.is_active" />

                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/admin/institutions">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update Institution
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
