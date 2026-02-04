<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Building2, Check, X, Trash2, Search, Mail, Phone, MapPin, User } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Institution {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    contact_person: string | null;
    phone: string | null;
    address: string | null;
    is_active: boolean;
    created_at: string;
}

interface Props {
    institutions: Institution[];
}

const props = defineProps<Props>();

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
    return props.institutions.filter(inst => 
        inst.name.toLowerCase().includes(query) ||
        inst.slug.toLowerCase().includes(query) ||
        inst.email?.toLowerCase().includes(query) ||
        inst.contact_person?.toLowerCase().includes(query)
    );
});

const toggleStatus = (institution: Institution) => {
    router.patch(`/admin/institutions/${institution.id}/status`, {
        is_active: !institution.is_active,
    }, {
        preserveScroll: true,
    });
};

const deleteInstitution = (institution: Institution) => {
    if (confirm(`Are you sure you want to delete ${institution.name}? This action cannot be undone.`)) {
        router.delete(`/admin/institutions/${institution.id}`, {
            preserveScroll: true,
        });
    }
};

const pendingCount = computed(() => props.institutions.filter(i => !i.is_active).length);
const activeCount = computed(() => props.institutions.filter(i => i.is_active).length);
</script>

<template>
    <Head title="Manage Institutions - Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Institution Management</h1>
                    <p class="text-muted-foreground">Manage and activate institution registrations</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Institutions</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ institutions.length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active</CardTitle>
                        <Check class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ activeCount }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Approval</CardTitle>
                        <X class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">{{ pendingCount }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search institutions..."
                    class="pl-10"
                />
            </div>

            <!-- Institutions List -->
            <Card>
                <CardHeader>
                    <CardTitle>Institutions</CardTitle>
                    <CardDescription>All registered institutions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="institution in filteredInstitutions"
                            :key="institution.id"
                            class="flex items-start justify-between rounded-lg border p-4 hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <Building2 class="h-5 w-5 text-muted-foreground" />
                                    <div>
                                        <h3 class="font-semibold">{{ institution.name }}</h3>
                                        <p class="text-sm text-muted-foreground">
                                            {{ institution.slug }}.talenttune.com
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                                    <div v-if="institution.email" class="flex items-center gap-2 text-muted-foreground">
                                        <Mail class="h-4 w-4" />
                                        <span>{{ institution.email }}</span>
                                    </div>
                                    <div v-if="institution.contact_person" class="flex items-center gap-2 text-muted-foreground">
                                        <User class="h-4 w-4" />
                                        <span>{{ institution.contact_person }}</span>
                                    </div>
                                    <div v-if="institution.phone" class="flex items-center gap-2 text-muted-foreground">
                                        <Phone class="h-4 w-4" />
                                        <span>{{ institution.phone }}</span>
                                    </div>
                                    <div v-if="institution.address" class="flex items-center gap-2 text-muted-foreground">
                                        <MapPin class="h-4 w-4" />
                                        <span class="truncate">{{ institution.address }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <span>Registered: {{ new Date(institution.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 ml-4">
                                <Badge :variant="institution.is_active ? 'default' : 'secondary'">
                                    {{ institution.is_active ? 'Active' : 'Pending' }}
                                </Badge>
                                
                                <Button
                                    @click="toggleStatus(institution)"
                                    :variant="institution.is_active ? 'outline' : 'default'"
                                    size="sm"
                                >
                                    <Check v-if="!institution.is_active" class="h-4 w-4 mr-1" />
                                    <X v-else class="h-4 w-4 mr-1" />
                                    {{ institution.is_active ? 'Deactivate' : 'Activate' }}
                                </Button>

                                <Button
                                    @click="deleteInstitution(institution)"
                                    variant="destructive"
                                    size="sm"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div v-if="filteredInstitutions.length === 0" class="text-center py-8 text-muted-foreground">
                            <Building2 class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>No institutions found matching your search.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
