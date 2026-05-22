<script setup lang="ts">
import MarketingLayout from '@/components/marketing/MarketingLayout.vue';
import { useDomain } from '@/composables/useDomain';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, Clock, Mail } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    institution: {
        id: number;
        name: string;
        slug: string;
    };
}

const props = defineProps<Props>();
const { baseDomain } = useDomain();

const workspaceLoginUrl = computed(() => {
    const slug = props.institution?.slug;
    const domain = baseDomain.value;
    if (!slug || !domain) return '/login';
    const protocol =
        typeof window !== 'undefined' ? window.location.protocol : 'https:';
    return `${protocol}//${slug}.${domain}/login`;
});
</script>

<template>
    <MarketingLayout title="Registration Submitted - Viva Suite">
        <section class="marketing-page-section">
            <div class="marketing-page-inner marketing-page-inner--narrow">
                <div class="marketing-info-card">
                    <div class="marketing-success-icon">
                        <CheckCircle2 :size="36" stroke-width="1.5" />
                    </div>

                    <h1 class="marketing-page-title">
                        Registration submitted
                    </h1>
                    <p class="marketing-page-sub" style="margin-bottom: 32px">
                        Thank you for registering
                        <strong>{{ institution.name }}</strong> with Viva Suite.
                        We will review your application shortly.
                    </p>

                    <div class="marketing-callout">
                        <div
                            style="
                                display: flex;
                                gap: 12px;
                                align-items: flex-start;
                            "
                        >
                            <Clock
                                :size="20"
                                style="
                                    flex-shrink: 0;
                                    margin-top: 2px;
                                    color: var(--text3);
                                "
                            />
                            <div>
                                <h3 class="marketing-callout-title">
                                    What happens next
                                </h3>
                                <ul>
                                    <li>
                                        Our admin team reviews your registration
                                    </li>
                                    <li>
                                        You receive an email when your account is
                                        activated
                                    </li>
                                    <li>
                                        Access your portal at
                                        <code
                                            >{{ institution.slug }}.{{
                                                baseDomain
                                            }}</code
                                        >
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div
                        class="marketing-callout marketing-callout--accent"
                        style="margin-top: 16px"
                    >
                        <div
                            style="
                                display: flex;
                                gap: 12px;
                                align-items: flex-start;
                            "
                        >
                            <Mail
                                :size="20"
                                style="
                                    flex-shrink: 0;
                                    margin-top: 2px;
                                    color: var(--accent2);
                                "
                            />
                            <div>
                                <h3 class="marketing-callout-title">
                                    Check your email
                                </h3>
                                <p>
                                    We sent a confirmation with your registration
                                    details. Check your inbox and spam folder for
                                    updates.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="marketing-actions-row">
                        <Link href="/" class="btn-secondary">
                            Back to home
                        </Link>
                        <a
                            :href="workspaceLoginUrl"
                            class="btn-primary"
                        >
                            Sign in to workspace
                            <svg
                                class="btn-arrow"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                            >
                                <path
                                    d="M3 8H13M9 4L13 8L9 12"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
