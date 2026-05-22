import { onMounted, onUnmounted, ref } from 'vue';

const typingPhrases = [
    'In my implementation, I used a write-through cache strategy with optimistic locking...',
    'The primary concern with concurrent writes is race conditions, so I implemented...',
    'I designed the cache layer to use a mutex lock on write operations while...',
];

export function useMarketingHome() {
    const typingText = ref('');
    const progressScale = ref(0);

    let typingTimer: ReturnType<typeof setTimeout> | undefined;
    let phraseIdx = 0;
    let charIdx = 0;
    let deleting = false;

    function runTyping() {
        const current = typingPhrases[phraseIdx];
        if (!deleting) {
            typingText.value = current.substring(0, charIdx + 1);
            charIdx++;
            if (charIdx >= current.length) {
                deleting = true;
                typingTimer = setTimeout(runTyping, 2000);
                return;
            }
            typingTimer = setTimeout(runTyping, 45);
        } else {
            typingText.value = current.substring(0, charIdx - 1);
            charIdx--;
            if (charIdx === 0) {
                deleting = false;
                phraseIdx = (phraseIdx + 1) % typingPhrases.length;
            }
            typingTimer = setTimeout(runTyping, 20);
        }
    }

    function onScroll() {
        const max =
            document.documentElement.scrollHeight - window.innerHeight;
        progressScale.value = max > 0 ? window.scrollY / max : 0;
    }

    let revealObserver: IntersectionObserver | undefined;

    onMounted(() => {
        runTyping();
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        const revealEls = document.querySelectorAll('.marketing-page .reveal');
        revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -60px 0px' },
        );
        revealEls.forEach((el) => revealObserver?.observe(el));
    });

    onUnmounted(() => {
        revealObserver?.disconnect();
        window.removeEventListener('scroll', onScroll);
        if (typingTimer) {
            clearTimeout(typingTimer);
        }
    });

    return {
        typingText,
        progressScale,
    };
}
