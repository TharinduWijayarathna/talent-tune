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
import { useDomain } from '@/composables/useDomain';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    Check,
    Mail,
    MapPin,
    Pencil,
    Phone,
    TimerOff,
    Trash2,
    User,
    X,
} from 'lucide-vue-next';

interface Institution {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    contact_person: string | null;
    phone: string | null;
    address: string | null;
    is_active: boolean;
    subscription_status: string | null;
    trial_ends_at: string | null;
    created_at: string;
}

const props = defineProps<{
    institution: Institution;
}>();

const { baseDomain } = useDomain();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Institutions', href: '/admin/institutions' },
    { title: props.institution.name, href: '#' },
];

const hasActiveTrial = () =>
    props.institution.trial_ends_at &&
    new Date(props.institution.trial_ends_at) > new Date();
const hasPaidSubscription = () =>
    props.institution.subscription_status === 'active';

const toggleStatus = () => {
    router.patch(
        `/admin/institutions/${props.institution.slug}/status`,
        { is_active: !props.institution.is_active },
        { preserveScroll: true },
    );
};

const endTrial = () => {
    if (
        !confirm(
            `End trial for ${props.institution.name}? They will be asked to pay immediately to continue access.`,
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

const deleteInstitution = () => {
    if (
        !confirm(
            `Are you sure you want to delete ${props.institution.name}? This action cannot be undone.`,
        )
    ) {
        router.delete(`/admin/institutions/${props.institution.slug}`, {
            onSuccess: () => router.visit('/admin/institutions'),
        });
    }
};
</script>

<template>
    <Head :title="`${institution.name} - Admin`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="'/admin/institutions'">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ institution.name }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{ institution.slug }}.{{ baseDomain }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link
                            :href="`/admin/institutions/${institution.slug}/edit`"
                        >
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button
                        v-if="hasActiveTrial() && !hasPaidSubscription()"
                        variant="outline"
                        size="sm"
                        @click="endTrial"
                    >
                        <TimerOff class="mr-2 h-4 w-4" />
                        End trial
                    </Button>
                    <Button
                        :variant="institution.is_active ? 'outline' : 'default'"
                        size="sm"
                        @click="toggleStatus"
                    >
                        <Check
                            v-if="!institution.is_active"
                            class="mr-2 h-4 w-4"
                        />
                        <X v-else class="mr-2 h-4 w-4" />
                        {{ institution.is_active ? 'Deactivate' : 'Activate' }}
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="deleteInstitution"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Status -->
            <div class="flex flex-wrap gap-2">
                <Badge
                    :variant="institution.is_active ? 'default' : 'secondary'"
                >
                    {{ institution.is_active ? 'Active' : 'Pending' }}
                </Badge>
                <Badge
                    v-if="hasPaidSubscription()"
                    variant="default"
                    class="bg-green-600"
                >
                    Subscribed
                </Badge>
                <Badge
                    v-else-if="hasActiveTrial()"
                    variant="secondary"
                    class="border-amber-500 text-amber-700"
                >
                    Trial until
                    {{
                        new Date(
                            institution.trial_ends_at!,
                        ).toLocaleDateString()
                    }}
                </Badge>
                <Badge
                    v-else-if="
                        institution.trial_ends_at && !hasPaidSubscription()
                    "
                    variant="secondary"
                >
                    Trial ended
                </Badge>
            </div>

            <!-- Details -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Details
                    </CardTitle>
                    <CardDescription>
                        Contact and registration info
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-if="institution.email"
                            class="flex items-center gap-3 text-sm"
                        >
                            <Mail class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-muted-foreground">Email</p>
                                <p class="font-medium">
                                    {{ institution.email }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="institution.contact_person"
                            class="flex items-center gap-3 text-sm"
                        >
                            <User class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-muted-foreground">
                                    Contact person
                                </p>
                                <p class="font-medium">
                                    {{ institution.contact_person }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="institution.phone"
                            class="flex items-center gap-3 text-sm"
                        >
                            <Phone class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-muted-foreground">Phone</p>
                                <p class="font-medium">
                                    {{ institution.phone }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="institution.address"
                            class="flex items-start gap-3 text-sm sm:col-span-2"
                        >
                            <MapPin
                                class="mt-0.5 h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p class="text-muted-foreground">Address</p>
                                <p class="font-medium">
                                    {{ institution.address }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="border-t pt-4 text-xs text-muted-foreground">
                        Registered
                        {{
                            new Date(
                                institution.created_at,
                            ).toLocaleDateString()
                        }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
