<script setup lang="ts">
import { computed } from 'vue';

export type VivaSessionStatus =
    | 'idle'
    | 'speaking'
    | 'recording'
    | 'evaluating'
    | 'feedback';

const props = withDefaults(
    defineProps<{
        sessionTitle: string;
        live?: boolean;
        studentName: string;
        studentInitials: string;
        courseLabel: string;
        questionLabel: string;
        progressPercent: number;
        question?: string | null;
        status?: VivaSessionStatus;
        statusText?: string;
        transcript?: string;
        showTranscriptCursor?: boolean;
        waveformActive?: boolean;
        elapsed?: string;
        answeredCount?: number;
        showFeedback?: boolean;
        feedbackText?: string | null;
        showWindowChrome?: boolean;
    }>(),
    {
        live: false,
        question: null,
        status: 'idle',
        statusText: '',
        transcript: '',
        showTranscriptCursor: false,
        waveformActive: false,
        elapsed: '00:00',
        answeredCount: 0,
        showFeedback: false,
        feedbackText: null,
        showWindowChrome: true,
    },
);

const listeningLabel = computed(() => {
    if (props.statusText) return props.statusText;
    switch (props.status) {
        case 'speaking':
            return 'Listening to question…';
        case 'recording':
            return 'Student responding…';
        case 'evaluating':
            return 'Evaluating your answer…';
        case 'feedback':
            return 'Examiner feedback';
        default:
            return 'Ready for your answer';
    }
});

const showRecordingBadge = computed(
    () => props.live && props.status === 'recording',
);

const micActive = computed(
    () =>
        props.waveformActive ||
        props.status === 'recording' ||
        props.status === 'speaking',
);
</script>

<template>
    <div class="viva-session">
        <div class="viva-panel">
            <div v-if="showWindowChrome" class="panel-header">
                <div class="panel-dots">
                    <div class="panel-dot pd1" />
                    <div class="panel-dot pd2" />
                    <div class="panel-dot pd3" />
                </div>
                <span class="panel-title">{{ sessionTitle }}</span>
                <div
                    class="panel-badge"
                    :class="{ 'panel-badge--muted': !live }"
                >
                    {{ live ? 'Live' : 'Session' }}
                </div>
            </div>

            <div class="panel-body">
                <div class="panel-session-meta">
                    <div class="panel-student">
                        <div class="panel-student-avatar">
                            {{ studentInitials }}
                        </div>
                        <div>
                            <div class="panel-student-name">
                                {{ studentName }}
                            </div>
                            <div class="panel-student-course">
                                {{ courseLabel }}
                            </div>
                        </div>
                    </div>
                    <div class="panel-progress-wrap">
                        <div class="panel-progress-label">
                            <span>{{ questionLabel }}</span>
                            <span>{{ Math.round(progressPercent) }}%</span>
                        </div>
                        <div class="panel-progress-track">
                            <div
                                class="panel-progress-fill"
                                :style="{
                                    width: `${Math.min(100, Math.max(0, progressPercent))}%`,
                                }"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="showFeedback && feedbackText" class="panel-feedback">
                    <div class="panel-feedback-label">Feedback</div>
                    <p class="panel-feedback-text">{{ feedbackText }}</p>
                </div>

                <div v-if="question" class="viva-question">
                    <div class="q-label">
                        <svg
                            width="10"
                            height="10"
                            viewBox="0 0 10 10"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="5"
                                cy="5"
                                r="4"
                                stroke="currentColor"
                                stroke-width="1"
                            />
                            <path
                                d="M5 3V5.5L6.5 7"
                                stroke="currentColor"
                                stroke-width="1"
                                stroke-linecap="round"
                            />
                        </svg>
                        AI-generated question
                    </div>
                    <div class="q-text">{{ question }}</div>
                </div>

                <div class="panel-listening">
                    <div class="panel-listening-left">
                        <span
                            class="panel-mic-ring"
                            :class="{ 'panel-mic-ring--active': micActive }"
                            aria-hidden="true"
                        >
                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <path
                                    d="M19 10v2a7 7 0 0 1-14 0v-2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </span>
                        <span class="panel-listening-text">{{
                            listeningLabel
                        }}</span>
                    </div>
                    <span
                        v-if="showRecordingBadge"
                        class="panel-listening-badge"
                        >Recording</span
                    >
                </div>

                <div
                    class="waveform"
                    :class="waveformActive ? 'waveform--active' : 'waveform--idle'"
                    aria-hidden="true"
                >
                    <div v-for="n in 28" :key="n" class="wave-bar" />
                </div>

                <div class="transcript-box">
                    <div class="transcript-line">
                        <span class="tl-speaker">STU›</span>
                        <span v-if="transcript">{{ transcript }}</span>
                        <span v-else class="transcript-placeholder"
                            >Your answer will appear here as you speak…</span
                        >
                        <span
                            v-if="showTranscriptCursor && transcript"
                            class="typing-cursor"
                        />
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <div class="panel-footer-stats">
                    <div class="panel-footer-chip">
                        Answered: {{ answeredCount }}
                    </div>
                </div>
                <div class="time-pill">{{ elapsed }} elapsed</div>
                <div v-if="$slots.footer" class="panel-footer-actions">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>
