import { onMounted, onUnmounted, ref } from 'vue';

export type Appearance = 'light' | 'dark' | 'system';

function getStoredAppearance(): Appearance {
    if (typeof document === 'undefined') {
        return 'system';
    }
    const match = document.cookie.match(/(?:^|;\s*)appearance=([^;]+)/);
    const value = match?.[1];
    if (value === 'light' || value === 'dark' || value === 'system') {
        return value;
    }
    return 'system';
}

function systemPrefersDark(): boolean {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    );
}

/**
 * Applies light or dark theme on the document root.
 */
export function updateTheme(mode: Appearance = getStoredAppearance()): void {
    if (typeof document === 'undefined') {
        return;
    }
    const isDark =
        mode === 'dark' || (mode === 'system' && systemPrefersDark());
    document.documentElement.classList.toggle('dark', isDark);
}

/**
 * Sync theme with stored preference and system changes.
 */
export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }
    updateTheme(getStoredAppearance());
}

const appearance = ref<Appearance>(getStoredAppearance());

/**
 * Theme composable — defaults to system (follows OS light/dark).
 */
export function useAppearance() {
    let mediaQuery: MediaQueryList | null = null;

    function onSystemChange() {
        if (appearance.value === 'system') {
            updateTheme('system');
        }
    }

    function updateAppearance(value: Appearance): void {
        appearance.value = value;
        document.cookie = `appearance=${value};path=/;max-age=31536000;SameSite=Lax`;
        updateTheme(value);
    }

    onMounted(() => {
        appearance.value = getStoredAppearance();
        updateTheme(appearance.value);
        mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', onSystemChange);
    });

    onUnmounted(() => {
        mediaQuery?.removeEventListener('change', onSystemChange);
    });

    return {
        appearance,
        updateAppearance,
    };
}
