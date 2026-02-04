<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
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
  Globe,
  Building2,
  Star,
  TrendingUp,
  Award,
  FileText,
  MessageSquare,
  PlayCircle,
  ChevronDown
} from 'lucide-vue-next'
import { login } from '@/routes'
import { ref, onMounted } from 'vue'

interface Props {
  canRegister: boolean
}

withDefaults(defineProps<Props>(), {
  canRegister: true,
})

const isVisible = ref(false)
const statsVisible = ref(false)

onMounted(() => {
  // Trigger animations on mount
  setTimeout(() => {
    isVisible.value = true
  }, 100)
  
  // Stats animation on scroll
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          statsVisible.value = true
        }
      })
    },
    { threshold: 0.3 }
  )
  
  const statsElement = document.getElementById('stats-section')
  if (statsElement) {
    observer.observe(statsElement)
  }
  
  return () => {
    if (statsElement) {
      observer.unobserve(statsElement)
    }
  }
})
</script>

<template>
  <Head title="TalentTune - AI-Powered Viva Management Platform for Educational Institutions" />
  
  <div class="min-h-screen bg-background">
    <!-- Navigation -->
    <nav class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50 transition-all">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex items-center gap-2">
            <GraduationCap class="h-6 w-6 text-primary animate-pulse" />
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">TalentTune</span>
          </div>
          <div class="flex items-center gap-4">
            <Link
              :href="'/register-institution'"
              class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors hidden sm:block"
            >
              Register Your University
            </Link>
            <Link
              :href="login()"
              class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
            >
              Sign In
            </Link>
            <Link :href="'/register-institution'">
              <Button size="sm" class="hidden sm:flex">
                Get Started
                <ArrowRight class="ml-2 h-4 w-4" />
              </Button>
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section with Animations -->
    <section class="relative overflow-hidden py-20 sm:py-32 min-h-[90vh] flex items-center justify-center">
      <!-- Animated Background -->
      <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-background to-background" />
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl animate-pulse" />
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl animate-pulse delay-1000" />
      </div>
      
      <!-- Floating Elements -->
      <div class="absolute top-20 left-10 w-2 h-2 bg-primary rounded-full animate-bounce delay-300 hidden lg:block" />
      <div class="absolute top-40 right-20 w-3 h-3 bg-primary/50 rounded-full animate-bounce delay-700 hidden lg:block" />
      <div class="absolute bottom-40 left-20 w-2 h-2 bg-primary/30 rounded-full animate-bounce delay-1000 hidden lg:block" />
      
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
        <div class="mx-auto max-w-5xl text-center">
          <!-- Badge -->
          <div 
            class="mb-8 inline-flex items-center gap-2 rounded-full border bg-muted/50 backdrop-blur px-4 py-1.5 text-sm animate-fade-in justify-center"
            :class="{ 'opacity-0 translate-y-4': !isVisible, 'opacity-100 translate-y-0': isVisible }"
            style="transition: all 0.6s ease-out;"
          >
            <Sparkles class="h-4 w-4 text-primary animate-spin-slow" />
            <span class="font-medium">AI-Powered Viva Management SaaS Platform</span>
            <Badge variant="secondary" class="ml-2">New</Badge>
          </div>
          
          <!-- Main Heading -->
          <h1 
            class="mb-6 text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl leading-tight text-center"
            :class="{ 'opacity-0 translate-y-8': !isVisible, 'opacity-100 translate-y-0': isVisible }"
            style="transition: all 0.8s ease-out 0.2s;"
          >
            Transform Your Institution's
            <span class="block mt-2 bg-gradient-to-r from-primary via-primary/80 to-primary/60 bg-clip-text text-transparent animate-gradient">
              Viva Sessions with AI
            </span>
          </h1>
          
          <!-- Description -->
          <p 
            class="mb-10 text-lg text-muted-foreground sm:text-xl lg:text-2xl max-w-3xl mx-auto leading-relaxed text-center"
            :class="{ 'opacity-0 translate-y-8': !isVisible, 'opacity-100 translate-y-0': isVisible }"
            style="transition: all 0.8s ease-out 0.4s;"
          >
            A comprehensive SaaS platform designed for educational institutions. 
            Streamline viva examinations with intelligent question generation, 
            automated evaluation, and seamless multi-tenant management.
          </p>
          
          <!-- CTA Buttons -->
          <div 
            class="flex flex-col items-center justify-center gap-4 sm:flex-row mb-12"
            :class="{ 'opacity-0 translate-y-8': !isVisible, 'opacity-100 translate-y-0': isVisible }"
            style="transition: all 0.8s ease-out 0.6s;"
          >
            <Link :href="'/register-institution'">
              <Button size="lg" class="w-full sm:w-auto group shadow-lg hover:shadow-xl transition-all hover:scale-105">
                Register Your University
                <Building2 class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform" />
              </Button>
            </Link>
            <Link :href="login()">
              <Button size="lg" variant="outline" class="w-full sm:w-auto border-2 hover:bg-muted/50">
                Sign In
              </Button>
            </Link>
          </div>

          <!-- Stats Preview -->
          <div 
            class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl mx-auto"
            :class="{ 'opacity-0 translate-y-8': !isVisible, 'opacity-100 translate-y-0': isVisible }"
            style="transition: all 0.8s ease-out 0.8s;"
          >
            <div class="text-center p-4 rounded-lg bg-muted/30 backdrop-blur border">
              <div class="text-2xl font-bold text-primary">500+</div>
              <div class="text-xs text-muted-foreground mt-1">Institutions</div>
            </div>
            <div class="text-center p-4 rounded-lg bg-muted/30 backdrop-blur border">
              <div class="text-2xl font-bold text-primary">50K+</div>
              <div class="text-xs text-muted-foreground mt-1">Students</div>
            </div>
            <div class="text-center p-4 rounded-lg bg-muted/30 backdrop-blur border">
              <div class="text-2xl font-bold text-primary">10K+</div>
              <div class="text-xs text-muted-foreground mt-1">Vivas Conducted</div>
            </div>
            <div class="text-center p-4 rounded-lg bg-muted/30 backdrop-blur border">
              <div class="text-2xl font-bold text-primary">98%</div>
              <div class="text-xs text-muted-foreground mt-1">Satisfaction</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce z-10">
        <ChevronDown class="h-6 w-6 text-muted-foreground" />
      </div>
    </section>

    <!-- Stats Section -->
    <section id="stats-section" class="py-16 bg-muted/30 border-y">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div 
            v-for="(stat, index) in [
              { value: '500+', label: 'Institutions', icon: Building2 },
              { value: '50K+', label: 'Students', icon: GraduationCap },
              { value: '10K+', label: 'Vivas', icon: FileText },
              { value: '98%', label: 'Satisfaction', icon: Star }
            ]"
            :key="index"
            class="text-center"
            :class="{ 'opacity-0 scale-95': !statsVisible, 'opacity-100 scale-100': statsVisible }"
            :style="`transition: all 0.6s ease-out ${index * 0.1}s;`"
          >
            <component :is="stat.icon" class="h-8 w-8 text-primary mx-auto mb-2" />
            <div class="text-3xl font-bold mb-1">{{ stat.value }}</div>
            <div class="text-sm text-muted-foreground">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <Badge variant="outline" class="mb-4">Features</Badge>
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            Everything Your Institution Needs
          </h2>
          <p class="text-lg text-muted-foreground">
            Powerful features designed to make viva examinations efficient, fair, and insightful.
          </p>
        </div>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <Card 
            v-for="(feature, index) in [
              {
                icon: Brain,
                title: 'AI Question Generation',
                description: 'Generate intelligent, context-aware questions using advanced AI models tailored to your subject matter. Supports multiple difficulty levels and topics.',
                color: 'from-blue-500 to-cyan-500'
              },
              {
                icon: Mic,
                title: 'Voice-Based Interaction',
                description: 'Natural voice interaction with high-quality text-to-speech and speech recognition for seamless viva sessions. Real-time transcription available.',
                color: 'from-purple-500 to-pink-500'
              },
              {
                icon: BarChart3,
                title: 'Automated Evaluation',
                description: 'Get instant, detailed feedback and scoring powered by AI, ensuring consistent and objective assessments with comprehensive analytics.',
                color: 'from-green-500 to-emerald-500'
              },
              {
                icon: Users,
                title: 'Multi-Tenant Architecture',
                description: 'Each institution gets its own isolated environment with custom branding, dedicated subdomain, and complete data separation.',
                color: 'from-orange-500 to-red-500'
              },
              {
                icon: Clock,
                title: 'Session Scheduling',
                description: 'Easy scheduling and management of viva sessions with calendar integration, automated reminders, and batch management.',
                color: 'from-indigo-500 to-blue-500'
              },
              {
                icon: Shield,
                title: 'Secure & Reliable',
                description: 'Enterprise-grade security with data encryption, secure authentication, two-factor auth, and compliance with educational standards.',
                color: 'from-teal-500 to-green-500'
              }
            ]"
            :key="index"
            class="group hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-2 hover:border-primary/50"
          >
            <CardHeader>
              <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br group-hover:scale-110 transition-transform" :class="feature.color">
                <component :is="feature.icon" class="h-6 w-6 text-white" />
              </div>
              <CardTitle class="group-hover:text-primary transition-colors">{{ feature.title }}</CardTitle>
              <CardDescription class="text-base leading-relaxed">
                {{ feature.description }}
              </CardDescription>
            </CardHeader>
          </Card>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="bg-muted/50 py-20 sm:py-32 relative overflow-hidden">
      <div class="absolute inset-0 bg-grid-pattern opacity-5" />
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="mx-auto max-w-2xl text-center mb-16">
          <Badge variant="outline" class="mb-4">Process</Badge>
          <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-4">
            How It Works
          </h2>
          <p class="text-lg text-muted-foreground">
            Get your institution started in minutes with our streamlined process.
          </p>
        </div>
        <div class="grid gap-8 md:grid-cols-3 max-w-5xl mx-auto">
          <div 
            v-for="(step, index) in [
              {
                number: '1',
                title: 'Register Your Institution',
                description: 'Fill out the registration form with your institution details. Our admin team will review and activate your account within 24 hours.',
                icon: Building2
              },
              {
                number: '2',
                title: 'Get Your Subdomain',
                description: 'Once activated, access your institution portal via your custom subdomain (e.g., your-university.talenttune.com).',
                icon: Globe
              },
              {
                number: '3',
                title: 'Start Managing Vivas',
                description: 'Add lecturers and students, create viva sessions, and leverage AI-powered question generation and evaluation.',
                icon: Zap
              }
            ]"
            :key="index"
            class="text-center relative"
          >
            <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/60 text-primary-foreground text-2xl font-bold shadow-lg">
              {{ step.number }}
            </div>
            <component :is="step.icon" class="h-8 w-8 text-primary mx-auto mb-4" />
            <h3 class="mb-3 text-xl font-semibold">{{ step.title }}</h3>
            <p class="text-muted-foreground leading-relaxed">
              {{ step.description }}
            </p>
            <div v-if="index < 2" class="hidden md:block absolute top-8 left-full w-full h-0.5 bg-gradient-to-r from-primary/50 to-transparent transform translate-x-4" />
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 sm:py-32">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <Badge variant="outline" class="mb-4">Benefits</Badge>
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-6">
              Why Educational Institutions Choose TalentTune
            </h2>
            <div class="space-y-6">
              <div 
                v-for="(benefit, index) in [
                  {
                    icon: TrendingUp,
                    title: 'Save Up to 80% Time',
                    description: 'Automate question generation and evaluation, reducing manual workload significantly. Lecturers can focus on teaching instead of paperwork.'
                  },
                  {
                    icon: Award,
                    title: 'Consistent Evaluation',
                    description: 'AI-powered evaluation ensures fair and consistent assessment across all students, eliminating human bias and subjectivity.'
                  },
                  {
                    icon: MessageSquare,
                    title: 'Enhanced Learning',
                    description: 'Detailed feedback helps students understand their strengths and areas for improvement, promoting better learning outcomes.'
                  },
                  {
                    icon: Zap,
                    title: 'Scalable Solution',
                    description: 'Handle multiple sessions simultaneously, perfect for institutions of any size. From small colleges to large universities.'
                  }
                ]"
                :key="index"
                class="flex gap-4 group"
              >
                <div class="flex-shrink-0">
                  <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                    <component :is="benefit.icon" class="h-6 w-6 text-primary" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-lg mb-1">{{ benefit.title }}</h3>
                  <p class="text-muted-foreground">{{ benefit.description }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-transparent rounded-2xl blur-3xl" />
            <Card class="relative border-2">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Zap class="h-5 w-5 text-primary" />
                  Real-Time Performance
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-6">
                <div v-for="metric in [
                  { label: 'Question Generation', value: 98, color: 'bg-primary' },
                  { label: 'Evaluation Accuracy', value: 95, color: 'bg-green-500' },
                  { label: 'User Satisfaction', value: 92, color: 'bg-blue-500' },
                  { label: 'System Uptime', value: 99.9, color: 'bg-purple-500' }
                ]" :key="metric.label">
                  <div class="flex justify-between text-sm mb-2">
                    <span>{{ metric.label }}</span>
                    <span class="font-medium">{{ metric.value }}%</span>
                  </div>
                  <div class="h-2 bg-muted rounded-full overflow-hidden">
                    <div 
                      class="h-full rounded-full transition-all duration-1000 ease-out" 
                      :class="metric.color"
                      :style="`width: ${metric.value}%`"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-br from-primary via-primary/90 to-primary/80 py-20 sm:py-32 relative overflow-hidden">
      <div class="absolute inset-0 bg-grid-pattern opacity-10" />
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="mx-auto max-w-3xl text-center">
          <Sparkles class="h-12 w-12 text-primary-foreground/80 mx-auto mb-6 animate-pulse" />
          <h2 class="text-3xl font-bold tracking-tight text-primary-foreground sm:text-4xl mb-4">
            Ready to Transform Your Institution's Viva Sessions?
          </h2>
          <p class="text-lg text-primary-foreground/90 mb-8 text-xl">
            Join 500+ educational institutions worldwide using TalentTune to streamline their examination process.
          </p>
          <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <Link :href="'/register-institution'">
              <Button size="lg" variant="secondary" class="w-full sm:w-auto shadow-xl hover:shadow-2xl hover:scale-105 transition-all">
                Register Your University
                <ArrowRight class="ml-2 h-4 w-4" />
              </Button>
            </Link>
            <Link :href="login()">
              <Button size="lg" variant="outline" class="w-full sm:w-auto bg-transparent border-primary-foreground/30 text-primary-foreground hover:bg-primary-foreground/10">
                Sign In
              </Button>
            </Link>
          </div>
          <p class="mt-6 text-sm text-primary-foreground/70">
            Free trial available • No credit card required • Setup in minutes
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t bg-background py-12">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 md:grid-cols-4">
          <div class="md:col-span-2">
            <div class="flex items-center gap-2 mb-4">
              <GraduationCap class="h-6 w-6 text-primary" />
              <span class="text-xl font-bold">TalentTune</span>
            </div>
            <p class="text-sm text-muted-foreground max-w-md mb-4">
              AI-powered viva management SaaS platform designed to revolutionize how educational institutions conduct examinations.
            </p>
            <div class="flex gap-4">
              <Badge variant="outline" class="text-xs">Trusted by 500+ Institutions</Badge>
              <Badge variant="outline" class="text-xs">99.9% Uptime</Badge>
            </div>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Product</h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              <li><Link href="/register-institution" class="hover:text-foreground transition-colors">Register Institution</Link></li>
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
              <li><a href="#" class="hover:text-foreground transition-colors">Terms</a></li>
            </ul>
          </div>
        </div>
        <div class="mt-8 border-t pt-8 text-center text-sm text-muted-foreground">
          <p>&copy; {{ new Date().getFullYear() }} TalentTune. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@keyframes gradient {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.animate-gradient {
  background-size: 200% 200%;
  animation: gradient 3s ease infinite;
}

.animate-spin-slow {
  animation: spin 3s linear infinite;
}

.animate-fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.bg-grid-pattern {
  background-image: 
    linear-gradient(to right, rgba(0,0,0,0.1) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0,0,0,0.1) 1px, transparent 1px);
  background-size: 20px 20px;
}

.delay-300 {
  animation-delay: 0.3s;
}

.delay-700 {
  animation-delay: 0.7s;
}

.delay-1000 {
  animation-delay: 1s;
}
</style>
