<script setup lang="ts">
// Theme: application uses light mode only. Theme change (light/dark) has been removed from profile/settings; dark mode is disabled.
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Sparkles, Trash2, Upload } from 'lucide-vue-next';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
const { getInitials } = useInitials();

const avatarUploadRef = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);
const isGenerating = ref(false);
const generateError = ref('');
const aiDialogOpen = ref(false);

const aiOptions = ref({
    style: 'professional',
    background: 'neutral',
    mood: 'friendly',
    gender: 'neutral',
});

const csrfToken = (page.props as { csrfToken?: string }).csrfToken ?? '';

const avatarUrl = computed(
    () =>
        (page.props.auth as { user?: { avatar?: string } })?.user?.avatar ??
        null,
);

function triggerAvatarUpload() {
    avatarUploadRef.value?.click();
}

async function onAvatarFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    isUploading.value = true;
    generateError.value = '';
    try {
        const formData = new FormData();
        formData.append('avatar', file);
        const response = await fetch('/settings/profile/avatar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: formData,
        });
        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(
                data.message ?? data.errors?.avatar?.[0] ?? 'Upload failed',
            );
        }
        router.reload();
    } catch (e: unknown) {
        generateError.value = e instanceof Error ? e.message : 'Upload failed';
    } finally {
        isUploading.value = false;
        input.value = '';
    }
}

async function generateAvatar() {
    isGenerating.value = true;
    generateError.value = '';
    try {
        const response = await fetch('/settings/profile/avatar/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(aiOptions.value),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.error ?? 'Generation failed');
        }
        aiDialogOpen.value = false;
        router.reload();
    } catch (e: unknown) {
        generateError.value =
            e instanceof Error ? e.message : 'Generation failed';
    } finally {
        isGenerating.value = false;
    }
}

async function removeAvatar() {
    try {
        const response = await fetch('/settings/profile/avatar', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        if (response.ok) router.reload();
    } catch {
        // ignore
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile photo"
                    description="Upload a photo or generate one with AI"
                />
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <Avatar
                        class="h-24 w-24 shrink-0 overflow-hidden rounded-full border-2 border-muted"
                    >
                        <AvatarImage
                            v-if="avatarUrl"
                            :src="avatarUrl"
                            :alt="user.name"
                        />
                        <AvatarFallback
                            class="bg-muted text-lg text-muted-foreground"
                        >
                            {{ getInitials(user?.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            ref="avatarUploadRef"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                            class="hidden"
                            @change="onAvatarFileChange"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            :disabled="isUploading"
                            @click="triggerAvatarUpload"
                        >
                            <Upload class="mr-2 h-4 w-4" />
                            {{ isUploading ? 'Uploading…' : 'Upload photo' }}
                        </Button>
                        <Dialog v-model:open="aiDialogOpen">
                            <DialogTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                >
                                    <Sparkles class="mr-2 h-4 w-4" />
                                    Generate with AI
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-md">
                                <DialogHeader>
                                    <DialogTitle
                                        >Generate profile photo with
                                        AI</DialogTitle
                                    >
                                    <DialogDescription>
                                        Answer a few questions so we can create
                                        a profile picture that fits your style.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-4 py-4">
                                    <div class="grid gap-2">
                                        <Label for="ai-style">Style</Label>
                                        <select
                                            id="ai-style"
                                            v-model="aiOptions.style"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option value="professional">
                                                Professional
                                            </option>
                                            <option value="casual">
                                                Casual
                                            </option>
                                            <option value="creative">
                                                Creative
                                            </option>
                                        </select>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="ai-background"
                                            >Background</Label
                                        >
                                        <select
                                            id="ai-background"
                                            v-model="aiOptions.background"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option value="neutral">
                                                Neutral
                                            </option>
                                            <option value="outdoor">
                                                Outdoor
                                            </option>
                                            <option value="abstract">
                                                Abstract
                                            </option>
                                            <option value="minimal">
                                                Minimal
                                            </option>
                                        </select>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="ai-mood">Mood</Label>
                                        <select
                                            id="ai-mood"
                                            v-model="aiOptions.mood"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option value="friendly">
                                                Friendly
                                            </option>
                                            <option value="serious">
                                                Serious
                                            </option>
                                            <option value="approachable">
                                                Approachable
                                            </option>
                                        </select>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="ai-gender"
                                            >Appearance</Label
                                        >
                                        <select
                                            id="ai-gender"
                                            v-model="aiOptions.gender"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                        >
                                            <option value="neutral">
                                                Neutral
                                            </option>
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                        </select>
                                    </div>
                                    <p
                                        v-if="generateError"
                                        class="text-sm text-destructive"
                                    >
                                        {{ generateError }}
                                    </p>
                                </div>
                                <DialogFooter>
                                    <Button
                                        type="button"
                                        :disabled="isGenerating"
                                        @click="generateAvatar"
                                    >
                                        <Sparkles class="mr-2 h-4 w-4" />
                                        {{
                                            isGenerating
                                                ? 'Generating…'
                                                : 'Generate photo'
                                        }}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                        <Button
                            v-if="avatarUrl"
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground"
                            @click="removeAvatar"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Remove
                        </Button>
                    </div>
                </div>

                <HeadingSmall
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
