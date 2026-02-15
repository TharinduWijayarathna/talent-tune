import { ref } from 'vue';

// Theme: application uses light mode only. Dark mode and system preference have been removed.
type Appearance = 'light';

/**
 * Applies the light theme only. No 'dark' class is ever set.
 */
export function updateTheme(): void {
    if (typeof document === 'undefined') {
        return;
    }
    document.documentElement.classList.remove('dark');
}

/**
 * Ensures light mode is set on initial load.
 */
export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }
    updateTheme();
}

const appearance = ref<Appearance>('light');

/**
 * useAppearance composable.
 * The application enforces light mode. The API remains for compatibility.
 */
export function useAppearance() {
    function updateAppearance(): void {
        appearance.value = 'light';
        updateTheme();
    }

    return {
        appearance,
        updateAppearance,
    };
}
