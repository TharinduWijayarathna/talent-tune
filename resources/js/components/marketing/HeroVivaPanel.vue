<script setup lang="ts">
import VivaSessionPanel from '@/components/viva/VivaSessionPanel.vue';

defineProps<{
    typingText: string;
}>();
</script>

<template>
    <div class="hero-visual">
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
            :transcript="typingText"
            :show-transcript-cursor="true"
            :waveform-active="true"
            elapsed="04:32"
            :answered-count="2"
        />
    </div>
</template>
