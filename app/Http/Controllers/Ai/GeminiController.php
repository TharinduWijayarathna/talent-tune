<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\GeminiQuestionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeminiController extends Controller
{
    public function __construct(
        protected GeminiQuestionService $geminiQuestionService
    ) {}

    /**
     * Generate questions for a viva session using Gemini AI.
     */
    public function generateQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'vivaId' => 'nullable|integer|exists:vivas,id',
            'topic' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'numQuestions' => 'nullable|integer|min:1|max:20',
            'difficulty' => 'nullable|string|in:beginner,intermediate,advanced',
            'studentDocumentPath' => 'nullable|string',
        ]);

        $result = $this->geminiQuestionService->generateQuestions(
            $request->input('vivaId'),
            $request->input('topic'),
            $request->input('description', ''),
            $request->input('numQuestions', 5),
            $request->input('difficulty', 'intermediate'),
            $request->input('studentDocumentPath')
        );

        if (isset($result['error'])) {
            $code = $result['code'] ?? 500;
            unset($result['code']);
            return response()->json($result, $code);
        }

        return response()->json($result);
    }

    /**
     * Evaluate a student's answer using Gemini AI.
     */
    public function evaluateAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'topic' => 'nullable|string|max:200',
        ]);

        $result = $this->geminiQuestionService->evaluateAnswer(
            $request->input('question'),
            $request->input('answer'),
            $request->input('topic', '')
        );

        if (isset($result['error'])) {
            $code = $result['code'] ?? 500;
            unset($result['code']);
            return response()->json($result, $code);
        }

        return response()->json($result);
    }

    /**
     * Generate a conversational follow-up response for viva session.
     */
    public function generateConversationalResponse(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'studentAnswer' => 'required|string|max:5000',
            'topic' => 'nullable|string|max:200',
            'conversationHistory' => 'nullable|array',
            'attemptNumber' => 'nullable|integer|min:1',
        ]);

        $result = $this->geminiQuestionService->generateConversationalResponse(
            $request->input('question'),
            $request->input('studentAnswer'),
            $request->input('topic', ''),
            $request->input('conversationHistory', [])
        );

        if (isset($result['error'])) {
            $code = $result['code'] ?? 500;
            unset($result['code']);
            return response()->json($result, $code);
        }

        return response()->json($result);
    }
}
