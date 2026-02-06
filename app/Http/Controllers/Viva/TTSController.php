<?php

namespace App\Http\Controllers\Viva;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TTSController extends Controller
{
    /**
     * Generate speech using Google Cloud Text-to-Speech API (v1beta1 with Gemini TTS)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
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

        $text = $request->input('text');
        $languageCode = $request->input('languageCode', 'en-us');
        $voiceName = $request->input('voiceName', 'Achernar');
        $modelName = $request->input('modelName', 'gemini-2.5-flash-lite-preview-tts');
        $prompt = $request->input('prompt', 'Read aloud in a warm, welcoming tone.');
        $audioEncoding = $request->input('audioEncoding', 'LINEAR16');
        $speakingRate = $request->input('speakingRate', 1);
        $pitch = $request->input('pitch', 0);

        // Check if we should use standard TTS instead of Gemini (if model fails)
        $useStandardTTS = $request->input('useStandardTTS', false);

        // Get Google Cloud TTS API key from config
        $apiKey = config('services.google.tts_api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Google TTS API key not configured'
            ], 500);
        }

        try {
            // Google Cloud Text-to-Speech API v1beta1 endpoint with Gemini TTS
            $url = "https://texttospeech.googleapis.com/v1beta1/text:synthesize?key={$apiKey}";

            // Build request body - use standard TTS if Gemini model fails or if requested
            $requestBody = [
                'input' => [
                    'text' => $text,
                ],
                'audioConfig' => [
                    'audioEncoding' => $audioEncoding,
                    'speakingRate' => $speakingRate,
                    'pitch' => $pitch,
                ],
            ];

            // Use Gemini model with prompt, or fallback to standard voices
            if (!$useStandardTTS && $modelName) {
                $requestBody['voice'] = [
                    'languageCode' => $languageCode,
                    'modelName' => $modelName,
                    'name' => $voiceName,
                ];

                // Add prompt if provided (only for Gemini models)
                if ($prompt) {
                    $requestBody['input']['prompt'] = $prompt;
                }
            } else {
                // Use standard Google TTS voices (no Gemini model)
                $requestBody['voice'] = [
                    'languageCode' => $languageCode,
                    'name' => 'en-US-Standard-D', // Standard voice as fallback
                ];
            }

            $response = Http::post($url, $requestBody);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Failed to generate speech';
                $errorCode = $errorData['error']['code'] ?? $response->status();

                // Check if it's a Vertex AI API error (required for Gemini TTS model)
                $isVertexAIError = str_contains($errorMessage, 'Vertex AI API') ||
                                   str_contains($errorMessage, 'aiplatform.googleapis.com') ||
                                   str_contains($errorMessage, 'aiplatform.endpoints.predict');

                $isIAMPermissionError = str_contains($errorMessage, 'IAM_PERMISSION_DENIED') ||
                                        (str_contains($errorMessage, 'Permission') && str_contains($errorMessage, 'denied'));

                // If it's an IAM permission error and we haven't tried standard TTS yet, retry with standard TTS
                if ($isIAMPermissionError && $errorCode === 403 && !$useStandardTTS) {
                    // Retry with standard TTS (no Gemini model)
                    $standardRequestBody = [
                        'input' => [
                            'text' => $text,
                        ],
                        'voice' => [
                            'languageCode' => 'en-US',
                            'name' => 'en-US-Standard-D', // Standard voice
                        ],
                        'audioConfig' => [
                            'audioEncoding' => $audioEncoding,
                            'speakingRate' => $speakingRate,
                            'pitch' => $pitch,
                        ],
                    ];

                    $retryResponse = Http::post($url, $standardRequestBody);

                    if ($retryResponse->successful()) {
                        $retryData = $retryResponse->json();
                        $audioContent = $retryData['audioContent'] ?? null;

                        if ($audioContent) {
                            $audioData = base64_decode($audioContent);
                            $contentType = match($audioEncoding) {
                                'MP3' => 'audio/mpeg',
                                'LINEAR16' => 'audio/wav',
                                'OGG_OPUS' => 'audio/ogg',
                                default => 'audio/wav',
                            };

                            return response($audioData, 200)
                                ->header('Content-Type', $contentType)
                                ->header('Content-Disposition', 'inline; filename="speech.' . strtolower($audioEncoding === 'LINEAR16' ? 'wav' : ($audioEncoding === 'OGG_OPUS' ? 'ogg' : 'mp3')) . '"');
                        }
                    }
                }

                // Provide user-friendly error messages
                if ($isIAMPermissionError && $errorCode === 403) {
                    $userMessage = 'API key lacks IAM permissions for Gemini TTS model. ';
                    $userMessage .= 'To fix this:\n\n';
                    $userMessage .= 'Option 1: Grant API key permissions\n';
                    $userMessage .= '1. Go to Google Cloud Console > IAM & Admin > IAM\n';
                    $userMessage .= '2. Find your API key service account or create one\n';
                    $userMessage .= '3. Grant "Vertex AI User" role (roles/aiplatform.user)\n';
                    $userMessage .= '4. Or use a service account with proper permissions\n\n';
                    $userMessage .= 'Option 2: Use standard TTS voices (without Gemini model)\n';
                    $userMessage .= 'The Gemini model requires Vertex AI permissions. ';
                    $userMessage .= 'You can use standard Google TTS voices instead.';
                    $userMessage .= '\n\nNote: The system attempted to use standard TTS automatically.';
                } elseif ($isVertexAIError && $errorCode === 403) {
                    // Extract activation URL if available
                    $activationUrl = null;
                    if (isset($errorData['error']['details'])) {
                        foreach ($errorData['error']['details'] as $detail) {
                            if (isset($detail['metadata']['activationUrl'])) {
                                $activationUrl = $detail['metadata']['activationUrl'];
                                break;
                            }
                        }
                    }

                    $userMessage = 'Vertex AI API is required for Gemini TTS model but is not enabled. ';
                    if ($activationUrl) {
                        $userMessage .= "Enable it here: {$activationUrl}";
                    } else {
                        $userMessage .= 'Enable it in Google Cloud Console:\n1. Go to APIs & Services > Library\n2. Search for "Vertex AI API"\n3. Click Enable\n4. Wait a few minutes for it to propagate';
                    }
                } else {
                    $userMessage = match($errorCode) {
                        403 => 'Google TTS API access denied. Please check:\n1. Text-to-Speech API is enabled\n2. Vertex AI API is enabled (required for Gemini models)\n3. API key has proper IAM permissions (Vertex AI User role)\n4. Billing is enabled for your Google Cloud project',
                        401 => 'Invalid Google TTS API key. Please check your API key configuration.',
                        400 => 'Invalid request to Google TTS API. Please check the request parameters.',
                        default => $errorMessage,
                    };
                }

                return response()->json([
                    'error' => $userMessage,
                    'code' => $errorCode,
                    'details' => $errorData,
                ], $response->status());
            }

            $data = $response->json();
            $audioContent = $data['audioContent'] ?? null;

            if (!$audioContent) {
                return response()->json([
                    'error' => 'No audio content received'
                ], 500);
            }

            // Decode base64 audio content
            $audioData = base64_decode($audioContent);

            // Determine content type based on encoding
            $contentType = match($audioEncoding) {
                'MP3' => 'audio/mpeg',
                'LINEAR16' => 'audio/wav',
                'OGG_OPUS' => 'audio/ogg',
                default => 'audio/wav',
            };

            // Return audio file
            return response($audioData, 200)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'inline; filename="speech.' . strtolower($audioEncoding === 'LINEAR16' ? 'wav' : ($audioEncoding === 'OGG_OPUS' ? 'ogg' : 'mp3')) . '"');

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while generating speech',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

