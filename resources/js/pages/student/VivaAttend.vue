<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    FileText,
    Mic,
    MicOff,
    Square,
    Upload,
    Volume2,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    viva?: {
        id: number;
        title: string;
        description?: string;
        instructions?: string;
        scheduled_at: string;
        lecturer: string;
    };
    submission?: {
        id: number;
        document_path?: string;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Viva Sessions', href: '/student/vivas' },
    { title: 'Attend Viva', href: '#' },
];

interface VivaSession {
    id: number;
    title: string;
    description?: string;
    instructions?: string;
    scheduled_at: string;
    lecturer: string;
}

const vivaSession = computed<VivaSession>(() => {
    if (props.viva) {
        return props.viva;
    }
    // Fallback for backward compatibility
    return {
        id: 0,
        title: 'Viva Session',
        lecturer: 'Lecturer',
        scheduled_at: '',
    };
});

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
const answers = ref<
    Array<{
        question: string;
        answer: string;
        evaluation?: {
            score_1_10: number;
            feedback: string;
            correctPoints?: string[];
            improvements?: string[];
        };
    }>
>([]);
const currentEvaluation = ref<any>(null);
const showEvaluation = ref(false);
const conversationHistory = ref<Array<{ examiner: string; student: string }>>(
    [],
);
const isProcessingAnswer = ref(false);

const page = usePage();
const csrfToken = computed(() => (page.props as any).csrfToken || '');

// Document upload state
const documentFile = ref<File | null>(null);
const documentInputRef = ref<HTMLInputElement | null>(null);
const isUploadingDocument = ref(false);
const uploadedDocumentPath = ref<string | null>(
    props.submission?.document_path || null,
);
const documentUploaded = ref(!!props.submission?.document_path);

let timerInterval: ReturnType<typeof setInterval> | null = null;
let currentAudio: HTMLAudioElement | null = null;
let silenceTimer: ReturnType<typeof setTimeout> | null = null;
let finalAnswer = '';

const handleDocumentSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large. Maximum size is 10MB.');
            return;
        }
        // Check file type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Please upload PDF or Word document.');
            return;
        }
        documentFile.value = file;
    }
};

const uploadDocument = async () => {
    if (!documentFile.value || !vivaSession.value.id) {
        return;
    }

    isUploadingDocument.value = true;

    try {
        const formData = new FormData();
        formData.append('document', documentFile.value);

        const response = await fetch(
            `/student/vivas/${vivaSession.value.id}/upload-document`,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.value,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: formData,
            },
        );

        if (!response.ok) {
            const errorData = await response
                .json()
                .catch(() => ({ error: 'Failed to upload document' }));
            throw new Error(errorData.error || 'Failed to upload document');
        }

        const data = await response.json();
        uploadedDocumentPath.value = data.document_path;
        documentUploaded.value = true;
        documentFile.value = null;
        if (documentInputRef.value) {
            documentInputRef.value.value = '';
        }
        alert(
            'Document uploaded successfully! You can now start the viva session.',
        );
    } catch (error: any) {
        alert(`Error uploading document: ${error.message}`);
    } finally {
        isUploadingDocument.value = false;
    }
};

const generateQuestions = async () => {
    // Check if document is uploaded
    if (!documentUploaded.value && !uploadedDocumentPath.value) {
        alert('Please upload your document before starting the viva session.');
        return false;
    }

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
                vivaId: vivaSession.value.id,
                topic: vivaSession.value.title,
                description:
                    (vivaSession.value.description || vivaSession.value.title) +
                    ' viva session',
                numQuestions: 5,
                difficulty: 'intermediate',
                studentDocumentPath: uploadedDocumentPath.value,
            }),
        });

        if (!response.ok) {
            const errorData = await response
                .json()
                .catch(() => ({ error: 'Failed to generate questions' }));
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
        alert(
            `Error generating questions: ${error.message}\n\nPlease try again or contact support.`,
        );
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

// Google Cloud Text-to-Speech: speak text and return a Promise that resolves when playback ends (so we can chain TTS without overlap).
// When startRecordingWhenDone is false, we do not start recording in onended (used for feedback before moving to next question).
const speakAndWait = (
    text: string,
    startRecordingWhenDone: boolean = false,
): Promise<void> => {
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
    }
    isListening.value = true;
    isSpeaking.value = true;

    return fetch('/api/viva/tts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.value,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'audio/wav',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            text,
            languageCode: 'en-us',
            voiceName: 'Achernar',
            modelName: 'gemini-2.5-flash-lite-preview-tts',
            prompt: 'Read aloud in a warm, welcoming tone.',
            audioEncoding: 'LINEAR16',
            speakingRate: 1,
            pitch: 0,
        }),
    })
        .then(async (response) => {
            if (!response.ok) {
                const errorData = await response
                    .json()
                    .catch(() => ({ error: 'Unknown error' }));
                throw new Error(
                    errorData.error ||
                        `Failed to generate speech: ${response.status} ${response.statusText}`,
                );
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('audio')) {
                await response.text();
                throw new Error('Server did not return audio content');
            }
            const audioBlob = await response.blob();
            if (audioBlob.size === 0) {
                throw new Error('Received empty audio file');
            }
            const audioUrl = URL.createObjectURL(audioBlob);
            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
            }
            const audio = new Audio(audioUrl);
            currentAudio = audio;

            return new Promise<void>((resolve, reject) => {
                audio.onplay = () => {
                    isListening.value = true;
                    isSpeaking.value = true;
                    if (speechRecognition.value && recognitionActive.value) {
                        speechRecognition.value.stop();
                    }
                };
                audio.onended = () => {
                    isListening.value = false;
                    isSpeaking.value = false;
                    URL.revokeObjectURL(audioUrl);
                    currentAudio = null;
                    if (
                        startRecordingWhenDone &&
                        sessionActive.value &&
                        !isProcessingAnswer.value
                    ) {
                        startRecording();
                    }
                    resolve();
                };
                audio.onerror = () => {
                    isListening.value = false;
                    isSpeaking.value = false;
                    URL.revokeObjectURL(audioUrl);
                    currentAudio = null;
                    reject(new Error('Error playing audio'));
                };
                audio.play().catch(reject);
            });
        })
        .catch((error: any) => {
            isListening.value = false;
            isSpeaking.value = false;
            throw error;
        });
};

// Speak question and start recording when playback ends.
const speakQuestion = async (question: string) => {
    try {
        await speakAndWait(question, true);
    } catch (error: any) {
        const errorMsg = error.message || 'Unknown error';
        const is403Error =
            errorMsg.includes('403') ||
            errorMsg.includes('access denied') ||
            errorMsg.includes('blocked');
        const isVertexAIError =
            errorMsg.includes('Vertex AI') || errorMsg.includes('aiplatform');

        if (is403Error && isVertexAIError) {
            const urlMatch = errorMsg.match(/https:\/\/[^\s]+/);
            const activationUrl = urlMatch ? urlMatch[0] : null;
            let message = `Vertex AI API Required\n\n${errorMsg}\n\nTo fix this:\n1. Go to Google Cloud Console\n2. Enable "Vertex AI API" (required for Gemini TTS model)\n3. Also enable "Cloud Text-to-Speech API"\n4. Wait a few minutes for changes to propagate\n5. Ensure billing is enabled`;
            if (activationUrl) {
                message += `\n\nOr click here to enable directly:\n${activationUrl}`;
            }
            message += `\n\nYou can still read the question and type your answer.`;
            alert(message);
        } else if (is403Error) {
            alert(
                `Google TTS API Access Denied\n\n${errorMsg}\n\nTo fix this:\n1. Go to Google Cloud Console\n2. Enable "Cloud Text-to-Speech API"\n3. Enable "Vertex AI API" (required for Gemini models)\n4. Ensure your API key has the correct permissions\n5. Check that billing is enabled for your project\n\nYou can still read the question and type your answer.`,
            );
        } else {
            alert(
                `Error generating speech: ${errorMsg}\n\nPlease check:\n1. Google TTS API key is configured in .env\n2. API key has proper permissions\n3. Check browser console for details\n\nYou can still read the question and type your answer.`,
            );
        }
        startRecording();
    }
};

// Removed fallback to browser TTS - we only use Google TTS API
// If Google TTS fails, user can still read the question and type their answer

// Initialize Web Speech Recognition API with continuous mode
const initializeSpeechRecognition = () => {
    // Check if browser supports speech recognition
    const SpeechRecognition =
        (window as any).SpeechRecognition ||
        (window as any).webkitSpeechRecognition;

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
        if (
            (interimTranscript.trim() || finalTranscript.trim()) &&
            !isSpeaking.value &&
            sessionActive.value
        ) {
            silenceTimer = setTimeout(() => {
                // Student stopped speaking for 5 seconds - process the answer
                // Double-check conditions before processing
                if (
                    finalAnswer.trim() &&
                    !isProcessingAnswer.value &&
                    !isSpeaking.value &&
                    sessionActive.value
                ) {
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
        if (
            sessionActive.value &&
            !isSpeaking.value &&
            !isProcessingAnswer.value
        ) {
            setTimeout(() => {
                // Double-check AI is still not speaking before restarting
                if (
                    sessionActive.value &&
                    !isSpeaking.value &&
                    !isProcessingAnswer.value
                ) {
                    try {
                        recognition.start();
                    } catch {
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
        } catch {
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
                topic: vivaSession.value.title,
            }),
        });

        if (!response.ok) {
            const errorData = await response
                .json()
                .catch(() => ({ error: 'Failed to evaluate answer' }));
            throw new Error(errorData.error || 'Failed to evaluate answer');
        }

        const evaluation = await response.json();
        return evaluation;
    } catch {
        return {
            score_1_10: 5,
            feedback:
                'Could not evaluate answer automatically. Please review manually.',
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

    // Evaluate answer using Gemini AI (only if not skipped). score_1_10 is stored, not shown to user.
    let evaluation: {
        score_1_10: number;
        feedback: string;
        correctPoints?: string[];
        improvements?: string[];
    };
    const isLastQuestion = questionIndex.value >= questions.value.length - 1;
    const skipFeedbackMessage = isLastQuestion
        ? "That's okay — no problem at all. That was the last question. We're all done."
        : "That's okay — no problem at all. Let's move on to the next question.";

    if (isSkipped) {
        evaluation = {
            score_1_10: 1,
            feedback: skipFeedbackMessage,
            correctPoints: [],
            improvements: [],
        };
    } else {
        evaluation = await evaluateAnswer(currentQuestion.value!, answerText);
    }

    // Store answer and evaluation (score_1_10 kept for backend, not displayed)
    answers.value.push({
        question: currentQuestion.value!,
        answer: answerText,
        evaluation,
    });

    // Show evaluation
    currentEvaluation.value = evaluation;

    // Reset conversation history for next question
    conversationHistory.value = [];
    finalAnswer = '';
    answer.value = '';

    // Build message to speak: for skip use supportive message; for answer tell whether wrong or acceptable, then move on (or "we're done" on last question).
    const score = evaluation.score_1_10 ?? 5;
    const acceptableThreshold = 6;
    const nextPhrase = isLastQuestion
        ? " We're all done."
        : " Let's move to the next question.";
    const feedbackToSpeak = isSkipped
        ? skipFeedbackMessage
        : score >= acceptableThreshold
          ? `That's acceptable, well done.${nextPhrase}`
          : `That's not quite right.${nextPhrase}`;

    // Speak feedback via TTS and only move to next question after playback completes (no overlap).
    try {
        await speakAndWait(feedbackToSpeak, false);
    } catch {
        // If TTS fails, still move on after a short delay
    }
    moveToNextQuestion();
};

// Complete viva submission: send 5 answers with score_1_10 to backend; backend calls Python rubric service and returns rubric score.
const completeAndShowRubric = async () => {
    const submissionId = props.submission?.id;
    if (!submissionId || answers.value.length !== 5) {
        alert('Viva session completed. Your answers have been recorded.');
        return;
    }
    const payload = {
        submission_id: submissionId,
        answers: answers.value.map((a) => ({
            question: a.question,
            answer: a.answer,
            score_1_10: a.evaluation?.score_1_10 ?? 5,
            feedback: a.evaluation?.feedback ?? '',
            correctPoints: a.evaluation?.correctPoints ?? [],
            improvements: a.evaluation?.improvements ?? [],
        })),
    };
    try {
        const response = await fetch('/student/vivas/complete-submission', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });
        const data = await response.json().catch(() => ({}));
        if (response.ok && data.success) {
            const rubricScore =
                data.rubric_score != null ? data.rubric_score : '—';
            alert(
                `Viva session completed!\n\nYour answers have been saved.${data.rubric_from_service ? `\n\nRubric-based score: ${rubricScore}` : '\n\nScore has been recorded based on your answers.'}`,
            );
        } else {
            alert('Viva session completed. Your answers have been saved.');
        }
    } catch {
        alert('Viva session completed. Your answers have been saved.');
    }
};

const moveToNextQuestion = async () => {
    questionIndex.value++;
    showEvaluation.value = false;
    currentEvaluation.value = null;
    isProcessingAnswer.value = false;

    if (questionIndex.value < questions.value.length) {
        currentQuestion.value = questions.value[questionIndex.value];
        // Speak the next question
        speakQuestion(currentQuestion.value);
    } else {
        // All questions answered: save submission and get rubric score from Python service
        stopSession();
        await completeAndShowRubric();
    }
};

// Check if answer indicates skip / don't know (client-side)
const isSkipAnswer = (text: string): boolean => {
    const n = text.trim().toLowerCase();
    return [
        "i don't know",
        'i do not know',
        "don't know",
        'skip',
        'pass',
        'idk',
        'no answer',
        'not sure',
    ].some((p) => n.includes(p));
};

// Process student's answer: evaluate once (mark 1-10), no follow-up questions, then move on.
const processAnswer = async () => {
    if (
        !currentQuestion.value ||
        !finalAnswer.trim() ||
        isProcessingAnswer.value ||
        isSpeaking.value
    ) {
        return;
    }

    if (silenceTimer) {
        clearTimeout(silenceTimer);
        silenceTimer = null;
    }

    const answerText = finalAnswer.trim();
    if (speechRecognition.value && recognitionActive.value) {
        speechRecognition.value.stop();
    }

    const skipped = isSkipAnswer(answerText);
    await evaluateAndMoveOn(answerText, skipped);
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
        <div class="viva-voice-agent flex min-h-[calc(100vh-8rem)] flex-col">
            <!-- Minimal top bar -->
            <header
                class="flex shrink-0 items-center justify-between border-b bg-background/95 px-4 py-3 backdrop-blur"
            >
                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-semibold tracking-tight">
                        {{ vivaSession.title }}
                    </h1>
                    <span
                        v-if="sessionActive"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                        ></span>
                        Live
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <template v-if="sessionActive">
                        <span
                            class="font-mono text-sm tabular-nums text-muted-foreground"
                        >
                            {{ formatTime(timeElapsed) }}
                        </span>
                        <span class="text-sm text-muted-foreground">
                            {{ questionIndex + 1 }}/{{ questions.length }}
                        </span>
                        <Button
                            @click="stopSession"
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground hover:text-destructive"
                        >
                            <Square class="mr-1.5 h-4 w-4" />
                            End
                        </Button>
                    </template>
                </div>
            </header>

            <!-- Document upload gate (shown when no document) -->
            <div
                v-if="!documentUploaded"
                class="flex flex-1 flex-col items-center justify-center gap-6 px-4 py-12"
            >
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-muted"
                >
                    <FileText class="h-10 w-10 text-muted-foreground" />
                </div>
                <div class="max-w-sm text-center">
                    <h2 class="text-lg font-medium">Upload your document</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        PDF or Word, max 10MB. Required before starting the
                        viva.
                    </p>
                </div>
                <div class="flex w-full max-w-sm flex-col gap-2">
                    <Input
                        ref="documentInputRef"
                        id="document"
                        type="file"
                        accept=".pdf,.doc,.docx"
                        @change="handleDocumentSelect"
                        class="cursor-pointer"
                    />
                    <Button
                        @click="uploadDocument"
                        :disabled="!documentFile || isUploadingDocument"
                    >
                        <Upload class="mr-2 h-4 w-4" />
                        {{ isUploadingDocument ? 'Uploading...' : 'Upload' }}
                    </Button>
                </div>
            </div>

            <!-- Ready to start (document uploaded, session not started) -->
            <div
                v-else-if="!sessionActive"
                class="flex flex-1 flex-col items-center justify-center gap-8 px-4 py-12"
            >
                <div
                    class="flex max-w-md flex-col items-center gap-6 rounded-2xl border bg-card p-8 text-center shadow-sm"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10"
                    >
                        <Mic class="h-8 w-8 text-primary" />
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-xl font-semibold">
                            Ready for your viva
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Your document is uploaded. You’ll get 5 questions
                            and can answer by voice. Say “I don’t know” or
                            “Skip” to move on.
                        </p>
                    </div>
                    <ul
                        class="w-full space-y-2 text-left text-sm text-muted-foreground"
                    >
                        <li class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium"
                                >1</span
                            >
                            Listen to each question (read aloud)
                        </li>
                        <li class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium"
                                >2</span
                            >
                            Speak your answer — we’ll detect when you finish
                        </li>
                        <li class="flex items-center gap-2">
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium"
                                >3</span
                            >
                            Get feedback and move to the next question
                        </li>
                    </ul>
                    <Button
                        size="lg"
                        class="w-full"
                        :disabled="isLoadingQuestions"
                        @click="startSession"
                    >
                        <Mic class="mr-2 h-5 w-5" />
                        <span v-if="isLoadingQuestions"
                            >Generating questions...</span
                        >
                        <span v-else>Start viva</span>
                    </Button>
                </div>
            </div>

            <!-- Voice agent main view (session active) -->
            <template v-else>
                <!-- Center: orb + status -->
                <div
                    class="flex flex-1 flex-col items-center justify-center gap-8 px-4 py-8"
                >
                    <div
                        class="viva-orb"
                        :class="{
                            'viva-orb--speaking': isSpeaking || isListening,
                            'viva-orb--listening': isRecording && !isSpeaking && !isListening,
                            'viva-orb--idle':
                                !isRecording && !isSpeaking && !isListening,
                        }"
                    >
                        <Volume2
                            v-if="isSpeaking || isListening"
                            class="h-10 w-10 text-primary"
                        />
                        <Mic
                            v-else-if="isRecording"
                            class="h-10 w-10 text-emerald-500"
                        />
                        <MicOff
                            v-else
                            class="h-10 w-10 text-muted-foreground"
                        />
                    </div>

                    <!-- Status line -->
                    <p
                        class="max-w-md text-center text-sm text-muted-foreground"
                    >
                        <span v-if="isSpeaking">Listening to question...</span>
                        <span v-else-if="isProcessingAnswer"
                            >Evaluating your answer...</span
                        >
                        <span v-else-if="isRecording"
                            >Speak now. We’ll move on after you
                            finish.</span
                        >
                        <span v-else>Ready for your answer.</span>
                    </p>

                    <!-- Inline evaluation (compact) -->
                    <div
                        v-if="showEvaluation && currentEvaluation"
                        class="w-full max-w-md rounded-xl border bg-card px-4 py-3 text-left shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-muted-foreground"
                                >Feedback</span
                            >
                            <Badge variant="secondary" class="text-xs">
                                {{ currentEvaluation.score_1_10 }}/10
                            </Badge>
                        </div>
                        <p class="mt-2 text-sm">
                            {{ currentEvaluation.feedback }}
                        </p>
                    </div>
                </div>

                <!-- Transcript strip (conversation-style) -->
                <div
                    v-if="currentQuestion || answer.trim()"
                    class="shrink-0 border-t bg-muted/30 px-4 py-4"
                >
                    <div class="mx-auto flex max-w-2xl flex-col gap-3">
                        <div
                            v-if="currentQuestion"
                            class="flex gap-3"
                        >
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10"
                            >
                                <Volume2 class="h-4 w-4 text-primary" />
                            </div>
                            <div class="min-w-0 flex-1 rounded-2xl rounded-tl-sm bg-background px-4 py-2.5 text-sm shadow-sm">
                                {{ currentQuestion }}
                            </div>
                        </div>
                        <div
                            v-if="answer.trim()"
                            class="flex gap-3 justify-end"
                        >
                            <div class="min-w-0 max-w-[85%] rounded-2xl rounded-tr-sm bg-primary px-4 py-2.5 text-sm text-primary-foreground">
                                {{ answer || '...' }}
                            </div>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary"
                            >
                                <Mic class="h-4 w-4 text-primary-foreground" />
                            </div>
                        </div>
                    </div>
                    <!-- Manual submit fallback -->
                    <div class="mx-auto mt-3 flex max-w-2xl justify-end">
                        <Button
                            @click="processAnswer"
                            :disabled="
                                !answer.trim() ||
                                showEvaluation ||
                                isProcessingAnswer ||
                                isSpeaking
                            "
                            variant="ghost"
                            size="sm"
                            class="text-xs text-muted-foreground"
                        >
                            Submit answer
                        </Button>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<style scoped>
.viva-orb {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 7rem;
    width: 7rem;
    border-radius: 9999px;
    border-width: 2px;
    transition: all 0.3s ease-out;
}
.viva-orb--idle {
    border-color: var(--muted);
    background-color: color-mix(in srgb, var(--muted) 50%, transparent);
}
.viva-orb--idle:hover {
    border-color: color-mix(in srgb, var(--muted-foreground) 30%, transparent);
    background-color: color-mix(in srgb, var(--muted) 70%, transparent);
}
.viva-orb--speaking {
    border-color: var(--primary);
    background-color: color-mix(in srgb, var(--primary) 10%, transparent);
    box-shadow: 0 10px 15px -3px color-mix(in srgb, var(--primary) 20%, transparent);
    animation: viva-pulse 1.5s ease-in-out infinite;
}
.viva-orb--listening {
    border-color: rgb(16 185 129);
    background-color: rgb(16 185 129 / 0.1);
    box-shadow: 0 10px 15px -3px rgb(16 185 129 / 0.2);
    animation: viva-pulse 1.2s ease-in-out infinite;
}
@keyframes viva-pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.85;
        transform: scale(1.08);
    }
}
</style>
