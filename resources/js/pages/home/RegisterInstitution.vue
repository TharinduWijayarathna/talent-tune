<script setup lang="ts">
import MarketingLayout from '@/components/marketing/MarketingLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    contact_person: '',
    phone: '',
    address: '',
});

const submit = () => {
    form.post('/register-institution', {
        preserveScroll: true,
    });
};
</script>

<template>
    <MarketingLayout title="Register Your Institution - Viva Suite">
        <section class="marketing-page-section">
            <div class="marketing-page-inner marketing-page-inner--narrow">
                <div class="marketing-page-header">
                    <div class="marketing-page-icon">
                        <Building2 :size="28" stroke-width="1.5" />
                    </div>
                    <h1 class="marketing-page-title">
                        Register Your Institution
                    </h1>
                    <p class="marketing-page-sub">
                        Join Viva Suite and transform how your institution
                        conducts viva examinations. Our team will review your
                        application and activate your workspace.
                    </p>
                </div>

                <div class="marketing-form-card">
                    <div class="marketing-form-card-header">
                        <h2 class="marketing-form-card-title">
                            Institution details
                        </h2>
                        <p class="marketing-form-card-desc">
                            Fields marked with * are required. You will receive
                            email confirmation once submitted.
                        </p>
                    </div>

                    <form
                        class="marketing-form-body"
                        @submit.prevent="submit"
                    >
                        <div class="marketing-form-grid">
                            <div class="marketing-field">
                                <label class="marketing-label" for="name">
                                    Institution name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="marketing-input"
                                    :class="{
                                        'marketing-input--error':
                                            form.errors.name,
                                    }"
                                    placeholder="e.g. University of Technology"
                                    required
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="marketing-field-error"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="marketing-field">
                                <label class="marketing-label" for="email">
                                    Contact email *
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="marketing-input"
                                    :class="{
                                        'marketing-input--error':
                                            form.errors.email,
                                    }"
                                    placeholder="contact@university.edu"
                                    required
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="marketing-field-error"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="marketing-field">
                                <label
                                    class="marketing-label"
                                    for="contact_person"
                                >
                                    Contact person *
                                </label>
                                <input
                                    id="contact_person"
                                    v-model="form.contact_person"
                                    type="text"
                                    class="marketing-input"
                                    :class="{
                                        'marketing-input--error':
                                            form.errors.contact_person,
                                    }"
                                    placeholder="e.g. Dr. John Smith"
                                    required
                                />
                                <p
                                    v-if="form.errors.contact_person"
                                    class="marketing-field-error"
                                >
                                    {{ form.errors.contact_person }}
                                </p>
                            </div>

                            <div
                                class="marketing-form-grid marketing-form-grid--two"
                            >
                                <div class="marketing-field">
                                    <label class="marketing-label" for="phone">
                                        Phone
                                        <span class="marketing-label-hint"
                                            >(optional)</span
                                        >
                                    </label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="marketing-input"
                                        :class="{
                                            'marketing-input--error':
                                                form.errors.phone,
                                        }"
                                        placeholder="+1 (555) 123-4567"
                                    />
                                    <p
                                        v-if="form.errors.phone"
                                        class="marketing-field-error"
                                    >
                                        {{ form.errors.phone }}
                                    </p>
                                </div>

                                <div class="marketing-field">
                                    <label
                                        class="marketing-label"
                                        for="address"
                                    >
                                        Address
                                        <span class="marketing-label-hint"
                                            >(optional)</span
                                        >
                                    </label>
                                    <textarea
                                        id="address"
                                        v-model="form.address"
                                        class="marketing-textarea"
                                        :class="{
                                            'marketing-textarea--error':
                                                form.errors.address,
                                        }"
                                        rows="3"
                                        placeholder="Street, city, state, ZIP"
                                    />
                                    <p
                                        v-if="form.errors.address"
                                        class="marketing-field-error"
                                    >
                                        {{ form.errors.address }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="marketing-form-actions">
                            <button
                                type="submit"
                                class="btn-primary marketing-btn-full"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing"
                                    >Submitting…</span
                                >
                                <span v-else>Submit registration</span>
                                <svg
                                    v-if="!form.processing"
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
                            </button>
                        </div>
                    </form>
                </div>

                <p class="marketing-form-footer-text">
                    Already have an account?
                    <Link href="/login">Sign in to your workspace</Link>
                </p>
            </div>
        </section>
    </MarketingLayout>
</template>
