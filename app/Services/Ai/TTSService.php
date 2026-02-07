<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class TTSService
{
    /**
     * Generate speech. Returns ['audio' => string (binary), 'content_type' => string] or ['error' => string, 'code' => int, 'details' => ?array].
     */
    public function generate(
        string $text,
        string $languageCode = 'en-us',
        string $voiceName = 'Achernar',
        string $modelName = 'gemini-2.5-flash-lite-preview-tts',
        string $prompt = 'Read aloud in a warm, welcoming tone.',
        string $audioEncoding = 'LINEAR16',
        float $speakingRate = 1,
        float $pitch = 0,
        bool $useStandardTTS = false
    ): array {
        $apiKey = config('services.google.tts_api_key');

        if (! $apiKey) {
            return ['error' => 'Google TTS API key not configured', 'code' => 500];
        }

        $url = "https://texttospeech.googleapis.com/v1beta1/text:synthesize?key={$apiKey}";

        $requestBody = [
            'input' => ['text' => $text],
            'audioConfig' => [
                'audioEncoding' => $audioEncoding,
                'speakingRate' => $speakingRate,
                'pitch' => $pitch,
            ],
        ];

        if (! $useStandardTTS && $modelName) {
            $requestBody['voice'] = [
                'languageCode' => $languageCode,
                'modelName' => $modelName,
                'name' => $voiceName,
            ];
            if ($prompt) {
                $requestBody['input']['prompt'] = $prompt;
            }
        } else {
            $requestBody['voice'] = [
                'languageCode' => $languageCode,
                'name' => 'en-US-Standard-D',
            ];
        }

        $response = Http::post($url, $requestBody);

        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Failed to generate speech';
            $errorCode = $errorData['error']['code'] ?? $response->status();

            $isVertexAIError = str_contains($errorMessage, 'Vertex AI API')
                || str_contains($errorMessage, 'aiplatform.googleapis.com')
                || str_contains($errorMessage, 'aiplatform.endpoints.predict');

            $isIAMPermissionError = str_contains($errorMessage, 'IAM_PERMISSION_DENIED')
                || (str_contains($errorMessage, 'Permission') && str_contains($errorMessage, 'denied'));

            if ($isIAMPermissionError && $errorCode === 403 && ! $useStandardTTS) {
                $standardResult = $this->generate(
                    $text,
                    'en-US',
                    'en-US-Standard-D',
                    '',
                    '',
                    $audioEncoding,
                    $speakingRate,
                    $pitch,
                    true
                );
                if (! isset($standardResult['error'])) {
                    return $standardResult;
                }
            }

            $userMessage = $this->getUserFriendlyErrorMessage($errorMessage, $errorCode, $errorData, $isVertexAIError, $isIAMPermissionError);

            return [
                'error' => $userMessage,
                'code' => $errorCode,
                'details' => $errorData,
            ];
        }

        $data = $response->json();
        $audioContent = $data['audioContent'] ?? null;

        if (! $audioContent) {
            return ['error' => 'No audio content received', 'code' => 500];
        }

        $audioData = base64_decode($audioContent);
        $contentType = match ($audioEncoding) {
            'MP3' => 'audio/mpeg',
            'LINEAR16' => 'audio/wav',
            'OGG_OPUS' => 'audio/ogg',
            default => 'audio/wav',
        };

        return [
            'audio' => $audioData,
            'content_type' => $contentType,
            'encoding' => $audioEncoding,
        ];
    }

    protected function getUserFriendlyErrorMessage(string $errorMessage, int $errorCode, array $errorData, bool $isVertexAIError, bool $isIAMPermissionError): string
    {
        if ($isIAMPermissionError && $errorCode === 403) {
            return 'API key lacks IAM permissions for Gemini TTS model. '
                ."To fix this:\n\nOption 1: Grant API key permissions\n"
                ."1. Go to Google Cloud Console > IAM & Admin > IAM\n"
                ."2. Find your API key service account or create one\n"
                ."3. Grant \"Vertex AI User\" role (roles/aiplatform.user)\n"
                ."4. Or use a service account with proper permissions\n\n"
                ."Option 2: Use standard TTS voices (without Gemini model)\n"
                .'The Gemini model requires Vertex AI permissions. '
                ."You can use standard Google TTS voices instead.\n\n"
                .'Note: The system attempted to use standard TTS automatically.';
        }

        if ($isVertexAIError && $errorCode === 403) {
            $activationUrl = null;
            if (isset($errorData['error']['details'])) {
                foreach ($errorData['error']['details'] as $detail) {
                    if (isset($detail['metadata']['activationUrl'])) {
                        $activationUrl = $detail['metadata']['activationUrl'];
                        break;
                    }
                }
            }
            $msg = 'Vertex AI API is required for Gemini TTS model but is not enabled. ';

            return $activationUrl
                ? $msg."Enable it here: {$activationUrl}"
                : $msg."Enable it in Google Cloud Console:\n1. Go to APIs & Services > Library\n2. Search for \"Vertex AI API\"\n3. Click Enable\n4. Wait a few minutes for it to propagate";
        }

        return match ($errorCode) {
            403 => 'Google TTS API access denied. Please check:\n1. Text-to-Speech API is enabled\n2. Vertex AI API is enabled (required for Gemini models)\n3. API key has proper IAM permissions (Vertex AI User role)\n4. Billing is enabled for your Google Cloud project',
            401 => 'Invalid Google TTS API key. Please check your API key configuration.',
            400 => 'Invalid request to Google TTS API. Please check the request parameters.',
            default => $errorMessage,
        };
    }
}
