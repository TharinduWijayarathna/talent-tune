<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\TTSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TTSController extends Controller
{
    public function __construct(
        protected TTSService $ttsService
    ) {}

    /**
     * Generate speech using Google Cloud Text-to-Speech API.
     */
    public function generate(Request $request): Response|JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'languageCode' => 'nullable|string|max:10',
            'voiceName' => 'nullable|string|max:50',
            'modelName' => 'nullable|string|max:100',
            'prompt' => 'nullable|string|max:500',
            'audioEncoding' => 'nullable|string|in:MP3,LINEAR16,OGG_OPUS',
            'speakingRate' => 'nullable|numeric|min:0.25|max:4.0',
            'pitch' => 'nullable|numeric|min:-20|max:20',
        ]);

        $result = $this->ttsService->generate(
            $request->input('text'),
            $request->input('languageCode', 'en-us'),
            $request->input('voiceName', 'Achernar'),
            $request->input('modelName', 'gemini-2.5-flash-lite-preview-tts'),
            $request->input('prompt', 'Read aloud in a warm, welcoming tone.'),
            $request->input('audioEncoding', 'LINEAR16'),
            (float) $request->input('speakingRate', 1),
            (float) $request->input('pitch', 0),
            $request->boolean('useStandardTTS', false)
        );

        if (isset($result['error'])) {
            $code = $result['code'] ?? 500;

            return response()->json([
                'error' => $result['error'],
                'code' => $result['code'] ?? null,
                'details' => $result['details'] ?? null,
            ], $code);
        }

        $encoding = $result['encoding'] ?? 'LINEAR16';
        $extension = strtolower($encoding === 'LINEAR16' ? 'wav' : ($encoding === 'OGG_OPUS' ? 'ogg' : 'mp3'));

        return response($result['audio'], 200)
            ->header('Content-Type', $result['content_type'])
            ->header('Content-Disposition', 'inline; filename="speech.'.$extension.'"');
    }
}
