<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Mic, MicOff, Volume2, Pause, Play, Square } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Viva Sessions', href: '/student/vivas' },
    { title: 'Attend Viva', href: '#' },
];

// Mock viva session data
const vivaSession = {
    id: 1,
    title: 'Database Systems Viva',
    lecturer: 'Dr. Smith',
    date: '2024-01-20',
    time: '10:00 AM',
};

const isRecording = ref(false);
const isListening = ref(false);
const currentQuestion = ref<string | null>(null);
const answer = ref('');
const questions = ref<string[]>([]);
const sessionActive = ref(false);
const timeElapsed = ref(0);

// Mock questions - in real app, these would come from the backend
const mockQuestions = [
    'What is a database?',
    'Explain the difference between SQL and NoSQL databases.',
    'What is normalization in database design?',
    'Describe the ACID properties of database transactions.',
    'What are indexes and why are they important?',
];

let timerInterval: ReturnType<typeof setInterval> | null = null;

const startSession = () => {
    sessionActive.value = true;
    timeElapsed.value = 0;
    questions.value = [...mockQuestions];
    currentQuestion.value = questions.value[0];
    
    // Start timer
    timerInterval = setInterval(() => {
        timeElapsed.value++;
    }, 1000);

    // Simulate voice agent speaking
    speakQuestion(currentQuestion.value);
};

const stopSession = () => {
    sessionActive.value = false;
    isRecording.value = false;
    isListening.value = false;
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
};

const speakQuestion = (question: string) => {
    isListening.value = true;
    // In a real app, this would use Web Speech API or a backend service
    // For now, we'll just show the question
    setTimeout(() => {
        isListening.value = false;
        isRecording.value = true;
    }, 2000);
};

const submitAnswer = () => {
    if (!answer.value.trim()) return;
    
    // Move to next question
    const currentIndex = questions.value.indexOf(currentQuestion.value!);
    if (currentIndex < questions.value.length - 1) {
        currentQuestion.value = questions.value[currentIndex + 1];
        answer.value = '';
        speakQuestion(currentQuestion.value);
    } else {
        // All questions answered
        stopSession();
        alert('Viva session completed! Your answers have been submitted.');
    }
};

const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});
</script>

<template>
    <Head title="Attend Viva" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ vivaSession.title }}</h1>
                    <p class="text-muted-foreground">{{ vivaSession.lecturer }} • {{ vivaSession.date }} at {{ vivaSession.time }}</p>
                </div>
                <Badge v-if="sessionActive" variant="default" class="text-sm">
                    Session Active
                </Badge>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Voice Agent UI -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Voice Agent</CardTitle>
                        <CardDescription>Listen to questions and provide your answers</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Voice Status -->
                        <div class="flex items-center justify-center">
                            <div
                                class="relative flex h-32 w-32 items-center justify-center rounded-full border-4 transition-all"
                                :class="{
                                    'border-primary bg-primary/10 animate-pulse': isListening,
                                    'border-muted': !isListening && !isRecording,
                                    'border-green-500 bg-green-500/10': isRecording && !isListening,
                                }"
                            >
                                <div
                                    v-if="isListening"
                                    class="flex items-center justify-center"
                                >
                                    <Volume2 class="h-12 w-12 text-primary animate-pulse" />
                                </div>
                                <div
                                    v-else-if="isRecording"
                                    class="flex items-center justify-center"
                                >
                                    <Mic class="h-12 w-12 text-green-500 animate-pulse" />
                                </div>
                                <div
                                    v-else
                                    class="flex items-center justify-center"
                                >
                                    <MicOff class="h-12 w-12 text-muted-foreground" />
                                </div>
                            </div>
                        </div>

                        <!-- Current Question -->
                        <div v-if="currentQuestion" class="space-y-4">
                            <div class="rounded-lg bg-muted p-4">
                                <div class="text-sm font-medium mb-2">Current Question:</div>
                                <div class="text-lg">{{ currentQuestion }}</div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Your Answer:</label>
                                <textarea
                                    v-model="answer"
                                    class="w-full min-h-[120px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    placeholder="Type or speak your answer here..."
                                />
                            </div>

                            <div class="flex gap-2">
                                <Button
                                    @click="submitAnswer"
                                    :disabled="!answer.trim() || !sessionActive"
                                    class="flex-1"
                                >
                                    Submit Answer
                                </Button>
                            </div>
                        </div>

                        <div v-else class="text-center text-muted-foreground py-8">
                            <p>Click "Start Session" to begin the viva</p>
                        </div>

                        <!-- Session Controls -->
                        <div class="flex items-center justify-center gap-4 pt-4 border-t">
                            <Button
                                v-if="!sessionActive"
                                @click="startSession"
                                size="lg"
                            >
                                <Play class="h-4 w-4 mr-2" />
                                Start Session
                            </Button>
                            <Button
                                v-else
                                @click="stopSession"
                                variant="destructive"
                                size="lg"
                            >
                                <Square class="h-4 w-4 mr-2" />
                                End Session
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Session Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Session Info</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-sm font-medium mb-1">Time Elapsed</div>
                            <div class="text-2xl font-bold">{{ formatTime(timeElapsed) }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium mb-1">Questions</div>
                            <div class="text-2xl font-bold">
                                {{ questions.length - (questions.length - (questions.indexOf(currentQuestion || '') + 1)) }}/{{ questions.length }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-sm font-medium">Question Progress</div>
                            <div class="space-y-1">
                                <div
                                    v-for="(question, index) in questions"
                                    :key="index"
                                    class="text-xs p-2 rounded border"
                                    :class="{
                                        'bg-primary/10 border-primary': question === currentQuestion,
                                        'bg-muted': questions.indexOf(currentQuestion || '') > index,
                                        'opacity-50': questions.indexOf(currentQuestion || '') < index,
                                    }"
                                >
                                    Q{{ index + 1 }}: {{ question.substring(0, 40) }}...
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t space-y-2">
                            <div class="text-xs text-muted-foreground">
                                <strong>Instructions:</strong>
                            </div>
                            <ul class="text-xs text-muted-foreground space-y-1 list-disc list-inside">
                                <li>Listen carefully to each question</li>
                                <li>Speak or type your answer</li>
                                <li>Submit your answer before moving to the next question</li>
                                <li>You can review your answers before submission</li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

