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
}
