<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { GraduationCap, Search, Building2, ArrowRight } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import type { Institution } from '@/types'

interface Props {
    institutions: Array<{
        id: number
        name: string
        slug: string
        logo_url?: string | null
    }>
}

const props = defineProps<Props>()

const searchQuery = ref('')

const filteredInstitutions = computed(() => {
    if (!searchQuery.value) {
        return props.institutions
    }
    
    const query = searchQuery.value.toLowerCase()
    return props.institutions.filter(inst => 
        inst.name.toLowerCase().includes(query) ||
        inst.slug.toLowerCase().includes(query)
    )
})

const selectInstitution = (institution: Institution) => {
    // Redirect to institution subdomain or path
    const host = window.location.host
    const protocol = window.location.protocol
    
    // Check if we're on a subdomain already
    const parts = host.split('.')
    
    if (parts.length >= 3 || host.includes('.test')) {
        // Replace subdomain
        const baseDomain = parts.slice(-2).join('.')
        const newHost = `${institution.slug}.${baseDomain}`
        window.location.href = `${protocol}//${newHost}/`
    } else {
        // Use path-based routing
        router.visit(`/${institution.slug}/`)
    }
}
</script>

<template>
    <Head title="Select Your Institution - TalentTune" />
    
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">TalentTune</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            :href="'/login'"
                            class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 sm:py-32">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
                        Select Your
                        <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                            Institution
                        </span>
                    </h1>
                    <p class="mb-10 text-lg text-muted-foreground sm:text-xl lg:text-2xl">
                        Choose your institution to access TalentTune's AI-powered viva management platform.
                    </p>
                    
                    <!-- Search -->
                    <div class="mb-8 max-w-md mx-auto">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search institutions..."
                                class="pl-10"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Institutions Grid -->
        <section class="py-12">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="filteredInstitutions.length === 0" class="text-center py-12">
                    <Building2 class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <p class="text-lg text-muted-foreground">No institutions found matching your search.</p>
                </div>
                
                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
                    <Card
                        v-for="institution in filteredInstitutions"
                        :key="institution.id"
                        class="cursor-pointer hover:shadow-lg transition-shadow"
                        @click="selectInstitution(institution)"
                    >
                        <CardHeader>
                            <div class="flex items-center gap-4 mb-4">
                                <div v-if="institution.logo_url" class="h-12 w-12 rounded-lg overflow-hidden bg-muted flex items-center justify-center">
                                    <img :src="institution.logo_url" :alt="institution.name" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <Building2 class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <CardTitle class="text-lg">{{ institution.name }}</CardTitle>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">{{ institution.slug }}</span>
                                <ArrowRight class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t bg-background py-12 mt-20">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} TalentTune. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
