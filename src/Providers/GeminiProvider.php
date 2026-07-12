<?php

namespace Mojahed\AIAPI\Providers;

use Illuminate\Support\Facades\Http;

class GeminiProvider implements ProviderInterface
{
    public function __construct(protected string $apiKey) {}

    public function send(array $messages, array $options): array
    {
        $model = $options['model'] ?? 'gemini-1.5-flash';

        // Convert OpenAI-style messages to Gemini format
        $contents      = [];
        $systemPrompt  = '';

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt = $msg['content'];
                continue;
            }
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens']  ?? 1024,
                'temperature'     => $options['temperature'] ?? 0.7,
            ],
        ];

        if ($systemPrompt !== '') {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]],
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
            'Content-Type'   => 'application/json',
        ])
            ->timeout($options['timeout'] ?? 60)
            ->post($url, $payload)
            ->throw();

        return $response->json() ?? [];
    }

    public function extractContent(array $raw): string
    {
        return $raw['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function extractTokens(array $raw): int
    {
        return $raw['usageMetadata']['totalTokenCount'] ?? 0;
    }

    public function extractModel(array $raw): string
    {
        return $raw['modelVersion'] ?? 'gemini';
    }
}
