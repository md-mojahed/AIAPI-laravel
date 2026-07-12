<?php

namespace Mojahed\AIAPI\Providers;

use Illuminate\Support\Facades\Http;

class CohereProvider implements ProviderInterface
{
    public function __construct(protected string $apiKey) {}

    public function send(array $messages, array $options): array
    {
        // Cohere v2 Chat API accepts an OpenAI-style messages list.
        $payload = [
            'model'       => $options['model'] ?? 'command-r-plus',
            'messages'    => $messages,
            'max_tokens'  => $options['max_tokens']  ?? 1024,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])
        ->timeout($options['timeout'] ?? 60)
        ->post('https://api.cohere.com/v2/chat', $payload)
        ->throw();

        return $response->json() ?? [];
    }

    public function extractContent(array $raw): string
    {
        // v2 returns content as an array of content blocks.
        foreach ($raw['message']['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'text') {
                return $block['text'] ?? '';
            }
        }

        return '';
    }

    public function extractTokens(array $raw): int
    {
        $tokens = $raw['usage']['tokens'] ?? $raw['usage']['billed_units'] ?? [];

        return (int) (($tokens['input_tokens'] ?? 0) + ($tokens['output_tokens'] ?? 0));
    }

    public function extractModel(array $raw): string
    {
        // The v2 response does not echo the model name back.
        return 'cohere';
    }
}
