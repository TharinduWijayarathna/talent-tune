import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { Institution } from '@/types'

export function useInstitution() {
    const page = usePage()
    
    const institution = computed<Institution | null>(() => {
        return (page.props.institution as Institution | null) || null
    })
    
    const hasInstitution = computed(() => institution.value !== null)
    
    const institutionName = computed(() => institution.value?.name || 'TalentTune')
    
    const institutionLogo = computed(() => institution.value?.logo_url)
    
    const institutionColor = computed(() => institution.value?.primary_color)
    
    return {
        institution,
        hasInstitution,
        institutionName,
        institutionLogo,
        institutionColor,
    }
}
