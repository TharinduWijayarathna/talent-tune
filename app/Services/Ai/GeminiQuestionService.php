<?php

namespace App\Services\Ai;

use App\Models\Viva;
use Illuminate\Support\Facades\Http;

class GeminiQuestionService
{
    public function __construct(
        protected GeminiFileService $geminiFileService
    ) {}

    protected function getApiKey(): ?string
    {
        return config('services.google.gemini_api_key');
    }

    protected function getApiUrl(): string
    {
        $apiKey = $this->getApiKey();
        return "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
    }

    private const VIVA_QUESTION_COUNT = 5;

    public function generateQuestions(
        ?int $vivaId,
        string $topic,
        string $description = '',
        int $numQuestions = self::VIVA_QUESTION_COUNT,
        string $difficulty = 'intermediate',
        ?string $studentDocumentPath = null
    ): array {
        $numQuestions = min(self::VIVA_QUESTION_COUNT, max(1, $numQuestions));
        if (!$this->getApiKey()) {
            return ['error' => 'Gemini API key not configured', 'code' => 500];
        }

        $prompt = '';
        if ($vivaId) {
            $viva = Viva::find($vivaId);
            if ($viva && $viva->base_prompt) {
                $prompt = $viva->base_prompt;
                if ($studentDocumentPath && $viva->viva_background) {
                    $prompt = $this->geminiFileService->enhancePromptWithStudentDocument(
                        $studentDocumentPath,
                        $viva->viva_background,
                        $prompt,
                        $viva->title
                    );
                }
            } else {
                $prompt = "Generate {$numQuestions} viva questions for the topic: {$topic}";
                if ($description) {
                    $prompt .= "\n\nContext: {$description}";
                }
            }
        } else {
            $prompt = "Generate {$numQuestions} viva questions for the topic: {$topic}";
            if ($description) {
                $prompt .= "\n\nContext: {$description}";
            }
        }

        $prompt .= "\n\nDifficulty level: {$difficulty}";
        $prompt .= "\n\nRequirements:";
        $prompt .= "\n- Questions should be clear and concise";
        $prompt .= "\n- Questions should test understanding, not just memorization";
        $prompt .= "\n- Questions should progress from basic to more complex concepts";
        $prompt .= "\n- Return ONLY a JSON array of question strings, no additional text";
        $prompt .= "\n- Format: [\"question1\", \"question2\", \"question3\", ...]";

        $response = Http::post($this->getApiUrl(), [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ],
        ]);

        if ($response->failed()) {
            $errorData = $response->json();
            return [
                'error' => $errorData['error']['message'] ?? 'Failed to generate questions',
                'code' => $response->status(),
            ];
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (preg_match('/\[.*\]/s', $text, $matches)) {
            $questions = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($questions)) {
                $questions = array_slice($questions, 0, $numQuestions);
                return ['questions' => $questions, 'count' => count($questions)];
            }
        }

        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $questions = [];
        foreach ($lines as $line) {
            $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
            $line = preg_replace('/^[-*]\s*/', '', $line);
            $line = trim($line);
            if (!empty($line) && strlen($line) > 10) {
                $questions[] = $line;
            }
        }

        if (empty($questions)) {
            return ['error' => 'Could not parse questions from AI response', 'raw_response' => $text, 'code' => 500];
        }

        return [
            'questions' => array_slice($questions, 0, $numQuestions),
            'count' => min(count($questions), $numQuestions),
        ];
    }

    public function evaluateAnswer(string $question, string $answer, string $topic = ''): array
    {
        if (!$this->getApiKey()) {
            return ['error' => 'Gemini API key not configured', 'code' => 500];
        }

        $prompt = "Evaluate the following viva answer:\n\n";
        $prompt .= "Question: {$question}\n\n";
        $prompt .= "Student Answer: {$answer}\n\n";
        if ($topic) {
            $prompt .= "Topic: {$topic}\n\n";
        }
        $prompt .= "Please provide:\n";
        $prompt .= "1. A score from 1 to 10 (integer, 1=very poor, 10=excellent)\n";
        $prompt .= "2. Brief feedback (2-3 sentences) for the student\n";
        $prompt .= "3. Key points that were covered correctly\n";
        $prompt .= "4. Areas that need improvement (if any)\n\n";
        $prompt .= "Return your response as a JSON object with the following structure:\n";
        $prompt .= "{\n";
        $prompt .= '  "score_1_10": <integer 1-10>,\n';
        $prompt .= '  "feedback": "<brief feedback text>",\n';
        $prompt .= '  "correctPoints": ["point1", "point2", ...],\n';
        $prompt .= '  "improvements": ["area1", "area2", ...]\n';
        $prompt .= "}\n";
        $prompt .= "Return ONLY the JSON object, no additional text.";

        $response = Http::post($this->getApiUrl(), [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.3,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
        ]);

        if ($response->failed()) {
            $errorData = $response->json();
            return [
                'error' => $errorData['error']['message'] ?? 'Failed to evaluate answer',
                'code' => $response->status(),
            ];
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $evaluation = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($evaluation)) {
                $score1_10 = isset($evaluation['score_1_10']) ? (int) $evaluation['score_1_10'] : (isset($evaluation['score']) ? (int) round($evaluation['score'] / 10) : 5);
                $score1_10 = max(1, min(10, $score1_10));
                $evaluation['score_1_10'] = $score1_10;
                $evaluation['feedback'] = $evaluation['feedback'] ?? 'No feedback provided';
                $evaluation['correctPoints'] = $evaluation['correctPoints'] ?? [];
                $evaluation['improvements'] = $evaluation['improvements'] ?? [];
                return $evaluation;
            }
        }

        $score1_10 = 5;
        $feedback = 'Could not parse evaluation response';
        if (preg_match('/score[:\s]+(\d+)/i', $text, $scoreMatch)) {
            $score1_10 = (int) $scoreMatch[1];
            $score1_10 = max(1, min(10, $score1_10));
        }
        $sentences = preg_split('/[.!?]+/', $text);
        $feedback = trim($sentences[0] ?? $text);
        if (strlen($feedback) > 200) {
            $feedback = substr($feedback, 0, 200) . '...';
        }

        return [
            'score_1_10' => $score1_10,
            'feedback' => $feedback,
            'correctPoints' => [],
            'improvements' => [],
        ];
    }

    public function generateConversationalResponse(
        string $question,
        string $studentAnswer,
        string $topic = '',
        array $conversationHistory = []
    ): array {
        if (!$this->getApiKey()) {
            return ['error' => 'Gemini API key not configured', 'code' => 500];
        }

        $prompt = "You are a viva examiner conducting an oral examination. ";
        if ($topic) {
            $prompt .= "The topic is: {$topic}. ";
        }
        $prompt .= "You asked the student: \"{$question}\"\n\n";
        $prompt .= "The student responded: \"{$studentAnswer}\"\n\n";
        if (!empty($conversationHistory)) {
            $prompt .= "Previous conversation:\n";
            foreach ($conversationHistory as $turn) {
                $prompt .= "- Examiner: {$turn['examiner']}\n";
                $prompt .= "- Student: {$turn['student']}\n\n";
            }
        }
        $prompt .= "Analyze the student's answer and respond as a natural viva examiner would:\n\n";
        $prompt .= "1. If the answer is insufficient, too short, or unclear, gently prompt them to elaborate or provide more details.\n";
        $prompt .= "2. If the student says they don't know (or similar phrases like 'I don't know', 'skip', 'pass', 'not sure'), acknowledge it professionally and move to the next question.\n";
        $prompt .= "3. If the answer is good but incomplete, ask a follow-up question to probe deeper.\n";
        $prompt .= "4. If the answer is complete and satisfactory, acknowledge it positively and indicate you're moving to the next question.\n\n";
        $prompt .= "Your response should be:\n";
        $prompt .= "- Natural and conversational (like a real examiner)\n";
        $prompt .= "- Brief (1-2 sentences maximum)\n";
        $prompt .= "- Professional and encouraging\n";
        $prompt .= "- Spoken aloud, so write it as you would say it\n\n";
        $prompt .= "Return your response as a JSON object:\n";
        $prompt .= "{\n";
        $prompt .= '  "response": "<your conversational response>",\n';
        $prompt .= '  "shouldContinue": <true if need more answer, false if can move to next question>,\n';
        $prompt .= '  "isSkipped": <true if student indicated they don\'t know>\n';
        $prompt .= "}\n";
        $prompt .= "Return ONLY the JSON object, no additional text.";

        $response = Http::post($this->getApiUrl(), [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.8,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 512,
            ],
        ]);

        if ($response->failed()) {
            $errorData = $response->json();
            return [
                'error' => $errorData['error']['message'] ?? 'Failed to generate conversational response',
                'code' => $response->status(),
            ];
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $result = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
                $result['response'] = $result['response'] ?? 'Please continue with your answer.';
                $result['shouldContinue'] = isset($result['shouldContinue']) ? (bool) $result['shouldContinue'] : true;
                $result['isSkipped'] = isset($result['isSkipped']) ? (bool) $result['isSkipped'] : false;
                return $result;
            }
        }

        $isSkip = $this->isSkipOrDontKnow($studentAnswer);
        $isValid = $this->isValidAnswer($studentAnswer);

        return [
            'response' => $isSkip
                ? 'Thank you. Let\'s move on to the next question.'
                : ($isValid
                    ? 'Thank you for your answer. Let\'s move to the next question.'
                    : 'Could you please elaborate on that? I need a bit more detail.'),
            'shouldContinue' => !$isSkip && !$isValid,
            'isSkipped' => $isSkip,
        ];
    }

    protected function isSkipOrDontKnow(string $answer): bool
    {
        $normalized = strtolower(trim($answer));
        $skipPatterns = [
            "i don't know", "i do not know", "don't know", "do not know",
            "skip", "i skip", "pass", "no answer", "cannot answer", "can't answer",
            "not sure", "unsure", "idk",
        ];
        foreach ($skipPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }
        return false;
    }

    protected function isValidAnswer(string $answer): bool
    {
        $trimmed = trim($answer);
        return strlen($trimmed) >= 10 && !$this->isSkipOrDontKnow($trimmed);
    }
}
