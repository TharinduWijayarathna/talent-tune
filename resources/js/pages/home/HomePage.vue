<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Mic,
  GraduationCap,
  Users,
  Brain,
  Clock,
  BarChart3,
  CheckCircle2,
  ArrowRight,
  Sparkles,
  Shield,
  Zap,
  Globe
} from 'lucide-vue-next'
import { dashboard, login, register } from '@/routes'
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
  <Head :title="`${institutionName} - AI-Powered Viva Management Platform`" />

  <div class="min-h-screen bg-background">
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
            <template v-else>
              <Link
                :href="login()"
                class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
              >
                Sign In
              </Link>
              <Link
                v-if="canRegister"
                :href="register()"
              >
                <Button size="sm">Get Started</Button>
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-20 sm:py-32">
      <div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/5 via-background to-background" />
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl text-center">
          <div class="mb-6 inline-flex items-center gap-2 rounded-full border bg-muted px-4 py-1.5 text-sm">
            <Sparkles :class="institutionColor ? '' : 'text-primary'" :style="institutionColor ? { color: institutionColor } : {}" class="h-4 w-4" />
            <span>{{ institutionName }} - AI-Powered Viva Management</span>
          </div>
          <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
            <span v-if="$page.props.auth?.user">Welcome back to</span>
            <span v-else>Welcome to </span>
            <span
              :class="institutionColor ? '' : 'bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent'"
              :style="institutionColor ? { color: institutionColor } : {}"
            >
              {{ institutionName }}
            </span>
          </h1>
          <p class="mb-10 text-lg text-muted-foreground sm:text-xl lg:text-2xl">
            <span v-if="$page.props.auth?.user">
              You're logged in! Access your dashboard to manage viva sessions, view your progress, and more.
            </span>
            <span v-else>
              Streamline viva examinations with intelligent question generation,
              automated evaluation, and seamless management for institutions, lecturers, and students.
            </span>
          </p>
          <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <template v-if="$page.props.auth?.user">
              <Link :href="dashboard()">
                <Button size="lg" class="w-full sm:w-auto">
                  Go to Dashboard
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </template>
            <template v-else>
              <Link v-if="canRegister" :href="register()">
                <Button size="lg" class="w-full sm:w-auto">
                  Start Free Trial
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
              <Link :href="login()">
                <Button size="lg" variant="outline" class="w-full sm:w-auto">
                  Sign In
                </Button>
              </Link>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            Everything You Need for Modern Viva Management
          </h2>
          <p class="text-lg text-muted-foreground">
            Powerful features designed to make viva examinations efficient, fair, and insightful.
          </p>
        </div>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <Card>
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <Brain class="h-6 w-6 text-primary" />
              </div>
              <CardTitle>AI Question Generation</CardTitle>
              <CardDescription>
                Generate intelligent, context-aware questions using advanced AI models tailored to your subject matter.
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <Mic class="h-6 w-6 text-primary" />
              </div>
              <CardTitle>Voice-Based Interaction</CardTitle>
              <CardDescription>
                Natural voice interaction with high-quality text-to-speech and speech recognition for seamless viva sessions.
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <BarChart3 class="h-6 w-6 text-primary" />
              </div>
              <CardTitle>Automated Evaluation</CardTitle>
              <CardDescription>
                Get instant, detailed feedback and scoring powered by AI, ensuring consistent and objective assessments.
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <Users class="h-6 w-6 text-primary" />
              </div>
              <CardTitle>Role-Based Management</CardTitle>
              <CardDescription>
                Comprehensive dashboards for institutions, lecturers, and students with role-specific features and insights.
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <Clock class="h-6 w-6 text-primary" />
              </div>
              <CardTitle>Session Scheduling</CardTitle>
              <CardDescription>
                Easy scheduling and management of viva sessions with calendar integration and automated reminders.
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <Shield class="h-6 w-6 text-primary" />
              </div>
              <CardTitle>Secure & Reliable</CardTitle>
              <CardDescription>
                Enterprise-grade security with data encryption, secure authentication, and compliance with educational standards.
              </CardDescription>
            </CardHeader>
          </Card>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="bg-muted/50 py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            How It Works
          </h2>
          <p class="text-lg text-muted-foreground">
            Get started in minutes and transform your viva examination process.
          </p>
        </div>
        <div class="grid gap-8 md:grid-cols-3">
          <div class="text-center">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground text-xl font-bold">
              1
            </div>
            <h3 class="mb-2 text-xl font-semibold">Create Sessions</h3>
            <p class="text-muted-foreground">
              Lecturers create viva sessions with topics, descriptions, and scheduling details.
            </p>
          </div>
          <div class="text-center">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground text-xl font-bold">
              2
            </div>
            <h3 class="mb-2 text-xl font-semibold">AI Generates Questions</h3>
            <p class="text-muted-foreground">
              Our AI generates relevant questions based on the topic and difficulty level.
            </p>
          </div>
          <div class="text-center">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground text-xl font-bold">
              3
            </div>
            <h3 class="mb-2 text-xl font-semibold">Students Attend</h3>
            <p class="text-muted-foreground">
              Students attend sessions with voice interaction and receive instant AI-powered feedback.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
          <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
            <div>
              <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-6">
                Why Choose {{ institutionName }}?
              </h2>
              <ul class="space-y-4">
                <li class="flex items-start gap-3">
                  <CheckCircle2 class="mt-0.5 h-5 w-5 text-primary flex-shrink-0" />
                  <div>
                    <p class="font-medium">Save Time</p>
                    <p class="text-sm text-muted-foreground">
                      Automate question generation and evaluation, reducing manual workload by up to 80%.
                    </p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <CheckCircle2 class="mt-0.5 h-5 w-5 text-primary flex-shrink-0" />
                  <div>
                    <p class="font-medium">Consistent Evaluation</p>
                    <p class="text-sm text-muted-foreground">
                      AI-powered evaluation ensures fair and consistent assessment across all students.
                    </p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <CheckCircle2 class="mt-0.5 h-5 w-5 text-primary flex-shrink-0" />
                  <div>
                    <p class="font-medium">Enhanced Learning</p>
                    <p class="text-sm text-muted-foreground">
                      Detailed feedback helps students understand their strengths and areas for improvement.
                    </p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <CheckCircle2 class="mt-0.5 h-5 w-5 text-primary flex-shrink-0" />
                  <div>
                    <p class="font-medium">Scalable Solution</p>
                    <p class="text-sm text-muted-foreground">
                      Handle multiple sessions simultaneously, perfect for institutions of any size.
                    </p>
                  </div>
                </li>
              </ul>
            </div>
            <div class="flex items-center justify-center">
              <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-primary/5 rounded-2xl blur-3xl" />
                <Card class="relative">
                  <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                      <Zap class="h-5 w-5 text-primary" />
                      Real-Time Performance
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div class="space-y-4">
                      <div>
                        <div class="flex justify-between text-sm mb-1">
                          <span>Question Generation</span>
                          <span class="font-medium">98%</span>
                        </div>
                        <div class="h-2 bg-muted rounded-full overflow-hidden">
                          <div class="h-full bg-primary rounded-full" style="width: 98%" />
                        </div>
                      </div>
                      <div>
                        <div class="flex justify-between text-sm mb-1">
                          <span>Evaluation Accuracy</span>
                          <span class="font-medium">95%</span>
                        </div>
                        <div class="h-2 bg-muted rounded-full overflow-hidden">
                          <div class="h-full bg-primary rounded-full" style="width: 95%" />
                        </div>
                      </div>
                      <div>
                        <div class="flex justify-between text-sm mb-1">
                          <span>User Satisfaction</span>
                          <span class="font-medium">92%</span>
                        </div>
                        <div class="h-2 bg-muted rounded-full overflow-hidden">
                          <div class="h-full bg-primary rounded-full" style="width: 92%" />
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section :class="institutionColor ? 'institution-bg-primary' : 'bg-primary'" class="py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2 class="text-3xl font-bold tracking-tight text-primary-foreground sm:text-4xl mb-4">
            <span v-if="$page.props.auth?.user">Ready to Continue Your Journey?</span>
            <span v-else>Ready to Transform Your Viva Sessions?</span>
          </h2>
          <p class="text-lg text-primary-foreground/90 mb-8">
            <span v-if="$page.props.auth?.user">
              Access your dashboard to manage sessions, view results, and track your progress.
            </span>
            <span v-else>
              Join {{ institutionName }} in streamlining your examination process with AI-powered viva management.
            </span>
          </p>
          <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <template v-if="$page.props.auth?.user">
              <Link :href="dashboard()">
                <Button size="lg" variant="secondary" class="w-full sm:w-auto">
                  Go to Dashboard
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </template>
            <template v-else>
              <Link v-if="canRegister" :href="register()">
                <Button size="lg" variant="secondary" class="w-full sm:w-auto">
                  Get Started Free
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
              <Link :href="login()">
                <Button size="lg" variant="outline" class="w-full sm:w-auto bg-transparent border-primary-foreground/20 text-primary-foreground hover:bg-primary-foreground/10">
                  Learn More
                </Button>
              </Link>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t bg-background py-12">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 md:grid-cols-4">
          <div class="md:col-span-2">
            <div class="flex items-center gap-2 mb-4">
              <img v-if="institutionLogo" :src="institutionLogo" :alt="institutionName" class="h-6 w-6 rounded" />
              <GraduationCap v-else :class="institutionColor ? '' : 'text-primary'" :style="institutionColor ? { color: institutionColor } : {}" class="h-6 w-6" />
              <span class="text-xl font-bold">{{ institutionName }}</span>
            </div>
            <p class="text-sm text-muted-foreground max-w-md">
              AI-powered viva management platform designed to revolutionize how {{ institutionName }} conducts examinations.
            </p>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Product</h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              <li><a href="#" class="hover:text-foreground transition-colors">Features</a></li>
              <li><a href="#" class="hover:text-foreground transition-colors">Pricing</a></li>
              <li><a href="#" class="hover:text-foreground transition-colors">Documentation</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Company</h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              <li><a href="#" class="hover:text-foreground transition-colors">About</a></li>
              <li><a href="#" class="hover:text-foreground transition-colors">Contact</a></li>
              <li><a href="#" class="hover:text-foreground transition-colors">Privacy</a></li>
            </ul>
          </div>
        </div>
        <div class="mt-8 border-t pt-8 text-center text-sm text-muted-foreground">
          <p>&copy; {{ new Date().getFullYear() }} {{ institutionName }}. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
