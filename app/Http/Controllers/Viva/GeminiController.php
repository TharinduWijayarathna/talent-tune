<?php

namespace App\Http\Controllers\Viva;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    /**
     * Generate questions for a viva session using Gemini AI
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateQuestions(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'numQuestions' => 'nullable|integer|min:1|max:20',
            'difficulty' => 'nullable|string|in:beginner,intermediate,advanced',
        ]);

        $topic = $request->input('topic');
        $description = $request->input('description', '');
        $numQuestions = $request->input('numQuestions', 5);
        $difficulty = $request->input('difficulty', 'intermediate');

        $apiKey = config('services.google.gemini_api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Gemini API key not configured'
            ], 500);
        }

        try {
            $prompt = "Generate {$numQuestions} viva questions for the topic: {$topic}";

            if ($description) {
                $prompt .= "\n\nContext: {$description}";
            }

            $prompt .= "\n\nDifficulty level: {$difficulty}";
            $prompt .= "\n\nRequirements:";
            $prompt .= "\n- Questions should be clear and concise";
            $prompt .= "\n- Questions should test understanding, not just memorization";
            $prompt .= "\n- Questions should progress from basic to more complex concepts";
            $prompt .= "\n- Return ONLY a JSON array of question strings, no additional text";
            $prompt .= "\n- Format: [\"question1\", \"question2\", \"question3\", ...]";

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to generate questions';

                return response()->json([
                    'error' => $errorMessage,
                    'code' => $response->status(),
                ], $response->status());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Extract JSON array from response
            $jsonMatch = preg_match('/\[.*\]/s', $text, $matches);

            if ($jsonMatch) {
                $questions = json_decode($matches[0], true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($questions)) {
                    return response()->json([
                        'questions' => $questions,
                        'count' => count($questions),
                    ]);
                }
            }

            // Fallback: try to parse as plain text, one question per line
            $lines = array_filter(array_map('trim', explode("\n", $text)));
            $questions = [];

            foreach ($lines as $line) {
                // Remove numbering and bullet points
                $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                $line = preg_replace('/^[-*]\s*/', '', $line);
                $line = trim($line);

                if (!empty($line) && strlen($line) > 10) {
                    $questions[] = $line;
                }
            }

            if (empty($questions)) {
                return response()->json([
                    'error' => 'Could not parse questions from AI response',
                    'raw_response' => $text,
                ], 500);
            }

            return response()->json([
                'questions' => array_slice($questions, 0, $numQuestions),
                'count' => min(count($questions), $numQuestions),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while generating questions',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Evaluate a student's answer using Gemini AI
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function evaluateAnswer(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'topic' => 'nullable|string|max:200',
        ]);

        $question = $request->input('question');
        $answer = $request->input('answer');
        $topic = $request->input('topic', '');

        $apiKey = config('services.google.gemini_api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Gemini API key not configured'
            ], 500);
        }

        try {
            $prompt = "Evaluate the following viva answer:\n\n";
            $prompt .= "Question: {$question}\n\n";
            $prompt .= "Student Answer: {$answer}\n\n";

            if ($topic) {
                $prompt .= "Topic: {$topic}\n\n";
            }

            $prompt .= "Please provide:\n";
            $prompt .= "1. A score out of 100\n";
            $prompt .= "2. Brief feedback (2-3 sentences)\n";
            $prompt .= "3. Key points that were covered correctly\n";
            $prompt .= "4. Areas that need improvement (if any)\n\n";
            $prompt .= "Return your response as a JSON object with the following structure:\n";
            $prompt .= "{\n";
            $prompt .= '  "score": <number 0-100>,\n';
            $prompt .= '  "feedback": "<brief feedback text>",\n';
            $prompt .= '  "correctPoints": ["point1", "point2", ...],\n';
            $prompt .= '  "improvements": ["area1", "area2", ...]\n';
            $prompt .= "}\n";
            $prompt .= "Return ONLY the JSON object, no additional text.";

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to evaluate answer';

                return response()->json([
                    'error' => $errorMessage,
                    'code' => $response->status(),
                ], $response->status());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Extract JSON object from response
            $jsonMatch = preg_match('/\{.*\}/s', $text, $matches);

            if ($jsonMatch) {
                $evaluation = json_decode($matches[0], true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($evaluation)) {
                    // Ensure all required fields exist
                    $evaluation['score'] = isset($evaluation['score']) ? (int) $evaluation['score'] : 0;
                    $evaluation['score'] = max(0, min(100, $evaluation['score'])); // Clamp between 0-100
                    $evaluation['feedback'] = $evaluation['feedback'] ?? 'No feedback provided';
                    $evaluation['correctPoints'] = $evaluation['correctPoints'] ?? [];
                    $evaluation['improvements'] = $evaluation['improvements'] ?? [];

                    return response()->json($evaluation);
                }
            }

            // Fallback: parse text response manually
            $score = 0;
            $feedback = 'Could not parse evaluation response';

            // Try to extract score
            if (preg_match('/score[:\s]+(\d+)/i', $text, $scoreMatch)) {
                $score = (int) $scoreMatch[1];
                $score = max(0, min(100, $score));
            }

            // Use first few sentences as feedback
            $sentences = preg_split('/[.!?]+/', $text);
            $feedback = trim($sentences[0] ?? $text);
            if (strlen($feedback) > 200) {
                $feedback = substr($feedback, 0, 200) . '...';
            }

            return response()->json([
                'score' => $score,
                'feedback' => $feedback,
                'correctPoints' => [],
                'improvements' => [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while evaluating answer',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a conversational follow-up response for viva session
     * Acts like a real viva examiner - responds naturally to student answers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateConversationalResponse(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'studentAnswer' => 'required|string|max:5000',
            'topic' => 'nullable|string|max:200',
            'conversationHistory' => 'nullable|array',
            'attemptNumber' => 'nullable|integer|min:1',
        ]);

        $question = $request->input('question');
        $studentAnswer = $request->input('studentAnswer');
        $topic = $request->input('topic', '');
        $conversationHistory = $request->input('conversationHistory', []);
        $attemptNumber = $request->input('attemptNumber', 1);

        $apiKey = config('services.google.gemini_api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Gemini API key not configured'
            ], 500);
        }

        try {
            $prompt = "You are a viva examiner conducting an oral examination. ";
            
            if ($topic) {
                $prompt .= "The topic is: {$topic}. ";
            }

            $prompt .= "You asked the student: \"{$question}\"\n\n";
            $prompt .= "The student responded: \"{$studentAnswer}\"\n\n";

            // Add conversation history if available
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

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 512,
                ]
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to generate conversational response';

                return response()->json([
                    'error' => $errorMessage,
                    'code' => $response->status(),
                ], $response->status());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Extract JSON object from response
            $jsonMatch = preg_match('/\{.*\}/s', $text, $matches);

            if ($jsonMatch) {
                $result = json_decode($matches[0], true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
                    // Ensure all required fields exist
                    $result['response'] = $result['response'] ?? 'Please continue with your answer.';
                    $result['shouldContinue'] = isset($result['shouldContinue']) ? (bool) $result['shouldContinue'] : true;
                    $result['isSkipped'] = isset($result['isSkipped']) ? (bool) $result['isSkipped'] : false;

                    return response()->json($result);
                }
            }

            // Fallback: generate a simple response
            $isSkip = $this->isSkipOrDontKnow($studentAnswer);
            $isValid = $this->isValidAnswer($studentAnswer);

            return response()->json([
                'response' => $isSkip 
                    ? 'Thank you. Let\'s move on to the next question.' 
                    : ($isValid 
                        ? 'Thank you for your answer. Let\'s move to the next question.' 
                        : 'Could you please elaborate on that? I need a bit more detail.'),
                'shouldContinue' => !$isSkip && !$isValid,
                'isSkipped' => $isSkip,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while generating conversational response',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to check if answer indicates skip or don't know
     */
    private function isSkipOrDontKnow(string $answer): bool
    {
        $normalized = strtolower(trim($answer));
        $skipPatterns = [
            "i don't know",
            "i do not know",
            "don't know",
            "do not know",
            "skip",
            "i skip",
            "pass",
            "no answer",
            "cannot answer",
            "can't answer",
            "not sure",
            "unsure",
            "idk",
        ];
        
        foreach ($skipPatterns as $pattern) {
            if (strpos($normalized, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Helper method to check if answer is valid
     */
    private function isValidAnswer(string $answer): bool
    {
        $trimmed = trim($answer);
        if (strlen($trimmed) < 10) {
            return false;
        }
        return !$this->isSkipOrDontKnow($trimmed);
    }
}
