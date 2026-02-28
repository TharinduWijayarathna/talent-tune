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
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { TimerOff } from 'lucide-vue-next';

interface InstitutionForm {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    contact_person: string | null;
    phone: string | null;
    address: string | null;
    is_active: boolean;
    subscription_status?: string | null;
    trial_ends_at?: string | null;
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
    is_active: props.institution.is_active,
});

const submit = () => {
    form.put(`/admin/institutions/${props.institution.slug}`);
};

const hasActiveTrial = () =>
    props.institution.trial_ends_at &&
    new Date(props.institution.trial_ends_at) > new Date();
const hasPaidSubscription = () =>
    props.institution.subscription_status === 'active';

const endTrial = () => {
    if (
        !confirm(
            'End this institution\'s trial now? They will be asked to pay to continue access.',
        )
    ) {
        return;
    }
    router.patch(
        `/admin/institutions/${props.institution.slug}/end-trial`,
        {},
        { preserveScroll: true },
    );
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
                        Update the institution information. The subdomain (slug)
                        cannot be changed.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="slug">Subdomain (read-only)</Label>
                            <div
                                class="flex items-center gap-2 rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground"
                            >
                                <span
                                    >{{ institution.slug }}.{{
                                        baseDomain
                                    }}</span
                                >
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
                                <Label for="contact_person"
                                    >Contact Person *</Label
                                >
                                <Input
                                    id="contact_person"
                                    v-model="form.contact_person"
                                    type="text"
                                    placeholder="Dr. Jane Smith"
                                    required
                                />
                                <InputError
                                    :message="form.errors.contact_person"
                                />
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

                        <div class="flex items-center gap-2">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-input"
                            />
                            <Label
                                for="is_active"
                                class="cursor-pointer font-normal"
                            >
                                Institution is active (can access the platform)
                            </Label>
                        </div>
                        <InputError :message="form.errors.is_active" />

                        <div
                            v-if="
                                institution.trial_ends_at &&
                                (hasActiveTrial() || !hasPaidSubscription())
                            "
                            class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30"
                        >
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                Trial
                                {{
                                    hasActiveTrial()
                                        ? `ends ${new Date(institution.trial_ends_at!).toLocaleDateString()}`
                                        : 'has ended'
                                }}
                            </p>
                            <Button
                                v-if="hasActiveTrial() && !hasPaidSubscription()"
                                type="button"
                                variant="outline"
                                size="sm"
                                class="mt-2"
                                @click="endTrial"
                            >
                                <TimerOff class="mr-2 h-4 w-4" />
                                End trial now
                            </Button>
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            <Button type="button" variant="outline" as-child>
                                <Link
                                    :href="`/admin/institutions/${institution.slug}`"
                                >
                                    Cancel
                                </Link>
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
