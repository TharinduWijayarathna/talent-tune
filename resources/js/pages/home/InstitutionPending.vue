<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { GraduationCap, Clock, Mail, CheckCircle2 } from 'lucide-vue-next'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface Props {
    institution?: {
        id: number
        name: string
        slug: string
        email?: string
        contact_person?: string
    } | null
}

const props = defineProps<Props>()
const page = usePage()

const institution = computed(() => props.institution || (page.props as any).institution)
const institutionName = computed(() => institution.value?.name || 'Your Institution')
</script>

<template>
    <Head :title="`${institutionName} - Activation Pending`" />
    
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">{{ institutionName }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Pending Activation Message -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl">
                    <Card>
                        <CardContent class="pt-12 pb-12">
                            <div class="text-center">
                                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/20">
                                    <Clock class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                                </div>
                                
                                <h1 class="text-3xl font-bold tracking-tight mb-4">
                                    Activation Pending
                                </h1>
                                
                                <p class="text-lg text-muted-foreground mb-8">
                                    Thank you for registering <strong>{{ institutionName }}</strong> with TalentTune.
                                </p>

                                <div class="bg-muted rounded-lg p-6 mb-8 text-left">
                                    <div class="flex items-start gap-4">
                                        <CheckCircle2 class="h-5 w-5 text-primary mt-0.5 flex-shrink-0" />
                                        <div>
                                            <h3 class="font-semibold mb-2">What Happens Next?</h3>
                                            <ul class="space-y-2 text-sm text-muted-foreground">
                                                <li>• Our admin team is reviewing your registration</li>
                                                <li>• You'll receive an email notification once your account is activated</li>
                                                <li>• The email will include your login credentials</li>
                                                <li>• Once activated, you can access your portal at: <code class="bg-background px-2 py-1 rounded">{{ institution?.slug }}.{{ (page.props as any).baseDomain || 'talenttune.com' }}</code></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-8 text-left">
                                    <div class="flex items-start gap-4">
                                        <Mail class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <h3 class="font-semibold mb-2 text-blue-900 dark:text-blue-100">Check Your Email</h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                                <span v-if="institution?.email">
                                                    We'll send the activation notification to <strong>{{ institution.email }}</strong>.
                                                </span>
                                                <span v-else>
                                                    We'll send the activation notification to the email address you provided during registration.
                                                </span>
                                                <br><br>
                                                Please check your inbox (and spam folder) for updates. Activation typically takes 24-48 hours.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm text-muted-foreground">
                                    <p>If you have any questions, please contact our support team.</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </div>
</template>
