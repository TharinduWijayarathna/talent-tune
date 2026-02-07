import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Composable to get the base domain (e.g., talenttune.test or talenttune.com)
 * Uses the baseDomain from Inertia shared props, or falls back to detecting from window.location
 */
export function useDomain() {
    const page = usePage();
    const baseDomain = computed(() => {
        // First try to get from Inertia shared props
        const sharedBaseDomain = (page.props as any).baseDomain;
        if (sharedBaseDomain) {
            return sharedBaseDomain;
        }

        // Fallback: detect from window.location
        if (typeof window !== 'undefined') {
            const host = window.location.host;
            const parts = host.split('.');

            // For .test domains in local dev
            if (host.endsWith('.test')) {
                return parts.length >= 2 ? parts.slice(-2).join('.') : host;
            }

            // For production domains
            return parts.length >= 2 ? parts.slice(-2).join('.') : host;
        }

        // Default fallback
        return 'talenttune.com';
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
