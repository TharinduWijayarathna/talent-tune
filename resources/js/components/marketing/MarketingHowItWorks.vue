<script setup lang="ts">
import VivaSessionPanel from '@/components/viva/VivaSessionPanel.vue';
import { useHowItWorksDemo } from '@/composables/useHowItWorksDemo';
import { onUnmounted, ref, watch } from 'vue';

const isOpen = ref(false);

const steps = [
    {
        number: '01',
        title: 'Lecturer Creates the Viva',
        desc: 'Define exam parameters, upload reference materials, and set grading rubrics.',
    },
    {
        number: '02',
        title: 'Student Uploads Their Work',
        desc: 'Students submit project files. AI reads and maps the document to exam criteria.',
    },
    {
        number: '03',
        title: 'AI Voice Assistant Conducts the Exam',
        desc: 'Tailored questions, real-time listening, and adaptive follow-ups.',
    },
    {
        number: '04',
        title: 'Review & Grade',
        desc: 'Transcripts, recordings, and AI-suggested scores for lecturer validation.',
    },
];

const demoTranscript =
    'In my implementation, I used a write-through cache with optimistic locking on concurrent writes…';

const {
    activeStep,
    isPaused,
    isPlaying,
    slideCount,
    startAutoplay,
    stopAutoplay,
    goToStep,
    nextStep,
    prevStep,
} = useHowItWorksDemo();

function open() {
    isOpen.value = true;
    goToStep(0);
    startAutoplay();
}

function close() {
    isOpen.value = false;
    stopAutoplay();
}

function onStepClick(index: number) {
    goToStep(index);
}

function onKeydown(event: KeyboardEvent) {
    if (!isOpen.value) return;
    if (event.key === 'Escape') {
        close();
    }
    if (event.key === 'ArrowRight') {
        nextStep();
    }
    if (event.key === 'ArrowLeft') {
        prevStep();
    }
}

watch(isOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
        window.addEventListener('keydown', onKeydown);
    } else {
        window.removeEventListener('keydown', onKeydown);
    }
});

defineExpose({ open, close });

onUnmounted(() => {
    stopAutoplay();
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="how-modal-fade">
            <div
                v-if="isOpen"
                class="how-modal-backdrop marketing-how-modal"
                role="presentation"
                @click.self="close"
            >
                <div
                    class="how-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="how-modal-title"
                >
                    <div class="how-modal-header">
                        <div>
                            <div class="how-modal-tag">Product demo</div>
                            <h2 id="how-modal-title" class="how-modal-title">
                                How Viva Suite works
                            </h2>
                        </div>
                        <button
                            type="button"
                            class="how-modal-close"
                            aria-label="Close demo"
                            @click="close"
                        >
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 5l10 10M15 5L5 15"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    </div>

                    <div
                        class="how-showcase"
                        @mouseenter="isPaused = true"
                        @mouseleave="isPaused = false"
                    >
                        <div class="how-steps">
                            <button
                                v-for="(step, idx) in steps"
                                :key="step.number"
                                type="button"
                                class="how-step"
                                :class="{ active: activeStep === idx }"
                                :aria-current="
                                    activeStep === idx ? 'step' : undefined
                                "
                                @click="onStepClick(idx)"
                            >
                                <div class="how-step-top">
                                    <span class="how-step-number">{{
                                        step.number
                                    }}</span>
                                    <span
                                        v-if="activeStep === idx"
                                        class="how-step-live"
                                        aria-hidden="true"
                                        >Playing</span
                                    >
                                </div>
                                <div class="how-step-title">
                                    {{ step.title }}
                                </div>
                                <div class="how-step-desc">{{ step.desc }}</div>
                                <div
                                    class="how-step-progress"
                                    :class="{
                                        active:
                                            activeStep === idx && !isPaused,
                                    }"
                                />
                            </button>
                        </div>

                        <div class="how-demo-wrap">
                            <div class="how-demo-toolbar">
                                <div class="how-demo-dots" aria-hidden="true">
                                    <span class="pd1" />
                                    <span class="pd2" />
                                    <span class="pd3" />
                                </div>
                                <span class="how-demo-title"
                                    >viva_suite_demo</span
                                >
                                <span class="how-demo-badge">Preview</span>
                            </div>

                            <div class="how-demo-stage">
                                <Transition name="how-slide" mode="out-in">
                                    <div
                                        v-if="activeStep === 0"
                                        key="create"
                                        class="how-slide how-slide--app"
                                    >
                                        <div class="how-app-topbar">
                                            <span class="how-app-screen-title"
                                                >Create Viva Session</span
                                            >
                                            <span class="ws-add-btn"
                                                >Save draft</span
                                            >
                                        </div>
                                        <div class="how-app-body">
                                            <div class="how-form-field">
                                                <label>Course / Module</label>
                                                <div class="how-form-value">
                                                    CS301 — Systems Design
                                                </div>
                                            </div>
                                            <div class="how-form-field">
                                                <label
                                                    >Duration &amp;
                                                    questions</label
                                                >
                                                <div class="how-form-value">
                                                    45 min · 8 questions
                                                </div>
                                            </div>
                                            <div class="how-form-field">
                                                <label
                                                    >Reference materials</label
                                                >
                                                <div class="how-form-files">
                                                    <span class="how-file-chip"
                                                        >rubric.pdf</span
                                                    >
                                                    <span class="how-file-chip"
                                                        >brief.docx</span
                                                    >
                                                </div>
                                            </div>
                                            <div class="how-form-field">
                                                <label>Grading rubric</label>
                                                <div class="how-form-rubric">
                                                    Technical depth, design
                                                    rationale, edge cases…
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-else-if="activeStep === 1"
                                        key="upload"
                                        class="how-slide how-slide--app"
                                    >
                                        <div class="how-app-topbar">
                                            <span class="how-app-screen-title"
                                                >Submit Your Work</span
                                            >
                                            <span class="how-app-meta"
                                                >CS301 · Viva #12</span
                                            >
                                        </div>
                                        <div
                                            class="how-app-body how-app-body--center"
                                        >
                                            <div class="how-upload-zone">
                                                <div
                                                    class="how-upload-icon"
                                                    aria-hidden="true"
                                                >
                                                    📄
                                                </div>
                                                <div class="how-upload-title">
                                                    dissertation_final.pdf
                                                </div>
                                                <div class="how-upload-sub">
                                                    2.4 MB · Uploaded
                                                    successfully
                                                </div>
                                                <div class="how-upload-bar">
                                                    <div
                                                        class="how-upload-bar-fill"
                                                        style="width: 100%"
                                                    />
                                                </div>
                                            </div>
                                            <div class="how-ai-scan">
                                                <span class="how-ai-scan-dot" />
                                                AI document analysis complete —
                                                14 topics mapped
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-else-if="activeStep === 2"
                                        key="live"
                                        class="how-slide how-slide--viva"
                                    >
                                        <VivaSessionPanel
                                            session-title="viva_session_cs301"
                                            :live="true"
                                            student-name="T. Siriwardena"
                                            student-initials="TS"
                                            course-label="CS301 — Systems Design"
                                            question-label="Question 3 of 8"
                                            :progress-percent="37.5"
                                            question="Explain how your proposed caching strategy handles concurrent write operations under high load."
                                            status="recording"
                                            :transcript="demoTranscript"
                                            :show-transcript-cursor="true"
                                            :waveform-active="true"
                                            elapsed="04:32"
                                            :answered-count="2"
                                        />
                                    </div>

                                    <div
                                        v-else
                                        key="review"
                                        class="how-slide how-slide--app"
                                    >
                                        <div class="how-app-topbar">
                                            <span class="how-app-screen-title"
                                                >Review &amp; Grade</span
                                            >
                                            <span class="how-app-meta"
                                                >T. Siriwardena · CS301</span
                                            >
                                        </div>
                                        <div class="how-app-body">
                                            <div class="how-review-summary">
                                                <div class="how-review-score">
                                                    <span
                                                        class="how-review-score-val"
                                                        >82</span
                                                    >
                                                    <span
                                                        class="how-review-score-lbl"
                                                        >AI suggested</span
                                                    >
                                                </div>
                                                <div class="how-review-meta">
                                                    <div>
                                                        8 questions answered
                                                    </div>
                                                    <div>
                                                        42:18 total duration
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="how-review-list">
                                                <div class="how-review-row">
                                                    <span
                                                        >Q3 — Caching
                                                        strategy</span
                                                    >
                                                    <span
                                                        class="how-review-chip good"
                                                        >Strong</span
                                                    >
                                                </div>
                                                <div class="how-review-row">
                                                    <span>Q5 — Trade-offs</span>
                                                    <span
                                                        class="how-review-chip mid"
                                                        >Partial</span
                                                    >
                                                </div>
                                                <div class="how-review-row">
                                                    <span
                                                        >Q7 — Failure modes</span
                                                    >
                                                    <span
                                                        class="how-review-chip good"
                                                        >Strong</span
                                                    >
                                                </div>
                                            </div>
                                            <div class="how-review-actions">
                                                <span
                                                    class="how-review-btn primary"
                                                    >Approve grade</span
                                                >
                                                <span class="how-review-btn"
                                                    >Listen to recording</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <div class="how-demo-controls">
                                <button
                                    type="button"
                                    class="how-nav-btn"
                                    aria-label="Previous step"
                                    @click="prevStep"
                                >
                                    ←
                                </button>
                                <div
                                    class="how-dots"
                                    role="tablist"
                                    aria-label="Demo steps"
                                >
                                    <button
                                        v-for="idx in slideCount"
                                        :key="idx"
                                        type="button"
                                        role="tab"
                                        class="how-dot"
                                        :class="{
                                            active: activeStep === idx - 1,
                                        }"
                                        :aria-selected="activeStep === idx - 1"
                                        :aria-label="`Step ${idx}`"
                                        @click="goToStep(idx - 1)"
                                    />
                                </div>
                                <button
                                    type="button"
                                    class="how-nav-btn"
                                    aria-label="Next step"
                                    @click="nextStep"
                                >
                                    →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
