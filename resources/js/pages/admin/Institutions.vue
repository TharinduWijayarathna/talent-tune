<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useDomain } from '@/composables/useDomain';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Building2, Check, ChevronRight, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

interface Props {
    institutions: Institution[];
}

const props = defineProps<Props>();

const { baseDomain } = useDomain();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Institutions', href: '/admin/institutions' },
];

const searchQuery = ref('');

const filteredInstitutions = computed(() => {
    if (!searchQuery.value) {
        return props.institutions;
    }

    const query = searchQuery.value.toLowerCase();
    return props.institutions.filter(
        (inst) =>
            inst.name.toLowerCase().includes(query) ||
            inst.slug.toLowerCase().includes(query) ||
            inst.email?.toLowerCase().includes(query) ||
            inst.contact_person?.toLowerCase().includes(query),
    );
});

const hasActiveTrial = (inst: Institution) =>
    inst.trial_ends_at && new Date(inst.trial_ends_at) > new Date();
const hasPaidSubscription = (inst: Institution) =>
    inst.subscription_status === 'active';

const pendingCount = computed(
    () => props.institutions.filter((i) => !i.is_active).length,
);
const activeCount = computed(
    () => props.institutions.filter((i) => i.is_active).length,
);
</script>

<template>
    <Head title="Manage Institutions - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Institutions</h1>
                    <p class="text-muted-foreground">
                        Manage and activate institution registrations
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total
                        </CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ institutions.length }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Active
                        </CardTitle>
                        <Check class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ activeCount }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Pending
                        </CardTitle>
                        <X class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ pendingCount }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search -->
            <div class="relative max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search institutions..."
                    class="pl-10"
                />
            </div>

            <!-- Institutions List (minimal) -->
            <Card>
                <CardHeader>
                    <CardTitle>All institutions</CardTitle>
                    <CardDescription>
                        Click an institution to view details and actions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="divide-y rounded-lg border">
                        <Link
                            v-for="institution in filteredInstitutions"
                            :key="institution.id"
                            :href="`/admin/institutions/${institution.slug}`"
                            class="flex items-center justify-between gap-4 px-4 py-3 transition-colors hover:bg-muted/50"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="font-medium truncate">
                                    {{ institution.name }}
                                </p>
                                <p class="text-sm text-muted-foreground truncate">
                                    {{ institution.slug }}.{{ baseDomain }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <Badge
                                    :variant="
                                        institution.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="text-xs"
                                >
                                    {{
                                        institution.is_active
                                            ? 'Active'
                                            : 'Pending'
                                    }}
                                </Badge>
                                <Badge
                                    v-if="hasPaidSubscription(institution)"
                                    variant="default"
                                    class="bg-green-600 text-xs"
                                >
                                    Subscribed
                                </Badge>
                                <Badge
                                    v-else-if="hasActiveTrial(institution)"
                                    variant="secondary"
                                    class="border-amber-500 text-amber-700 text-xs"
                                >
                                    Trial
                                </Badge>
                                <ChevronRight
                                    class="h-4 w-4 shrink-0 text-muted-foreground"
                                />
                            </div>
                        </Link>
                    </div>

                    <div
                        v-if="filteredInstitutions.length === 0"
                        class="py-12 text-center text-muted-foreground"
                    >
                        <Building2
                            class="mx-auto mb-3 h-10 w-10 opacity-50"
                        />
                        <p>No institutions found.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
