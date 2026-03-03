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
     * Generate a profile picture using Imagen based on user choices.
     * Returns ['path' => string] (storage path) or ['error' => string, 'code' => int].
     *
     * @param  array{style?: string, background?: string, mood?: string, gender?: string}  $options
     */
    public function generateProfilePicture(array $options, int $userId): array
    {
        if (! $this->getApiKey()) {
            return ['error' => 'API key not configured for image generation', 'code' => 500];
        }

        $prompt = $this->buildProfilePrompt($options);
        $apiKey = $this->getApiKey();
        $url = "https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-001:predict?key={$apiKey}";

        $body = [
            'instances' => [
                ['prompt' => $prompt],
            ],
            'parameters' => [
                'sampleCount' => 1,
                'personGeneration' => 'allow_adult',
            ],
        ];

        $response = Http::timeout(60)->post($url, $body);

        if ($response->failed()) {
            $errorData = $response->json();

            return [
                'error' => $errorData['error']['message'] ?? 'Failed to generate profile image',
                'code' => $response->status(),
            ];
        }

        $data = $response->json();

        // Response may be: predictions[0].bytesBase64Encoded or generatedImages[0].image.imageBytes
        $base64 = $data['predictions'][0]['bytesBase64Encoded']
            ?? $data['generatedImages'][0]['image']['imageBytes']
            ?? null;

        if (! $base64 || ! is_string($base64)) {
            return ['error' => 'No image data in response', 'code' => 500];
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return ['error' => 'Invalid image data', 'code' => 500];
        }

        $dir = "avatars/{$userId}";
        $filename = uniqid('avatar_', true).'.png';
        $path = "{$dir}/{$filename}";

        Storage::disk('public')->put($path, $binary);

        return ['path' => $path];
    }

    private function buildProfilePrompt(array $options): string
    {
        $style = $this->mapOption('style', $options, [
            'professional' => 'professional headshot, business attire, clean and polished',
            'casual' => 'casual friendly portrait, relaxed outfit, approachable',
            'creative' => 'creative portrait, artistic lighting, modern and distinctive',
        ]);
        $background = $this->mapOption('background', $options, [
            'neutral' => 'neutral gray or soft gradient background',
            'outdoor' => 'soft outdoor background, natural light, blurred',
            'abstract' => 'subtle abstract or geometric background, not distracting',
            'minimal' => 'plain white or light minimal background',
        ]);
        $mood = $this->mapOption('mood', $options, [
            'friendly' => 'warm smile, approachable and friendly expression',
            'serious' => 'composed, professional and confident expression',
            'approachable' => 'slight smile, welcoming and approachable',
        ]);
        $gender = $this->mapOption('gender', $options, [
            'neutral' => 'androgynous person',
            'male' => 'adult man',
            'female' => 'adult woman',
        ], 'neutral');

        return "Profile photo of one {$gender}, {$style}, {$background}, {$mood}. "
            .'Single person only, head and shoulders, front facing, high quality portrait suitable for a profile picture. '
            .'No text, no logos.';
    }

    /**
     * @param  array<string, string>  $map
     */
    private function mapOption(string $key, array $options, array $map, string $default = 'neutral'): string
    {
        $value = $options[$key] ?? $default;

        return $map[$value] ?? $map[$default] ?? $default;
    }
}
