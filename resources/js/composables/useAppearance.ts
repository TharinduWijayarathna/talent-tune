import { ref } from 'vue';

// Theme: application uses light mode only. Dark mode and system preference have been removed.
type Appearance = 'light';

/** Always applies light theme. Dark mode is disabled; document never gets 'dark' class. */
export function updateTheme(_value?: Appearance) {
    if (typeof document === 'undefined') {
        return;
    }
    document.documentElement.classList.remove('dark');
}

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }
    // Light mode only: ensure no dark class is ever applied.
    updateTheme('light');
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    function updateAppearance(_value: Appearance) {
        // No-op: theme is fixed to light. Kept for API compatibility.
        appearance.value = 'light';
        updateTheme('light');
    }

    return {
        appearance,
        updateAppearance,
    };
}
