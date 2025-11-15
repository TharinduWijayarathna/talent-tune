<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { GraduationCap, User, Building2, Shield } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const selectedRole = ref<'student' | 'lecturer' | 'institution' | 'admin' | null>(null);

const roles = [
    { value: 'student', label: 'Student', icon: GraduationCap, description: 'Access viva sessions and view marks' },
    { value: 'lecturer', label: 'Lecturer', icon: User, description: 'Create viva sessions and manage materials' },
    { value: 'institution', label: 'Institution', icon: Building2, description: 'Manage lecturers and students' },
    { value: 'admin', label: 'Admin', icon: Shield, description: 'Monitor all activities' },
];
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <div v-if="!selectedRole" class="space-y-4">
            <div class="text-center text-sm text-muted-foreground mb-6">
                Select your role to continue
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Card
                    v-for="role in roles"
                    :key="role.value"
                    class="cursor-pointer transition-all hover:border-primary hover:shadow-md"
                    :class="{ 'border-primary ring-2 ring-primary': selectedRole === role.value }"
                    @click="selectedRole = role.value as any"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-3">
                            <component :is="role.icon" class="h-5 w-5 text-primary" />
                            <CardTitle class="text-base">{{ role.label }}</CardTitle>
                        </div>
                        <CardDescription class="text-xs mt-2">
                            {{ role.description }}
                        </CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </div>

        <Form
            v-else
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <component :is="roles.find(r => r.value === selectedRole)?.icon" class="h-4 w-4" />
                    <span class="text-sm font-medium">{{ roles.find(r => r.value === selectedRole)?.label }}</span>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="selectedRole = null"
                >
                    Change Role
                </Button>
            </div>

            <input type="hidden" name="role" :value="selectedRole" />

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
