<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Mic, MicOff, Volume2, Pause, Play, Square } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

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
const isSpeaking = ref(false);
const speechRecognition: any = ref(null);
const recognitionActive = ref(false);
const isLoadingQuestions = ref(false);
const questionIndex = ref(0);
const answers = ref<Array<{question: string, answer: string, evaluation?: any}>>([]);
const currentEvaluation = ref<any>(null);
const showEvaluation = ref(false);
const conversationHistory = ref<Array<{examiner: string, student: string}>>([]);
const isProcessingAnswer = ref(false);
const currentConversationResponse = ref<string | null>(null);

const page = usePage();
const csrfToken = computed(() => (page.props as any).csrfToken || '');

let timerInterval: ReturnType<typeof setInterval> | null = null;
let currentAudio: HTMLAudioElement | null = null;
let silenceTimer: ReturnType<typeof setTimeout> | null = null;
let finalAnswer = '';

const generateQuestions = async () => {
    isLoadingQuestions.value = true;

    try {
        const response = await fetch('/api/viva/questions/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                topic: vivaSession.title,
                description: vivaSession.title + ' viva session',
                numQuestions: 5,
                difficulty: 'intermediate',
            }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Failed to generate questions' }));
            throw new Error(errorData.error || 'Failed to generate questions');
        }

        const data = await response.json();
        questions.value = data.questions || [];

        if (questions.value.length === 0) {
            throw new Error('No questions generated');
        }

    questionIndex.value = 0;
    currentQuestion.value = questions.value[0];
    answers.value = [];
    conversationHistory.value = [];
    finalAnswer = '';
    answer.value = '';

    return true;
    } catch (error: any) {
        alert(`Error generating questions: ${error.message}\n\nPlease try again or contact support.`);
        return false;
    } finally {
        isLoadingQuestions.value = false;
    }
};

const startSession = async () => {
    // Generate questions using Gemini AI
    const questionsGenerated = await generateQuestions();

    if (!questionsGenerated) {
        return;
    }

    sessionActive.value = true;
    timeElapsed.value = 0;
    showEvaluation.value = false;
    currentEvaluation.value = null;
    conversationHistory.value = [];
    finalAnswer = '';
    answer.value = '';
    isProcessingAnswer.value = false;
    currentConversationResponse.value = null;

    // Start timer
    timerInterval = setInterval(() => {
        timeElapsed.value++;
    }, 1000);

    // Start with first question
    speakQuestion(currentQuestion.value!);
};

const stopSession = () => {
    sessionActive.value = false;
    stopRecording();
    isListening.value = false;
    isSpeaking.value = false;
    isProcessingAnswer.value = false;

    // Stop any ongoing Google TTS audio
    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }

    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }

    if (silenceTimer) {
        clearTimeout(silenceTimer);
        silenceTimer = null;
    }
};

// Google Cloud Text-to-Speech API Integration
const speakQuestion = async (question: string) => {
    // Stop recording before AI starts speaking
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
    }

    isListening.value = true;
    isSpeaking.value = true;

    try {
        // Call backend endpoint that uses Google Cloud TTS API
        // The backend will handle the Google TTS API call and return audio
        const response = await fetch('/api/viva/tts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'audio/wav',
            },
            credentials: 'same-origin', // Important: include cookies for CSRF
            body: JSON.stringify({
                text: question,
                languageCode: 'en-us',
                voiceName: 'Achernar',
                modelName: 'gemini-2.5-flash-lite-preview-tts',
                prompt: 'Read aloud in a warm, welcoming tone.',
                audioEncoding: 'LINEAR16',
                speakingRate: 1,
                pitch: 0,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Unknown error' }));

            // Extract user-friendly error message
            const errorMessage = errorData.error || `Failed to generate speech: ${response.status} ${response.statusText}`;
            const errorCode = errorData.code || response.status;

            throw new Error(errorMessage);
        }

        // Check if response is actually audio
        const contentType = response.headers.get('content-type');

        if (!contentType || !contentType.includes('audio')) {
            const errorText = await response.text();
            throw new Error('Server did not return audio content');
        }

        // Get audio blob from response
        const audioBlob = await response.blob();

        if (audioBlob.size === 0) {
            throw new Error('Received empty audio file');
        }

        const audioUrl = URL.createObjectURL(audioBlob);

        // Stop any currently playing audio
        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
        }

        // Create audio element and play
        const audio = new Audio(audioUrl);
        currentAudio = audio;

        audio.onplay = () => {
            isListening.value = true;
            isSpeaking.value = true;
            // Stop recording when AI starts speaking to prevent capturing AI's voice
            if (speechRecognition.value && recognitionActive.value) {
                speechRecognition.value.stop();
            }
        };

        audio.onended = () => {
            isListening.value = false;
            isSpeaking.value = false;
            // Clean up the object URL
            URL.revokeObjectURL(audioUrl);
            currentAudio = null;
            // Start recording after question/prompt is spoken (if session is active and not processing)
            if (sessionActive.value && !isProcessingAnswer.value) {
                startRecording();
            }
        };

        audio.onerror = () => {
            isListening.value = false;
            isSpeaking.value = false;
            URL.revokeObjectURL(audioUrl);
            currentAudio = null;
            alert('Error playing audio. Please try again.');
            startRecording(); // Allow manual input
        };

        // Play the audio
        await audio.play();

    } catch (error: any) {
        isListening.value = false;
        isSpeaking.value = false;

        // Show detailed error to user
        const errorMsg = error.message || 'Unknown error';
        const is403Error = errorMsg.includes('403') || errorMsg.includes('access denied') || errorMsg.includes('blocked');
        const isVertexAIError = errorMsg.includes('Vertex AI') || errorMsg.includes('aiplatform');

        if (is403Error && isVertexAIError) {
            // Extract activation URL from error if available
            const urlMatch = errorMsg.match(/https:\/\/[^\s]+/);
            const activationUrl = urlMatch ? urlMatch[0] : null;

            let message = `Vertex AI API Required\n\n${errorMsg}\n\nTo fix this:\n1. Go to Google Cloud Console\n2. Enable "Vertex AI API" (required for Gemini TTS model)\n3. Also enable "Cloud Text-to-Speech API"\n4. Wait a few minutes for changes to propagate\n5. Ensure billing is enabled`;

            if (activationUrl) {
                message += `\n\nOr click here to enable directly:\n${activationUrl}`;
            }

            message += `\n\nYou can still read the question and type your answer.`;

            alert(message);
        } else if (is403Error) {
            alert(`Google TTS API Access Denied\n\n${errorMsg}\n\nTo fix this:\n1. Go to Google Cloud Console\n2. Enable "Cloud Text-to-Speech API"\n3. Enable "Vertex AI API" (required for Gemini models)\n4. Ensure your API key has the correct permissions\n5. Check that billing is enabled for your project\n\nYou can still read the question and type your answer.`);
        } else {
            alert(`Error generating speech: ${errorMsg}\n\nPlease check:\n1. Google TTS API key is configured in .env\n2. API key has proper permissions\n3. Check browser console for details\n\nYou can still read the question and type your answer.`);
        }

        // Don't use fallback - just allow manual input
        // User can still type their answer
        startRecording();
    }
};

// Removed fallback to browser TTS - we only use Google TTS API
// If Google TTS fails, user can still read the question and type their answer

// Initialize Web Speech Recognition API with continuous mode
const initializeSpeechRecognition = () => {
    // Check if browser supports speech recognition
    const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;

    if (!SpeechRecognition) {
        return null;
    }

    const recognition = new SpeechRecognition();
    recognition.continuous = true; // Continuous mode for conversation
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    recognition.onstart = () => {
        recognitionActive.value = true;
        isRecording.value = true;
        finalAnswer = '';
    };

    recognition.onresult = (event: any) => {
        let interimTranscript = '';
        let finalTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript + ' ';
            } else {
                interimTranscript += transcript;
            }
        }

        // Update answer display with interim results
        if (interimTranscript) {
            answer.value = (finalAnswer + ' ' + interimTranscript).trim();
        }

        // Accumulate final transcripts
        if (finalTranscript) {
            finalAnswer = (finalAnswer + ' ' + finalTranscript).trim();
            answer.value = finalAnswer;
        }

        // Reset silence timer whenever speech is detected (both interim and final)
        // This ensures the timer resets if student continues speaking after a pause
        if (silenceTimer) {
            clearTimeout(silenceTimer);
            silenceTimer = null;
        }

        // Only set timer if we have some content and AI is not speaking
        // Wait 5 seconds of silence before processing the answer
        if ((interimTranscript.trim() || finalTranscript.trim()) && !isSpeaking.value && sessionActive.value) {
            silenceTimer = setTimeout(() => {
                // Student stopped speaking for 5 seconds - process the answer
                // Double-check conditions before processing
                if (finalAnswer.trim() && !isProcessingAnswer.value && !isSpeaking.value && sessionActive.value) {
                    processAnswer();
                }
            }, 5000); // Wait 5 seconds of silence before processing
        }
    };

    recognition.onerror = (event: any) => {
        console.error('Speech recognition error:', event.error);
        if (event.error === 'no-speech') {
            // No speech detected - restart recognition
            if (recognitionActive.value && sessionActive.value) {
                setTimeout(() => {
                    if (recognitionActive.value && sessionActive.value) {
                        recognition.start();
                    }
                }, 500);
            }
        } else {
            isRecording.value = false;
            recognitionActive.value = false;
        }
    };

    recognition.onend = () => {
        // Auto-restart ONLY if AI is not speaking and session is active
        if (sessionActive.value && !isSpeaking.value && !isProcessingAnswer.value) {
            setTimeout(() => {
                // Double-check AI is still not speaking before restarting
                if (sessionActive.value && !isSpeaking.value && !isProcessingAnswer.value) {
                    try {
                        recognition.start();
                    } catch (e) {
                        // Recognition might already be running
                    }
                }
            }, 100);
        } else {
            recognitionActive.value = false;
            isRecording.value = false;
        }
    };

    return recognition;
};

// Start recording student's speech (continuous mode)
const startRecording = () => {
    // Don't start recording if AI is speaking
    if (isSpeaking.value) {
        return;
    }

    if (!speechRecognition.value) {
        speechRecognition.value = initializeSpeechRecognition();
    }

    if (speechRecognition.value) {
        try {
            if (!recognitionActive.value && !isSpeaking.value) {
                speechRecognition.value.start();
                isRecording.value = true;
            }
        } catch (error) {
            // If recognition is already running, that's fine
            console.log('Recognition already active');
        }
    } else {
        // Fallback: manual text input only
        isRecording.value = true;
    }
};

// Stop recording
const stopRecording = () => {
    if (silenceTimer) {
        clearTimeout(silenceTimer);
        silenceTimer = null;
    }
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
    }
    isRecording.value = false;
    recognitionActive.value = false;
};

// Evaluate answer using Gemini AI
const evaluateAnswer = async (question: string, answerText: string) => {
    try {
        const response = await fetch('/api/viva/answer/evaluate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                question: question,
                answer: answerText,
                topic: vivaSession.title,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Failed to evaluate answer' }));
            throw new Error(errorData.error || 'Failed to evaluate answer');
        }

        const evaluation = await response.json();
        return evaluation;
    } catch (error: any) {
        // Return a default evaluation if API fails
        return {
            score: 0,
            feedback: 'Could not evaluate answer automatically. Please review manually.',
            correctPoints: [],
            improvements: [],
        };
    }
};

// Evaluate answer and move to next question
const evaluateAndMoveOn = async (answerText: string, isSkipped: boolean) => {
    // Stop recording
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
    }

    // Show loading state
    showEvaluation.value = true;
    isProcessingAnswer.value = true;

    // Evaluate answer using Gemini AI (only if not skipped)
    let evaluation;
    if (isSkipped) {
        evaluation = {
            score: 0,
            feedback: 'Question was skipped or student indicated they do not know the answer.',
            correctPoints: [],
            improvements: [],
        };
    } else {
        evaluation = await evaluateAnswer(currentQuestion.value!, answerText);
    }

    // Store answer and evaluation
    answers.value.push({
        question: currentQuestion.value!,
        answer: answerText,
        evaluation: evaluation,
    });

    // Show evaluation
    currentEvaluation.value = evaluation;

    // Reset conversation history for next question
    conversationHistory.value = [];
    finalAnswer = '';
    answer.value = '';

    // Move to next question after showing evaluation
    setTimeout(() => {
        moveToNextQuestion();
    }, 3000);
};

const moveToNextQuestion = () => {
    questionIndex.value++;
    showEvaluation.value = false;
    currentEvaluation.value = null;
    isProcessingAnswer.value = false;

    if (questionIndex.value < questions.value.length) {
        currentQuestion.value = questions.value[questionIndex.value];
        // Speak the next question
        speakQuestion(currentQuestion.value);
    } else {
        // All questions answered
        stopSession();

        // Calculate total score
        const totalScore = answers.value.reduce((sum, item) => sum + (item.evaluation?.score || 0), 0);
        const averageScore = Math.round(totalScore / answers.value.length);

        alert(`Viva session completed!\n\nAverage Score: ${averageScore}/100\n\nYour answers have been evaluated and saved.`);
    }
};

// Generate conversational response using AI
const generateConversationalResponse = async (question: string, studentAnswer: string): Promise<any> => {
    try {
        const response = await fetch('/api/viva/conversation/response', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                question: question,
                studentAnswer: studentAnswer,
                topic: vivaSession.title,
                conversationHistory: conversationHistory.value,
                attemptNumber: conversationHistory.value.length + 1,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Failed to generate response' }));
            throw new Error(errorData.error || 'Failed to generate conversational response');
        }

        return await response.json();
    } catch (error: any) {
        // Fallback response
        const normalized = studentAnswer.trim().toLowerCase();
        const isSkip = normalized.includes("don't know") || normalized.includes("skip") || normalized.includes("pass") || normalized.includes("idk");
        const isValid = studentAnswer.trim().length >= 10 && !isSkip;

        return {
            response: isSkip
                ? 'Thank you. Let\'s move on to the next question.'
                : (isValid
                    ? 'Thank you for your answer. Let\'s move to the next question.'
                    : 'Could you please elaborate on that? I need a bit more detail.'),
            shouldContinue: !isSkip && !isValid,
            isSkipped: isSkip,
        };
    }
};

// Process student's answer automatically
const processAnswer = async () => {
    // Don't process if AI is speaking or already processing
    if (!currentQuestion.value || !finalAnswer.trim() || isProcessingAnswer.value || isSpeaking.value) {
        return;
    }

    // Clear any pending silence timer
    if (silenceTimer) {
        clearTimeout(silenceTimer);
        silenceTimer = null;
    }

    isProcessingAnswer.value = true;
    const answerText = finalAnswer.trim();

    // Stop recording while processing
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
    }

    // Generate conversational response from AI examiner
    const conversationResult = await generateConversationalResponse(currentQuestion.value, answerText);

    // Add to conversation history
    conversationHistory.value.push({
        examiner: currentQuestion.value,
        student: answerText,
    });

    // Speak the AI examiner's response
    currentConversationResponse.value = conversationResult.response;
    await speakQuestion(conversationResult.response);

    // Clear the response display
    currentConversationResponse.value = null;

    // Check if we should continue or move to next question
    if (conversationResult.isSkipped || !conversationResult.shouldContinue) {
        // Student said they don't know or answer is satisfactory - evaluate and move on
        await evaluateAndMoveOn(answerText, conversationResult.isSkipped);
    } else {
        // Need more information - continue conversation
        // Add examiner's follow-up to history
        conversationHistory.value.push({
            examiner: conversationResult.response,
            student: '',
        });

        // Restart recording for next answer
        isProcessingAnswer.value = false;
        finalAnswer = '';
        answer.value = '';
        startRecording();
    }
};

const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

onMounted(() => {
    // Initialize speech recognition on component mount
    speechRecognition.value = initializeSpeechRecognition();
});

onUnmounted(() => {
    // Cleanup
    if (timerInterval) {
        clearInterval(timerInterval);
    }

    if (silenceTimer) {
        clearTimeout(silenceTimer);
    }

    // Stop any playing audio
    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }

    // Stop speech recognition
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
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
                                    'border-primary bg-primary/10 animate-pulse': isListening || isSpeaking,
                                    'border-muted': !isListening && !isRecording && !isSpeaking,
                                    'border-green-500 bg-green-500/10': isRecording && !isListening && !isSpeaking,
                                }"
                            >
                                <div
                                    v-if="isListening || isSpeaking"
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

                        <!-- Evaluation Result -->
                        <div v-if="showEvaluation && currentEvaluation" class="rounded-lg border-2 border-primary bg-primary/5 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">Answer Evaluation</div>
                                <Badge :variant="currentEvaluation.score >= 70 ? 'default' : currentEvaluation.score >= 50 ? 'secondary' : 'destructive'">
                                    Score: {{ currentEvaluation.score }}/100
                                </Badge>
                            </div>
                            <div class="text-sm text-muted-foreground">
                                {{ currentEvaluation.feedback }}
                            </div>
                            <div v-if="currentEvaluation.correctPoints && currentEvaluation.correctPoints.length > 0" class="space-y-1">
                                <div class="text-xs font-medium text-green-600">✓ Correct Points:</div>
                                <ul class="text-xs text-muted-foreground list-disc list-inside space-y-0.5">
                                    <li v-for="(point, idx) in currentEvaluation.correctPoints" :key="idx">{{ point }}</li>
                                </ul>
                            </div>
                            <div v-if="currentEvaluation.improvements && currentEvaluation.improvements.length > 0" class="space-y-1">
                                <div class="text-xs font-medium text-orange-600">⚠ Areas for Improvement:</div>
                                <ul class="text-xs text-muted-foreground list-disc list-inside space-y-0.5">
                                    <li v-for="(improvement, idx) in currentEvaluation.improvements" :key="idx">{{ improvement }}</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Current Question -->
                        <div v-if="currentQuestion" class="space-y-4">
                            <div class="rounded-lg bg-muted p-4">
                                <div class="text-sm font-medium mb-2">Current Question:</div>
                                <div class="text-lg">{{ currentQuestion }}</div>
                            </div>

                            <!-- AI Examiner Response -->
                            <div v-if="currentConversationResponse" class="rounded-lg border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/20 p-4">
                                <div class="text-sm font-medium text-blue-700 dark:text-blue-400 mb-1">AI Examiner:</div>
                                <div class="text-sm text-blue-600 dark:text-blue-300">{{ currentConversationResponse }}</div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium">Your Answer:</label>
                                    <div v-if="isRecording" class="flex items-center gap-2 text-xs text-green-600">
                                        <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                                        Recording...
                                    </div>
                                </div>
                                <textarea
                                    v-model="answer"
                                    class="w-full min-h-[120px] rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    placeholder="Type or speak your answer here... (Speech recognition will automatically transcribe your voice)"
                                    :disabled="isSpeaking"
                                />
                                <p class="text-xs text-muted-foreground">
                                    <span v-if="isSpeaking">Please wait while the examiner is speaking...</span>
                                    <span v-else-if="isProcessingAnswer">Processing your answer...</span>
                                    <span v-else-if="isRecording">Speak your answer now. The system will automatically detect when you finish speaking.</span>
                                    <span v-else>The AI examiner is listening. Speak naturally and your answer will be processed automatically.</span>
                                </p>
                            </div>

                            <!-- Manual controls (optional fallback) -->
                            <div class="flex gap-2">
                                <Button
                                    @click="processAnswer"
                                    :disabled="!answer.trim() || !sessionActive || showEvaluation || isProcessingAnswer || isSpeaking"
                                    class="flex-1"
                                    variant="outline"
                                >
                                    <span v-if="showEvaluation">Evaluating...</span>
                                    <span v-else-if="isProcessingAnswer">Processing...</span>
                                    <span v-else>Process Answer (Manual)</span>
                                </Button>
                            </div>
                            <p class="text-xs text-muted-foreground text-center mt-2">
                                💡 The system automatically processes your answer when you stop speaking. Use the button above only if needed.
                            </p>
                        </div>

                        <div v-else-if="isLoadingQuestions" class="text-center text-muted-foreground py-8">
                            <p>Generating questions using AI...</p>
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
                                :disabled="isLoadingQuestions"
                            >
                                <Play class="h-4 w-4 mr-2" />
                                <span v-if="isLoadingQuestions">Generating Questions...</span>
                                <span v-else>Start Session</span>
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
                                {{ questionIndex + 1 }}/{{ questions.length }}
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
                                        'bg-primary/10 border-primary': index === questionIndex,
                                        'bg-muted': questionIndex > index,
                                        'opacity-50': questionIndex < index,
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
                                <li>Listen carefully to each question from the AI examiner</li>
                                <li>Speak your answer naturally - the system will automatically detect when you finish</li>
                                <li>If you don't know, simply say "I don't know" and the examiner will move on</li>
                                <li>The AI examiner will ask follow-up questions if your answer needs more detail</li>
                                <li>This is a conversational viva - respond naturally as you would to a real examiner</li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

