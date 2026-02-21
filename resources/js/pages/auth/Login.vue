<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Building2, GraduationCap, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    showRoleSelection?: boolean;
    /** When true, only institution admin can log in (workspace requires payment). */
    adminLoginOnly?: boolean;
    institution?: {
        name: string;
        slug: string;
    } | null;
}>();

const page = usePage();
const serverErrors = computed(() => page.props.errors || {});

// Combine form errors and server errors for display
const allErrors = computed(() => {
    const errors: Record<string, string> = {};

    // Add form errors
    if (form.errors) {
        Object.assign(errors, form.errors);
    }

    // Add server errors (from page props)
    if (serverErrors.value) {
        Object.assign(errors, serverErrors.value);
    }

    return errors;
});

// When adminLoginOnly, default to institution so we show email/password form only
const selectedRole = ref<'institution' | 'lecturer' | 'student' | null>(
    props.showRoleSelection ? (props.adminLoginOnly ? 'institution' : null) : null,
);

const allRoles = [
    {
        value: 'student',
        label: 'Student',
        icon: GraduationCap,
        description: 'Access viva sessions',
    },
    {
        value: 'lecturer',
        label: 'Lecturer',
        icon: User,
        description: 'Create viva sessions and manage materials',
    },
    {
        value: 'institution',
        label: 'Institution Admin',
        icon: Building2,
        description: 'Manage lecturers and students',
    },
];

// When payment is required, show only institution admin role
const roles = computed(() =>
    props.adminLoginOnly ? allRoles.filter((r) => r.value === 'institution') : allRoles,
);

const form = useForm({
    email: '',
    password: '',
    remember: false,
    role: null as 'institution' | 'lecturer' | 'student' | null,
});

const submit = () => {
    // If role selection is shown, require role to be selected
    if (props.showRoleSelection && !selectedRole.value) {
        return;
    }

    form.role = selectedRole.value;

    form.post('/login', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Reset password only on successful login
            form.reset('password');
        },
        onError: () => {
            // Keep role selection and form data on error
            // Don't reset anything - let user see their mistake and correct it
        },
    });
};
</script>

<template>
    <AuthBase
        :title="
            showRoleSelection && institution
                ? `Log in to ${institution.name}`
                : 'Log in to your account'
        "
        :description="
            adminLoginOnly
                ? 'Log in as institution admin to complete payment and access the workspace'
                : showRoleSelection
                  ? 'Select your role and enter your credentials'
                  : 'Enter your email and password below to log in'
        "
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200"
        >
            {{ status }}
        </div>

        <div
            v-if="Object.keys(allErrors).length > 0"
            class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200"
        >
            <div
                v-for="(error, key) in allErrors"
                :key="key"
                class="mb-1 last:mb-0"
            >
                {{ Array.isArray(error) ? error[0] : error }}
            </div>
        </div>

        <!-- Role Selection (only shown on institution subdomain) -->
        <div
            v-if="showRoleSelection && !selectedRole"
            class="mb-6 w-full space-y-4"
        >
            <div class="text-center text-sm text-muted-foreground">
                Select your role to continue
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card
                    v-for="role in roles"
                    :key="role.value"
                    class="cursor-pointer transition-all hover:border-primary hover:shadow-md"
                    :class="{
                        'border-primary ring-2 ring-primary':
                            selectedRole === role.value,
                    }"
                    @click="selectedRole = role.value as any"
                >
                    <CardHeader class="pb-3 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <component
                                :is="role.icon"
                                class="h-6 w-6 text-primary"
                            />
                            <CardTitle class="text-base">{{
                                role.label
                            }}</CardTitle>
                        </div>
                        <CardDescription class="mt-2 text-center text-xs">
                            {{ role.description }}
                        </CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </div>

        <form
            @submit.prevent="submit"
            class="flex w-full flex-col gap-6"
            v-if="!showRoleSelection || selectedRole"
        >
            <!-- Role Indicator (when role is selected; hide Change Role when admin-only) -->
            <div
                v-if="showRoleSelection && selectedRole"
                class="mb-2 flex w-full items-center justify-between px-1"
            >
                <div class="flex items-center gap-2">
                    <component
                        :is="roles.find((r) => r.value === selectedRole)?.icon"
                        class="h-4 w-4"
                    />
                    <span class="text-sm font-medium">{{
                        roles.find((r) => r.value === selectedRole)?.label
                    }}</span>
                </div>
                <Button
                    v-if="!adminLoginOnly"
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="selectedRole = null"
                >
                    Change Role
                </Button>
            </div>

            <input type="hidden" name="role" :value="selectedRole" />

            <div class="grid w-full gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        :class="{
                            'border-red-500':
                                form.errors.email || allErrors.email,
                        }"
                    />
                    <InputError
                        :message="form.errors.email || allErrors.email"
                    />
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
                        v-model="form.password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                        :class="{
                            'border-red-500':
                                form.errors.password || allErrors.password,
                        }"
                    />
                    <InputError
                        :message="form.errors.password || allErrors.password"
                    />
                </div>

                <div class="flex w-full items-center justify-between">
                    <Label
                        for="remember"
                        class="flex cursor-pointer items-center gap-2"
                    >
                        <Checkbox
                            id="remember"
                            v-model:checked="form.remember"
                            :tabindex="3"
                        />
                        <span class="text-sm">Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :tabindex="4"
                    :disabled="
                        form.processing || (showRoleSelection && !selectedRole)
                    "
                    data-test="login-button"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Log in
                </Button>
            </div>

            <div
                class="w-full text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
