export interface PricingPlan {
    id: string;
    name: string;
    description: string;
    price: number;
    vivasPerMonth: number;
    studentsPerViva: number;
    highlighted?: boolean;
    features: string[];
}

export const pricingPlans: PricingPlan[] = [
    {
        id: 'starter',
        name: 'Starter',
        description: 'Ideal for small departments running occasional viva sessions.',
        price: 29,
        vivasPerMonth: 5,
        studentsPerViva: 49,
        features: [
            '5 vivas per month',
            'Up to 49 students per viva',
            'AI voice examinations',
            'Full transcription archive',
            'Email support',
        ],
    },
    {
        id: 'professional',
        name: 'Professional',
        description: 'For faculties running regular oral assessments across cohorts.',
        price: 79,
        vivasPerMonth: 15,
        studentsPerViva: 50,
        highlighted: true,
        features: [
            '15 vivas per month',
            'Up to 50 students per viva',
            'Institution workspace',
            'Analytics dashboard',
            'Priority email support',
        ],
    },
    {
        id: 'enterprise',
        name: 'Enterprise',
        description: 'For large institutions with high-volume viva programmes.',
        price: 129,
        vivasPerMonth: 20,
        studentsPerViva: 100,
        features: [
            '20 vivas per month',
            'Up to 100 students per viva',
            'Custom branding & subdomain',
            'Advanced analytics',
            'Dedicated onboarding',
        ],
    },
];
