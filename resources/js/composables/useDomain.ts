import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Composable to get the base domain (from APP_DOMAIN in config, shared via Inertia).
 * Falls back to deriving from window.location when not in shared props (e.g. SSR).
 */
export function useDomain() {
    const page = usePage();
    const baseDomain = computed(() => {
        const sharedBaseDomain = (page.props as any).baseDomain;
        if (sharedBaseDomain) {
            return sharedBaseDomain;
        }

        if (typeof window !== 'undefined') {
            const host = window.location.host;
            const parts = host.split('.');
            return parts.length >= 2 ? parts.slice(-2).join('.') : host;
        }

        return '';
    });

    /**
     * Get the full subdomain URL for an institution
     */
    const getInstitutionUrl = (slug: string): string => {
        const protocol =
            typeof window !== 'undefined' ? window.location.protocol : 'https:';
        return `${protocol}//${slug}.${baseDomain.value}`;
    };

    return {
        baseDomain,
        getInstitutionUrl,
    };
}
