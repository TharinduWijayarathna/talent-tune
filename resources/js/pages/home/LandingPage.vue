<script setup lang="ts">
import HeroVivaPanel from '@/components/marketing/HeroVivaPanel.vue';
import MarketingFooter from '@/components/marketing/MarketingFooter.vue';
import MarketingHowItWorks from '@/components/marketing/MarketingHowItWorks.vue';
import MarketingNav from '@/components/marketing/MarketingNav.vue';
import MarketingPricing from '@/components/marketing/MarketingPricing.vue';
import MarketingWorkspace from '@/components/marketing/MarketingWorkspace.vue';
import { useMarketingHome } from '@/composables/useMarketingHome';
import { registerInstitution } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { provide, ref } from 'vue';

import '../../../css/marketing-home.css';

defineProps<{
    canRegister?: boolean;
}>();

const registerUrl = registerInstitution.url();
const { typingText, progressScale } = useMarketingHome();
const howItWorksRef = ref<InstanceType<typeof MarketingHowItWorks> | null>(
    null,
);

function onSeeHowItWorks() {
    howItWorksRef.value?.open();
}

provide('openHowItWorksDemo', onSeeHowItWorks);
</script>

<template>
    <Head title="Viva Suite — Intelligent Oral Examination Platform">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Syne:wght@400;500;600;700;800&display=swap"
        />
    </Head>

    <div class="marketing-page">
        <div
            class="progress-bar"
            :style="{ transform: `scaleX(${progressScale})` }"
        />
        <div class="grid-bg" />

        <MarketingNav />

        <section class="hero">
            <div class="hero-glow" />
            <div class="hero-glow2" />
            <div class="orb orb1" />
            <div class="orb orb2" />
            <div class="orb orb3" />

            <div class="hero-layout">
                <div class="hero-content">
                    <div class="hero-tag">
                        <span class="hero-tag-dot" />
                        AI-Powered Oral Examination
                    </div>

                    <h1 class="hero-heading">
                        Viva exams,<br />
                        <em>reimagined</em>
                        <span class="outlined">for the AI era.</span>
                    </h1>

                    <p class="hero-sub">
                        Viva Suite lets institutions conduct intelligent,
                        voice-driven oral examinations online — powered by
                        document analysis, adaptive questioning, and real-time
                        transcription.
                    </p>

                    <div class="hero-actions">
                        <Link :href="registerUrl" class="btn-primary">
                            Register Your Institution
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
                        </Link>
                        <button
                            type="button"
                            class="btn-secondary"
                            @click="onSeeHowItWorks"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                            >
                                <circle
                                    cx="8"
                                    cy="8"
                                    r="6.5"
                                    stroke="currentColor"
                                    stroke-width="1.2"
                                />
                                <path
                                    d="M6.5 5.5L10.5 8L6.5 10.5V5.5Z"
                                    fill="currentColor"
                                />
                            </svg>
                            See how it works
                        </button>
                    </div>
                </div>

                <HeroVivaPanel :typing-text="typingText" />
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">98<span>%</span></div>
                    <div class="stat-label">Transcription Accuracy</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">3<span>x</span></div>
                    <div class="stat-label">Faster than manual vivas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500<span>+</span></div>
                    <div class="stat-label">Institutions onboarded</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24<span>/7</span></div>
                    <div class="stat-label">Always available</div>
                </div>
            </div>
        </section>

        <div class="tech-strip">
            <div class="tech-label">Powered by</div>
            <div class="tech-items">
                <div class="tech-item">
                    <div class="tech-dot" style="background: #3b82f6" />
                    Large Language Models
                </div>
                <div class="tech-item">
                    <div class="tech-dot" style="background: #60a5fa" />
                    Speech-to-Text AI
                </div>
                <div class="tech-item">
                    <div class="tech-dot" style="background: #f0c060" />
                    Document Intelligence
                </div>
                <div class="tech-item">
                    <div class="tech-dot" style="background: #a78bfa" />
                    Adaptive Questioning
                </div>
                <div class="tech-item">
                    <div class="tech-dot" style="background: #34d399" />
                    Real-time Transcription
                </div>
            </div>
        </div>

        <MarketingHowItWorks ref="howItWorksRef" />

        <section id="how">
            <div class="section-tag">Process</div>
            <h2 class="section-heading reveal">
                From setup to <em>results</em> in minutes.
            </h2>
            <p class="section-sub reveal">
                Four steps from viva creation to graded results — watch the
                interactive demo or read the workflow below.
            </p>
            <div class="how-cta reveal reveal-delay-1">
                <button
                    type="button"
                    class="btn-secondary"
                    @click="onSeeHowItWorks"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                        aria-hidden="true"
                    >
                        <circle
                            cx="8"
                            cy="8"
                            r="6.5"
                            stroke="currentColor"
                            stroke-width="1.2"
                        />
                        <path
                            d="M6.5 5.5L10.5 8L6.5 10.5V5.5Z"
                            fill="currentColor"
                        />
                    </svg>
                    Watch interactive demo
                </button>
            </div>
            <div class="how-grid">
                <div class="step-card reveal reveal-delay-1">
                    <div class="step-number">01</div>
                    <div class="step-title">Lecturer Creates the Viva</div>
                    <div class="step-desc">
                        Define exam parameters, upload reference materials, and
                        set grading rubrics. The platform ingests your
                        instructions and generates a structured exam framework.
                    </div>
                </div>
                <div class="step-card reveal reveal-delay-2">
                    <div class="step-number">02</div>
                    <div class="step-title">Student Uploads Their Work</div>
                    <div class="step-desc">
                        Students submit their project files, reports, or
                        dissertations. Our AI reads and deeply understands the
                        document, mapping it against the exam criteria.
                    </div>
                </div>
                <div class="step-card reveal reveal-delay-3">
                    <div class="step-number">03</div>
                    <div class="step-title">
                        AI Voice Assistant Conducts the Exam
                    </div>
                    <div class="step-desc">
                        The intelligent voice assistant asks tailored,
                        document-specific questions. It listens, understands, and
                        dynamically follows up based on student responses.
                    </div>
                </div>
                <div class="step-card reveal reveal-delay-4">
                    <div class="step-number">04</div>
                    <div class="step-title">Review &amp; Grade</div>
                    <div class="step-desc">
                        All responses are transcribed and stored alongside voice
                        recordings. Lecturers receive a full review dashboard with
                        AI-suggested scores for manual validation.
                    </div>
                </div>
            </div>
        </section>

        <section id="features" style="padding-top: 0">
            <div class="section-tag">Capabilities</div>
            <h2 class="section-heading reveal">
                Everything you need for <em>serious</em> assessment.
            </h2>
            <div class="features-layout">
                <div
                    v-for="(f, i) in [
                        {
                            title: 'Document-Aware Questioning',
                            desc: 'AI reads the student\'s uploaded document and generates targeted, relevant questions that probe actual understanding — not generic topics.',
                        },
                        {
                            title: 'Live Voice Interaction',
                            desc: 'Natural, conversational voice assistant speaks and listens in real-time. Students answer verbally — just like a real viva, but scalable.',
                        },
                        {
                            title: 'Full Transcription Archive',
                            desc: 'Every answer captured as both text and audio file. Reviewers get a timestamped, searchable transcript alongside the raw recording.',
                        },
                        {
                            title: 'Custom Rubric Builder',
                            desc: 'Lecturers define marking criteria in natural language. The AI scores responses against your rubric and explains the reasoning.',
                        },
                        {
                            title: 'Adaptive Follow-up Logic',
                            desc: 'If a student\'s answer is vague or incorrect, the AI probes deeper with intelligent follow-up questions — like a real examiner.',
                        },
                        {
                            title: 'Anti-Cheating Safeguards',
                            desc: 'Voice biometric session locking, randomised question order, and AI detection of rehearsed responses keeps integrity intact.',
                        },
                    ]"
                    :key="i"
                    class="feature-card reveal"
                    :class="`reveal-delay-${(i % 3) + 1}`"
                >
                    <div class="feature-title">{{ f.title }}</div>
                    <div class="feature-desc">{{ f.desc }}</div>
                </div>
            </div>
        </section>

        <MarketingWorkspace />

        <section id="roles" class="roles-section">
            <div class="section-tag">For Everyone</div>
            <h2 class="section-heading reveal">
                Three roles. One <em>seamless</em> platform.
            </h2>
            <div class="roles-grid-three">
                <div class="role-card institution reveal reveal-delay-1">
                    <div class="role-header">
                        <div class="role-avatar inst-av">🏛️</div>
                        <div>
                            <div class="role-name">Institution Admin</div>
                            <div class="role-subtitle">
                                Owns and governs the workspace
                            </div>
                        </div>
                    </div>
                    <div class="role-steps">
                        <div class="role-step">
                            <div class="rs-num">1</div>
                            <div class="rs-content">
                                <div class="rs-title">
                                    Register &amp; Set Up the Workspace
                                </div>
                                <div class="rs-desc">
                                    Create your isolated environment, configure
                                    branding, and set a custom subdomain.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="role-card lecturer reveal reveal-delay-2">
                    <div class="role-header">
                        <div class="role-avatar lect-av">🎓</div>
                        <div>
                            <div class="role-name">Lecturer</div>
                            <div class="role-subtitle">
                                Designs and reviews viva exams
                            </div>
                        </div>
                    </div>
                    <div class="role-steps">
                        <div class="role-step">
                            <div class="rs-num">1</div>
                            <div class="rs-content">
                                <div class="rs-title">
                                    Create &amp; Configure the Viva
                                </div>
                                <div class="rs-desc">
                                    Set topic, difficulty, duration, and grading
                                    rubric with reference materials for the AI.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="role-card student reveal reveal-delay-3">
                    <div class="role-header">
                        <div class="role-avatar stu-av">📄</div>
                        <div>
                            <div class="role-name">Student</div>
                            <div class="role-subtitle">
                                Takes the AI-conducted viva exam
                            </div>
                        </div>
                    </div>
                    <div class="role-steps">
                        <div class="role-step">
                            <div class="rs-num">1</div>
                            <div class="rs-content">
                                <div class="rs-title">Answer by Voice</div>
                                <div class="rs-desc">
                                    Respond naturally to the AI examiner. Answers
                                    are captured as audio and auto-transcribed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <MarketingPricing />

        <section class="cta-section">
            <div class="cta-glow" />
            <div class="section-tag reveal" style="justify-content: center">
                Ready to begin
            </div>
            <h2 class="section-heading reveal">
                The future of <em>oral assessment</em> is already here.
            </h2>
            <p class="cta-sub reveal">
                Join institutions running scalable, intelligent viva examinations
                with Viva Suite.
            </p>
            <div class="cta-actions reveal">
                <Link
                    :href="registerUrl"
                    class="btn-primary"
                    style="font-size: 15px; padding: 18px 44px"
                >
                    Register Your Institution
                </Link>
                <a
                    href="#pricing"
                    class="btn-secondary"
                    style="font-size: 15px; padding: 18px 44px"
                >
                    View pricing
                </a>
            </div>
        </section>

        <MarketingFooter />
    </div>
</template>
