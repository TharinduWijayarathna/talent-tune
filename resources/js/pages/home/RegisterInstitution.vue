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
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Building2, GraduationCap } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    contact_person: '',
    phone: '',
    address: '',
    primary_color: '#3b82f6',
});

const submit = () => {
    form.post('/register-institution', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by backend
        },
    });
};
</script>

<template>
    <Head title="Register Your Institution - TalentTune" />

    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav
            class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <GraduationCap class="h-6 w-6 text-primary" />
                        <span class="text-xl font-bold">TalentTune</span>
                    </Link>
                    <div class="flex items-center gap-4">
                        <Link
                            href="/"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Back to Home
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Registration Form -->
        <section class="py-20 sm:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl">
                    <div class="mb-8 text-center">
                        <div
                            class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                        >
                            <Building2 class="h-6 w-6 text-primary" />
                        </div>
                        <h1
                            class="mb-2 text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Register Your Institution
                        </h1>
                        <p class="text-lg text-muted-foreground">
                            Join TalentTune and transform how your institution
                            conducts viva examinations.
                        </p>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Institution Details</CardTitle>
                            <CardDescription>
                                Fill out the form below. Our admin team will
                                review your application and activate your
                                account.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="space-y-2">
                                    <Label for="name">Institution Name *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="e.g., University of Technology"
                                        required
                                        :class="{
                                            'border-red-500': form.errors.name,
                                        }"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Contact Email *</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="contact@university.edu"
                                        required
                                        :class="{
                                            'border-red-500': form.errors.email,
                                        }"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="contact_person"
                                        >Contact Person Name *</Label
                                    >
                                    <Input
                                        id="contact_person"
                                        v-model="form.contact_person"
                                        type="text"
                                        placeholder="e.g., Dr. John Smith"
                                        required
                                        :class="{
                                            'border-red-500':
                                                form.errors.contact_person,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.contact_person"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone">Phone Number</Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="+1 (555) 123-4567"
                                        :class="{
                                            'border-red-500': form.errors.phone,
                                        }"
                                    />
                                    <InputError :message="form.errors.phone" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="address"
                                        >Institution Address</Label
                                    >
                                    <Textarea
                                        id="address"
                                        v-model="form.address"
                                        placeholder="123 University Street, City, State, ZIP"
                                        rows="3"
                                        :class="{
                                            'border-red-500':
                                                form.errors.address,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.address"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="primary_color"
                                        >Primary Brand Color</Label
                                    >
                                    <div class="flex items-center gap-4">
                                        <Input
                                            id="primary_color"
                                            v-model="form.primary_color"
                                            type="color"
                                            class="h-12 w-24 cursor-pointer"
                                        />
                                        <Input
                                            v-model="form.primary_color"
                                            type="text"
                                            placeholder="#3b82f6"
                                            class="flex-1"
                                            pattern="^#[0-9A-Fa-f]{6}$"
                                        />
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        This color will be used for your
                                        institution's branding.
                                    </p>
                                    <InputError
                                        :message="form.errors.primary_color"
                                    />
                                </div>

                                <div class="flex items-center gap-4 pt-4">
                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1"
                                    >
                                        <span v-if="form.processing"
                                            >Submitting...</span
                                        >
                                        <span
                                            v-else
                                            class="flex items-center gap-2"
                                        >
                                            Submit Registration
                                            <ArrowRight class="h-4 w-4" />
                                        </span>
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <div class="mt-6 text-center text-sm text-muted-foreground">
                        <p>
                            Already have an account?
                            <Link
                                href="/login"
                                class="text-primary hover:underline"
                                >Sign in</Link
                            >
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
