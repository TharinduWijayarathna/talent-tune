<script setup lang="ts">
import { pricingPlans } from '@/data/pricingPlans';
import { registerInstitution } from '@/routes';
import { Link } from '@inertiajs/vue3';

const registerUrl = registerInstitution.url();
</script>

<template>
    <section id="pricing" class="pricing-section">
        <div class="pricing-inner">
            <div class="section-tag">Pricing</div>
            <h2 class="section-heading reveal">
                Plans that scale with your <em>institution</em>.
            </h2>
            <p class="section-sub reveal">
                Transparent monthly pricing based on viva volume and cohort
                size. Pick the tier that fits your examination programme.
            </p>

            <div class="pricing-grid">
                <article
                    v-for="plan in pricingPlans"
                    :key="plan.id"
                    class="pricing-card reveal"
                    :class="{ featured: plan.highlighted }"
                >
                    <span v-if="plan.highlighted" class="pricing-badge"
                        >Most popular</span
                    >
                    <h3 class="pricing-name">{{ plan.name }}</h3>
                    <p class="pricing-desc">{{ plan.description }}</p>
                    <div class="pricing-amount">
                        ${{ plan.price }}<span>/mo</span>
                    </div>
                    <p class="pricing-period">Billed monthly</p>

                    <div class="pricing-limits">
                        <div class="pricing-limit">
                            <span>Vivas per month</span>
                            <strong>{{ plan.vivasPerMonth }}</strong>
                        </div>
                        <div class="pricing-limit">
                            <span>Students per viva</span>
                            <strong>{{ plan.studentsPerViva }}</strong>
                        </div>
                    </div>

                    <ul class="pricing-features">
                        <li v-for="(feature, i) in plan.features" :key="i">
                            {{ feature }}
                        </li>
                    </ul>

                    <Link
                        :href="registerUrl"
                        class="pricing-cta"
                        :class="{ primary: plan.highlighted }"
                    >
                        Get started
                    </Link>
                </article>
            </div>
        </div>
    </section>
</template>
