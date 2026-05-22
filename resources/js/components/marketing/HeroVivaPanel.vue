<script setup lang="ts">
defineProps<{
    typingText: string;
}>();
</script>

<template>
    <div class="hero-visual">
        <div class="viva-panel">
            <div class="panel-header">
                <div class="panel-dots">
                    <div class="panel-dot pd1" />
                    <div class="panel-dot pd2" />
                    <div class="panel-dot pd3" />
                </div>
                <span class="panel-title">viva_session_cs301</span>
                <div class="panel-badge">Live</div>
            </div>

            <div class="panel-body">
                <div class="panel-session-meta">
                    <div class="panel-student">
                        <div class="panel-student-avatar">TS</div>
                        <div>
                            <div class="panel-student-name">
                                T. Siriwardena
                            </div>
                            <div class="panel-student-course">
                                CS301 — Systems Design
                            </div>
                        </div>
                    </div>
                    <div class="panel-progress-wrap">
                        <div class="panel-progress-label">
                            <span>Question 3 of 8</span>
                            <span>38%</span>
                        </div>
                        <div class="panel-progress-track">
                            <div
                                class="panel-progress-fill"
                                style="width: 37.5%"
                            />
                        </div>
                    </div>
                </div>

                <div class="viva-question">
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
                    <div class="q-text">
                        "Explain how your proposed caching strategy handles
                        concurrent write operations under high load."
                    </div>
                </div>

                <div class="panel-listening">
                    <div class="panel-listening-left">
                        <span class="panel-mic-ring" aria-hidden="true">
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
                        <span class="panel-listening-text"
                            >Student responding…</span
                        >
                    </div>
                    <span class="panel-listening-badge">Recording</span>
                </div>

                <div class="waveform" aria-hidden="true">
                    <div v-for="n in 28" :key="n" class="wave-bar" />
                </div>

                <div class="transcript-box">
                    <div class="transcript-line">
                        <span class="tl-speaker">STU›</span>
                        <span class="typing-text">{{ typingText }}</span
                        ><span class="typing-cursor" />
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <div class="panel-footer-stats">
                    <div class="score-pill">Confidence: 87%</div>
                    <div class="panel-footer-chip">Follow-ups: 2</div>
                </div>
                <div class="time-pill">04:32 elapsed</div>
            </div>
        </div>
    </div>
</template>
