import { onUnmounted, ref, watch } from 'vue';

const SLIDE_COUNT = 4;
const AUTO_ADVANCE_MS = 5000;

export function useHowItWorksDemo() {
    const activeStep = ref(0);
    const isPaused = ref(false);
    const isPlaying = ref(false);

    let timer: ReturnType<typeof setInterval> | undefined;

    function clearTimer() {
        if (timer) {
            clearInterval(timer);
            timer = undefined;
        }
    }

    function startAutoplay() {
        clearTimer();
        isPlaying.value = true;
        timer = setInterval(() => {
            if (!isPaused.value) {
                activeStep.value = (activeStep.value + 1) % SLIDE_COUNT;
            }
        }, AUTO_ADVANCE_MS);
    }

    function stopAutoplay() {
        clearTimer();
        isPlaying.value = false;
    }

    function goToStep(index: number) {
        activeStep.value = ((index % SLIDE_COUNT) + SLIDE_COUNT) % SLIDE_COUNT;
    }

    function nextStep() {
        goToStep(activeStep.value + 1);
    }

    function prevStep() {
        goToStep(activeStep.value - 1);
    }

    onUnmounted(() => {
        clearTimer();
    });

    watch(isPaused, (paused) => {
        if (!paused && isPlaying.value && !timer) {
            startAutoplay();
        }
    });

    return {
        activeStep,
        isPaused,
        isPlaying,
        slideCount: SLIDE_COUNT,
        startAutoplay,
        stopAutoplay,
        goToStep,
        nextStep,
        prevStep,
    };
}
