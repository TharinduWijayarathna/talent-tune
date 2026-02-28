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
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Mail, Plus, Shield, User } from 'lucide-vue-next';

interface AdminItem {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
}

const props = defineProps<{
    admins: AdminItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'TalentTune Admins', href: '/admin/talenttune-admins' },
];

const formatDate = (iso: string) =>
    iso
        ? new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' })
        : '';
</script>

<template>
    <Head title="TalentTune Admins - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">System Users</h1>
                    <p class="text-muted-foreground">Manage system users</p>
                </div>
                <Button as-child>
                    <Link href="/admin/talenttune-admins/add">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Admin
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle
                        >System Users ({{ props.admins.length }})</CardTitle
                    >
                    <CardDescription
                        >Users with full access to manage institutions, users,
                        payments, reports, and support</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="admin in props.admins"
                            :key="admin.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-all hover:bg-muted/50"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <Shield class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold">
                                            {{ admin.name }}
                                        </h3>
                                        <Badge variant="secondary">
                                            Admin
                                        </Badge>
                                    </div>
                                    <div
                                        class="flex items-center gap-1 text-sm text-muted-foreground"
                                    >
                                        <Mail class="h-4 w-4" />
                                        {{ admin.email }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Added {{ formatDate(admin.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="props.admins.length === 0"
                            class="py-12 text-center"
                        >
                            <User
                                class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                            />
                            <p class="text-muted-foreground">
                                No TalentTune admins yet
                            </p>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Add administrators to manage the platform
                            </p>
                            <Button as-child class="mt-4">
                                <Link href="/admin/talenttune-admins/add">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Admin
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
