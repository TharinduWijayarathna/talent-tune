<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RubricService
{
    /**
     * Get rubric-based score from Python service using 5 question scores (1-10 each).
     *
     * @param  array<int, int>  $scores  [q1_score, q2_score, q3_score, q4_score, q5_score] each 1-10
     * @return array{success: bool, score?: float|int, error?: string}
     */
    public function getRubricScore(array $scores): array
    {
        if (count($scores) !== 5) {
            return ['success' => false, 'error' => 'Exactly 5 scores are required'];
        }

        $payload = [
            'q1_score' => (int) max(1, min(10, $scores[0] ?? 0)),
            'q2_score' => (int) max(1, min(10, $scores[1] ?? 0)),
            'q3_score' => (int) max(1, min(10, $scores[2] ?? 0)),
            'q4_score' => (int) max(1, min(10, $scores[3] ?? 0)),
            'q5_score' => (int) max(1, min(10, $scores[4] ?? 0)),
        ];

        $baseUrl = rtrim(config('services.rubric.url', 'http://127.0.0.1:5000'), '/');
        $url = $baseUrl.'/predict';

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->failed()) {
                Log::warning('Rubric service request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Rubric service returned '.$response->status(),
                ];
            }

            $data = $response->json();
            $score = $data['score'] ?? $data['rubric_score'] ?? $data['prediction'] ?? null;
            if ($score !== null && is_numeric($score)) {
                return ['success' => true, 'score' => (float) $score];
            }

            return [
                'success' => false,
                'error' => 'Invalid rubric response format',
            ];
        } catch (\Exception $e) {
            Log::error('Rubric service error', ['message' => $e->getMessage(), 'url' => $url]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
