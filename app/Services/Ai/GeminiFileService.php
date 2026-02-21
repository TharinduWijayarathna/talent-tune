<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiFileService
{
    protected string $apiKey;

    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.google.gemini_api_key');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    }

    /**
     * Process lecture materials and generate viva background and base prompt
     *
     * @param  array  $files  Array of file paths
     * @param  string  $title  Viva title
     * @param  string|null  $description  Viva description
     * @return array ['background' => string, 'base_prompt' => string]
     */
    public function processLectureMaterials(array $files, string $title, ?string $description = null): array
    {
        if (empty($files)) {
            return $this->generateBasicBackground($title, $description);
        }

        try {
            $fileContents = [];
            foreach ($files as $filePath) {
                $content = $this->extractFileContent($filePath);
                if ($content) {
                    $fileContents[] = $content;
                }
            }

            if (empty($fileContents)) {
                return $this->generateBasicBackground($title, $description);
            }

            $combinedContent = implode("\n\n---\n\n", $fileContents);
            $background = $this->generateBackground($title, $description, $combinedContent);
            $basePrompt = $this->generateBasePrompt($title, $description, $combinedContent);

            return [
                'background' => $background,
                'base_prompt' => $basePrompt,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing lecture materials: '.$e->getMessage());

            return $this->generateBasicBackground($title, $description);
        }
    }

    public function extractFileContent(string $filePath): ?string
    {
        if (! Storage::exists($filePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fullPath = Storage::path($filePath);

        try {
            switch ($extension) {
                case 'pdf':
                    return $this->extractPdfContent($fullPath);
                case 'doc':
                case 'docx':
                    return $this->extractDocxContent($fullPath);
                case 'ppt':
                case 'pptx':
                    return $this->extractPptxContent($fullPath);
                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Error extracting content from {$filePath}: ".$e->getMessage());

            return null;
        }
    }

    protected function extractPdfContent(string $filePath): ?string
    {
        try {
            $fileData = file_get_contents($filePath);
            $base64 = base64_encode($fileData);
            $mimeType = mime_content_type($filePath) ?: 'application/pdf';

            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
            ])->post($this->apiUrl.'?key='.$this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                            ['text' => 'Extract all text content from this document. Return only the extracted text, no additional commentary.'],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error extracting PDF content: '.$e->getMessage());

            return null;
        }
    }

    protected function extractDocxContent(string $filePath): ?string
    {
        try {
            $fileData = file_get_contents($filePath);
            $base64 = base64_encode($fileData);
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
            ])->post($this->apiUrl.'?key='.$this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                            ['text' => 'Extract all text content from this document. Return only the extracted text, no additional commentary.'],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error extracting DOCX content: '.$e->getMessage());

            return null;
        }
    }

    protected function extractPptxContent(string $filePath): ?string
    {
        try {
            $fileData = file_get_contents($filePath);
            $base64 = base64_encode($fileData);
            $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
            ])->post($this->apiUrl.'?key='.$this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                            ['text' => 'Extract all text content from this presentation. Return only the extracted text from all slides, no additional commentary.'],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error extracting PPTX content: '.$e->getMessage());

            return null;
        }
    }

    protected function generateBackground(string $title, ?string $description, string $content): string
    {
        $prompt = "You are analyzing lecture materials for a viva (oral examination) session.\n\n";
        $prompt .= "Viva Title: {$title}\n\n";
        if ($description) {
            $prompt .= "Description: {$description}\n\n";
        }
        $prompt .= "Lecture Materials Content:\n{$content}\n\n";
        $prompt .= 'Based on the lecture materials above, generate a comprehensive background context for this viva session. ';
        $prompt .= 'This background should summarize the key topics, concepts, and learning objectives that students should be familiar with. ';
        $prompt .= 'The background should be clear, structured, and suitable for generating relevant viva questions. ';
        $prompt .= 'Return only the background text, no additional formatting or commentary.';

        $response = Http::post($this->apiUrl.'?key='.$this->apiKey, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2048],
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        return '';
    }

    protected function generateBasePrompt(string $title, ?string $description, string $content): string
    {
        $prompt = "Generate viva questions based on the following context:\n\n";
        $prompt .= "Viva Title: {$title}\n\n";
        if ($description) {
            $prompt .= "Description: {$description}\n\n";
        }
        $prompt .= "Lecture Materials Summary:\n{$content}\n\n";
        $prompt .= "Generate questions that:\n";
        $prompt .= "1. Test understanding of key concepts from the lecture materials\n";
        $prompt .= "2. Progress from basic to advanced topics\n";
        $prompt .= "3. Encourage critical thinking and application\n";
        $prompt .= "4. Are clear and concise\n";
        $prompt .= '5. Are appropriate for oral examination format';

        return $prompt;
    }

    protected function generateBasicBackground(string $title, ?string $description): array
    {
        $background = "Viva Session: {$title}\n\n";
        if ($description) {
            $background .= "Description: {$description}\n\n";
        }
        $background .= "This viva session will assess students' understanding of the topics covered in the course materials.";
        $basePrompt = "Generate viva questions for: {$title}";
        if ($description) {
            $basePrompt .= "\n\nContext: {$description}";
        }

        return [
            'background' => $background,
            'base_prompt' => $basePrompt,
        ];
    }

    public function enhancePromptWithStudentDocument(
        string $studentDocumentPath,
        string $vivaBackground,
        string $basePrompt,
        string $title
    ): string {
        $studentContent = $this->extractFileContent($studentDocumentPath);
        if (! $studentContent) {
            return $basePrompt;
        }
        $enhancedPrompt = $basePrompt."\n\n";
        $enhancedPrompt .= "Student's Submitted Document Content:\n{$studentContent}\n\n";
        $enhancedPrompt .= "IMPORTANT: Generate questions that:\n";
        $enhancedPrompt .= "1. Are based on the lecture materials background provided above\n";
        $enhancedPrompt .= "2. Specifically relate to the content in the student's submitted document\n";
        $enhancedPrompt .= "3. Test the student's understanding of how the lecture concepts apply to their specific work\n";
        $enhancedPrompt .= "4. Probe deeper into areas covered in the student's document\n";
        $enhancedPrompt .= "5. Are personalized to the student's submission while maintaining academic rigor";

        return $enhancedPrompt;
    }
}
