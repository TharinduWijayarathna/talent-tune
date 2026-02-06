<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { GraduationCap, ArrowRight, CheckCircle2 } from 'lucide-vue-next'
import { dashboard, login } from '@/routes'
import { computed, onMounted, watch } from 'vue'
import type { Institution } from '@/types'

interface Props {
    canRegister: boolean
    institution?: Institution | null
}

const props = withDefaults(defineProps<Props>(), {
    canRegister: true,
    institution: null,
})

const page = usePage()
const institution = computed(() => props.institution || page.props.institution)
const institutionName = computed(() => institution.value?.name || 'TalentTune')
const institutionLogo = computed(() => institution.value?.logo_url)
const institutionColor = computed(() => institution.value?.primary_color)

// Apply institution color as CSS variable if available
onMounted(() => {
    applyInstitutionColor()
})
watch(institutionColor, () => {
    applyInstitutionColor()
})

const applyInstitutionColor = () => {
    if (institutionColor.value) {
        // Apply as CSS variable
        document.documentElement.style.setProperty('--institution-primary', institutionColor.value)
        // Also apply to primary color classes via style tag
        const styleId = 'institution-branding'
        let styleElement = document.getElementById(styleId)
        if (!styleElement) {
            styleElement = document.createElement('style')
            styleElement.id = styleId
            document.head.appendChild(styleElement)
        }
        styleElement.textContent = `
            .institution-primary { color: ${institutionColor.value} !important; }
            .institution-bg-primary { background-color: ${institutionColor.value} !important; }
            .institution-border-primary { border-color: ${institutionColor.value} !important; }
            .institution-gradient { background: linear-gradient(to right, ${institutionColor.value}, ${institutionColor.value}80) !important; }
        `
    }
}
</script>

<template>
  <Head :title="`${institutionName} - Viva Management Platform`" />

  <div class="min-h-screen bg-background flex flex-col">
    <!-- Navigation -->
    <nav class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex items-center gap-2">
            <img v-if="institutionLogo" :src="institutionLogo" :alt="institutionName" class="h-8 w-8 rounded" />
            <GraduationCap v-else :class="institutionColor ? 'institution-primary' : 'text-primary'" class="h-6 w-6" />
            <span class="text-xl font-bold">{{ institutionName }}</span>
          </div>
          <div class="flex items-center gap-4">
            <Link
              v-if="$page.props.auth?.user"
              :href="dashboard()"
              class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
            >
              Dashboard
            </Link>
            <Link
              v-else
              :href="login()"
            >
              <Button size="sm">Sign In</Button>
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="w-full max-w-4xl">
        <!-- Welcome Section -->
        <div class="text-center mb-12">
          <h1 class="text-4xl font-bold tracking-tight sm:text-5xl mb-4">
            <span
              :class="institutionColor ? '' : 'bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent'"
              :style="institutionColor ? { color: institutionColor } : {}"
            >
              {{ institutionName }}
            </span>
          </h1>
          <p class="text-lg text-muted-foreground">
            <span v-if="$page.props.auth?.user">
              Welcome back! Access your dashboard to manage viva sessions.
            </span>
            <span v-else>
              Access your viva management platform
            </span>
          </p>
        </div>

        <!-- Pricing/Plan Section -->
        <Card class="mb-8">
          <CardHeader>
            <CardTitle>Your Plan</CardTitle>
            <CardDescription>Current subscription details</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 rounded-lg bg-muted">
                <div>
                  <p class="font-semibold">Standard Plan</p>
                  <p class="text-sm text-muted-foreground">Full access to all features</p>
                </div>
                <div class="text-right">
                  <p class="text-2xl font-bold">$99<span class="text-sm font-normal text-muted-foreground">/month</span></p>
                </div>
              </div>
              <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="h-4 w-4 text-primary" />
                  <span>Unlimited viva sessions</span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="h-4 w-4 text-primary" />
                  <span>AI-powered question generation</span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="h-4 w-4 text-primary" />
                  <span>Automated evaluation & feedback</span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="h-4 w-4 text-primary" />
                  <span>Priority support</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Login CTA -->
        <div class="text-center">
          <template v-if="$page.props.auth?.user">
            <Link :href="dashboard()">
              <Button size="lg" class="w-full sm:w-auto min-w-[200px]">
                Go to Dashboard
                <ArrowRight class="ml-2 h-4 w-4" />
              </Button>
            </Link>
          </template>
          <template v-else>
            <Link :href="login()">
              <Button size="lg" class="w-full sm:w-auto min-w-[200px]">
                Sign In to Dashboard
                <ArrowRight class="ml-2 h-4 w-4" />
              </Button>
            </Link>
            <p class="mt-4 text-sm text-muted-foreground">
              Need help? Contact your administrator
            </p>
          </template>
        </div>
      </div>
    </main>

    <!-- Simple Footer -->
    <footer class="border-t bg-background py-6">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-muted-foreground">
          <p>&copy; {{ new Date().getFullYear() }} {{ institutionName }}. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
