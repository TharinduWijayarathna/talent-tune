<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { GraduationCap, CheckCircle2, Clock, Mail } from 'lucide-vue-next'

interface Props {
    institution: {
        id: number
        name: string
        slug: string
    }
}

defineProps<Props>()
</script>

<template>
    <Head title="Registration Submitted - TalentTune" />
    
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">TalentTune</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Success Message -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl">
                    <Card>
                        <CardContent class="pt-12 pb-12">
                            <div class="text-center">
                                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                    <CheckCircle2 class="h-8 w-8 text-green-600 dark:text-green-400" />
                                </div>
                                
                                <h1 class="text-3xl font-bold tracking-tight mb-4">
                                    Registration Submitted Successfully!
                                </h1>
                                
                                <p class="text-lg text-muted-foreground mb-8">
                                    Thank you for registering <strong>{{ institution.name }}</strong> with TalentTune.
                                </p>

                                <div class="bg-muted rounded-lg p-6 mb-8 text-left">
                                    <div class="flex items-start gap-4">
                                        <Clock class="h-5 w-5 text-muted-foreground mt-0.5" />
                                        <div>
                                            <h3 class="font-semibold mb-2">What Happens Next?</h3>
                                            <ul class="space-y-2 text-sm text-muted-foreground">
                                                <li>• Our admin team will review your registration</li>
                                                <li>• You'll receive an email notification once your account is activated</li>
                                                <li>• Once activated, access your portal at: <code class="bg-background px-2 py-1 rounded">{{ institution.slug }}.talenttune.com</code></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-8 text-left">
                                    <div class="flex items-start gap-4">
                                        <Mail class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                                        <div>
                                            <h3 class="font-semibold mb-2 text-blue-900 dark:text-blue-100">Check Your Email</h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                                We've sent a confirmation email with your registration details. 
                                                Please check your inbox (and spam folder) for updates.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                    <Link href="/">
                                        <Button variant="outline">
                                            Back to Home
                                        </Button>
                                    </Link>
                                    <Link href="/login">
                                        <Button>
                                            Sign In
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </div>
</template>
