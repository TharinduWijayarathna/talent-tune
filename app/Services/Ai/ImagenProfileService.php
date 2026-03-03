<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImagenProfileService
{
    protected function getApiKey(): ?string
    {
        return config('services.google.gemini_api_key');
    }

    /**
     * Enhance an existing profile photo with AI to look more professional.
     * Reads the image from storage, sends to Gemini for enhancement, saves result.
     * Returns ['path' => string] or ['error' => string, 'code' => int].
     */
    public function enhanceProfilePicture(string $avatarPath, int $userId): array
    {
        if (! $this->getApiKey()) {
            return ['error' => 'API key not configured for image enhancement', 'code' => 500];
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($avatarPath)) {
            return ['error' => 'Avatar image not found', 'code' => 404];
        }

        $contents = $disk->get($avatarPath);
        $mimeType = $this->guessMimeType($avatarPath);
        $base64 = base64_encode($contents);

        $apiKey = $this->getApiKey();
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}";

        $prompt = 'Transform this photo into a professional headshot. '
            .'Improve lighting and clarity, use a clean neutral background (e.g. soft gray or white). '
            .'Keep the same person and their appearance; only enhance quality and make it look professional. '
            .'Output only the enhanced image, no text or captions.';

        $body = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64,
                            ],
                        ],
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE'],
            ],
        ];

        $response = Http::timeout(90)->post($url, $body);

        if ($response->failed()) {
            $errorData = $response->json();

            return [
                'error' => $errorData['error']['message'] ?? 'Failed to enhance image',
                'code' => $response->status(),
            ];
        }

        $data = $response->json();
        $parts = $data['candidates'][0]['content']['parts'] ?? [];

        $imageBase64 = null;
        foreach ($parts as $part) {
            $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
            if ($inline && isset($inline['data'])) {
                $imageBase64 = $inline['data'];
                break;
            }
        }

        if (! $imageBase64 || ! is_string($imageBase64)) {
            return ['error' => 'No enhanced image in response', 'code' => 500];
        }

        $binary = base64_decode($imageBase64, true);
        if ($binary === false) {
            return ['error' => 'Invalid image data', 'code' => 500];
        }

        $dir = "avatars/{$userId}";
        $filename = 'enhanced_'.uniqid('', true).'.png';
        $newPath = "{$dir}/{$filename}";

        $disk->put($newPath, $binary);

        return ['path' => $newPath];
    }

    private function guessMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
