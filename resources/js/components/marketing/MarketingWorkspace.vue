<script setup lang="ts">
import { pricingPlans } from '@/data/pricingPlans';
import { computed, ref } from 'vue';

const activePanel = ref(0);

const wsBillingPlan = computed(
    () => pricingPlans.find((p) => p.highlighted) ?? pricingPlans[1],
);

const features = [
    {
        title: 'Isolated Workspace',
        desc: "Your institution's vivas, members, and data are completely separate. No cross-org visibility, ever.",
    },
    {
        title: 'Member & Role Management',
        desc: 'Invite admins, lecturers, and students. Assign roles that control exactly what each person can see and do.',
    },
    {
        title: 'Workspace Analytics Dashboard',
        desc: 'Track exam volumes, pass rates, average scores, and AI confidence metrics across all viva sessions.',
    },
    {
        title: 'Custom Branding & Domain',
        desc: "Add your institution's logo, colours, and a custom subdomain so the platform feels native to your brand.",
    },
    {
        title: 'Subscription & Billing Control',
        desc: 'Institution admins manage plan upgrades, seat limits, and invoicing — all from one central dashboard.',
    },
];

const chartMonths = [
    { label: 'Jan', height: '55%', active: false },
    { label: 'Feb', height: '72%', active: false },
    { label: 'Mar', height: '48%', active: false },
    { label: 'Apr', height: '88%', active: false },
    { label: 'May', height: '95%', active: true },
];

const recentExams = [
    { name: 'CS301 — Systems Design', status: 'live', dot: '#28c840' },
    { name: 'EE204 — Circuit Theory', status: 'pending', dot: '#f0c060' },
    { name: 'IT101 — Web Dev Fundamentals', status: 'done', dot: '#60a5fa' },
];

const members = [
    {
        initials: 'KA',
        name: 'Dr. K. Abeysekara',
        email: 'k.abeysekara@university.edu',
        role: 'admin',
        avatarStyle: 'background:rgba(59,130,246,0.2);color:#60a5fa',
    },
    {
        initials: 'SP',
        name: 'Ms. S. Perera',
        email: 's.perera@university.edu',
        role: 'lecturer',
        avatarStyle: 'background:rgba(240,192,96,0.15);color:#f0c060',
    },
    {
        initials: 'RJ',
        name: 'Mr. R. Jayasinghe',
        email: 'r.jayasinghe@university.edu',
        role: 'lecturer',
        avatarStyle: 'background:rgba(167,139,250,0.15);color:#a78bfa',
    },
    {
        initials: 'TS',
        name: 'T. Siriwardena',
        email: '20cs087@stu.university.edu',
        role: 'student',
        avatarStyle: 'background:rgba(52,211,153,0.12);color:#34d399',
    },
];
</script>

<template>
    <section id="workspace" class="workspace-section">
        <div class="section-tag">Institution Workspace</div>
        <h2 class="section-heading reveal">
            Your institution gets its <em>own</em> dedicated space.
        </h2>
        <p class="section-sub reveal">
            Every registered institution operates inside a fully isolated,
            branded workspace — with its own members, exams, settings, and
            analytics. Nothing bleeds across organisations.
        </p>

        <div class="workspace-showcase">
            <div class="ws-features reveal reveal-delay-1">
                <button
                    v-for="(item, idx) in features"
                    :key="idx"
                    type="button"
                    class="ws-feature"
                    :class="{ active: activePanel === idx }"
                    @click="activePanel = idx"
                >
                    <div class="ws-feature-icon" aria-hidden="true">
                        <svg
                            v-if="idx === 0"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <rect
                                x="2"
                                y="2"
                                width="16"
                                height="16"
                                rx="3"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <path
                                d="M6 7h8M6 10h5M6 13h3"
                                stroke="currentColor"
                                stroke-width="1.3"
                                stroke-linecap="round"
                            />
                        </svg>
                        <svg
                            v-else-if="idx === 1"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <circle
                                cx="7"
                                cy="6"
                                r="3"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <circle
                                cx="13"
                                cy="6"
                                r="3"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <path
                                d="M1 17c0-3.314 2.686-6 6-6M13 11c3.314 0 6 2.686 6 6"
                                stroke="currentColor"
                                stroke-width="1.3"
                                stroke-linecap="round"
                            />
                        </svg>
                        <svg
                            v-else-if="idx === 2"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M2 14l4-4 3 3 4-5 5 6"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <rect
                                x="2"
                                y="2"
                                width="16"
                                height="16"
                                rx="2"
                                stroke="currentColor"
                                stroke-width="1.3"
                            />
                        </svg>
                        <svg
                            v-else-if="idx === 3"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <circle
                                cx="10"
                                cy="10"
                                r="8"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <path
                                d="M10 6v4l3 3"
                                stroke="currentColor"
                                stroke-width="1.3"
                                stroke-linecap="round"
                            />
                        </svg>
                        <svg
                            v-else
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M10 2l2.39 4.84L18 7.64l-4 3.9.94 5.46L10 14.27 5.06 17l.94-5.46L2 7.64l5.61-.8L10 2z"
                                stroke="currentColor"
                                stroke-width="1.4"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <div class="ws-feature-content">
                        <div class="ws-feature-title">{{ item.title }}</div>
                        <div class="ws-feature-desc">{{ item.desc }}</div>
                    </div>
                    <div class="ws-feature-arrow">→</div>
                </button>
            </div>

            <div class="ws-panel-wrap reveal reveal-delay-2">
                <div class="ws-panel">
                    <!-- Overview -->
                    <div class="ws-screen" :class="{ active: activePanel === 0 }">
                        <div class="ws-topbar">
                            <div class="ws-org-badge">
                                <div class="ws-org-logo">U</div>
                                <div>
                                    <div class="ws-org-name">
                                        University Workspace
                                    </div>
                                    <div class="ws-org-plan">
                                        Professional · 48 members
                                    </div>
                                </div>
                            </div>
                            <div class="ws-topbar-actions">
                                <div class="ws-notif" aria-hidden="true">
                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 14 14"
                                        fill="none"
                                    >
                                        <path
                                            d="M7 1a4 4 0 0 1 4 4v2l1.5 2.5H1.5L3 7V5a4 4 0 0 1 4-4z"
                                            stroke="currentColor"
                                            stroke-width="1.2"
                                        />
                                        <path
                                            d="M5.5 10.5a1.5 1.5 0 0 0 3 0"
                                            stroke="currentColor"
                                            stroke-width="1.2"
                                        />
                                    </svg>
                                </div>
                                <div class="ws-avatar-sm">KA</div>
                            </div>
                        </div>
                        <div class="ws-stat-row">
                            <div class="ws-stat-box">
                                <div class="ws-stat-val">24</div>
                                <div class="ws-stat-lbl">Active Vivas</div>
                            </div>
                            <div class="ws-stat-box">
                                <div class="ws-stat-val">312</div>
                                <div class="ws-stat-lbl">Students</div>
                            </div>
                            <div class="ws-stat-box">
                                <div class="ws-stat-val">91%</div>
                                <div class="ws-stat-lbl">Avg. Score</div>
                            </div>
                        </div>
                        <div class="ws-recent-header">Recent Viva Sessions</div>
                        <div class="ws-exam-list">
                            <div
                                v-for="exam in recentExams"
                                :key="exam.name"
                                class="ws-exam-row"
                            >
                                <div
                                    class="ws-exam-dot"
                                    :style="{ background: exam.dot }"
                                />
                                <div class="ws-exam-name">{{ exam.name }}</div>
                                <div
                                    class="ws-exam-badge"
                                    :class="exam.status"
                                >
                                    {{
                                        exam.status === 'live'
                                            ? 'Live'
                                            : exam.status === 'pending'
                                              ? 'Reviewing'
                                              : 'Completed'
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Members -->
                    <div class="ws-screen" :class="{ active: activePanel === 1 }">
                        <div class="ws-topbar">
                            <div class="ws-screen-title">Members &amp; Roles</div>
                            <div class="ws-add-btn">+ Invite Member</div>
                        </div>
                        <div class="ws-member-list">
                            <div
                                v-for="member in members"
                                :key="member.email"
                                class="ws-member-row"
                            >
                                <div
                                    class="ws-avatar-sm"
                                    :style="member.avatarStyle"
                                >
                                    {{ member.initials }}
                                </div>
                                <div class="ws-member-info">
                                    <div class="ws-member-name">
                                        {{ member.name }}
                                    </div>
                                    <div class="ws-member-email">
                                        {{ member.email }}
                                    </div>
                                </div>
                                <div
                                    class="ws-role-chip"
                                    :class="member.role"
                                >
                                    {{
                                        member.role === 'admin'
                                            ? 'Admin'
                                            : member.role === 'lecturer'
                                              ? 'Lecturer'
                                              : 'Student'
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div class="ws-screen" :class="{ active: activePanel === 2 }">
                        <div class="ws-topbar">
                            <div class="ws-screen-title">Analytics Overview</div>
                            <div class="ws-period-badge">Last 30 days</div>
                        </div>
                        <div class="ws-chart-area">
                            <div class="ws-chart-bars">
                                <div
                                    v-for="month in chartMonths"
                                    :key="month.label"
                                    class="ws-bar-wrap"
                                >
                                    <div
                                        class="ws-bar"
                                        :class="{ active: month.active }"
                                        :style="{ '--h': month.height }"
                                    />
                                    <div class="ws-bar-lbl">
                                        {{ month.label }}
                                    </div>
                                </div>
                            </div>
                            <div class="ws-chart-label">
                                Exams Conducted per Month
                            </div>
                        </div>
                        <div class="ws-metric-row">
                            <div class="ws-metric">
                                <div
                                    class="ws-metric-val"
                                    style="color: #28c840"
                                >
                                    87%
                                </div>
                                <div class="ws-metric-lbl">Pass Rate</div>
                            </div>
                            <div class="ws-metric">
                                <div
                                    class="ws-metric-val"
                                    style="color: #60a5fa"
                                >
                                    4.2m
                                </div>
                                <div class="ws-metric-lbl">Avg. Duration</div>
                            </div>
                            <div class="ws-metric">
                                <div
                                    class="ws-metric-val"
                                    style="color: #f0c060"
                                >
                                    98%
                                </div>
                                <div class="ws-metric-lbl">
                                    Transcript Acc.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branding -->
                    <div class="ws-screen" :class="{ active: activePanel === 3 }">
                        <div class="ws-topbar">
                            <div class="ws-screen-title">
                                Workspace Branding
                            </div>
                        </div>
                        <div class="ws-branding-preview">
                            <div class="ws-brand-logo-area">
                                <div class="ws-brand-logo-box">
                                    <span style="font-size: 22px">🏛️</span>
                                </div>
                                <div>
                                    <div class="ws-brand-preview-name">
                                        University Workspace
                                    </div>
                                    <div class="ws-brand-preview-domain">
                                        your-uni.vivasuite.com
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ws-brand-fields">
                            <div class="ws-brand-field">
                                <div class="ws-brand-field-label">
                                    Primary Colour
                                </div>
                                <div class="ws-brand-field-val">
                                    <div
                                        class="ws-color-dot"
                                        style="background: #1e40af"
                                    />
                                    #1E40AF
                                </div>
                            </div>
                            <div class="ws-brand-field">
                                <div class="ws-brand-field-label">
                                    Custom Subdomain
                                </div>
                                <div class="ws-brand-field-val mono">
                                    your-uni.vivasuite.com
                                </div>
                            </div>
                            <div class="ws-brand-field">
                                <div class="ws-brand-field-label">
                                    Email Domain Whitelist
                                </div>
                                <div class="ws-brand-field-val mono">
                                    @university.edu
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="ws-screen" :class="{ active: activePanel === 4 }">
                        <div class="ws-topbar">
                            <div class="ws-screen-title">Plan &amp; Billing</div>
                        </div>
                        <div class="ws-plan-card">
                            <div class="ws-plan-badge">
                                {{ wsBillingPlan.name }}
                            </div>
                            <div class="ws-plan-price">
                                ${{ wsBillingPlan.price }}<span>/mo</span>
                            </div>
                            <div class="ws-plan-desc">
                                {{ wsBillingPlan.vivasPerMonth }} vivas per month
                                · up to {{ wsBillingPlan.studentsPerViva }}
                                students per viva
                            </div>
                        </div>
                        <div class="ws-billing-rows">
                            <div class="ws-billing-row">
                                <span>Vivas this month</span>
                                <span class="ws-billing-val"
                                    >12 /
                                    {{ wsBillingPlan.vivasPerMonth }}</span
                                >
                            </div>
                            <div class="ws-billing-row">
                                <span>Max students per viva</span>
                                <span class="ws-billing-val">{{
                                    wsBillingPlan.studentsPerViva
                                }}</span>
                            </div>
                            <div class="ws-billing-row">
                                <span>Next invoice</span>
                                <span class="ws-billing-val">Jun 1, 2026</span>
                            </div>
                            <div class="ws-billing-row">
                                <span>Status</span>
                                <span class="ws-billing-status">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
