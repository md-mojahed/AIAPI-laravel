<?php

namespace Mojahed\AIAPI\Providers\Concerns;

use Illuminate\Support\Facades\Http;

trait OpenAICompatible
{
    /**
     * Send messages using OpenAI-compatible chat completions format.
     */
    public function send(array $messages, array $options): array
    {
        $payload = [
            'model'       => $options['model'],
            'messages'    => $messages,
            'max_tokens'  => $options['max_tokens']  ?? 1024,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        // Force JSON response format if requested
        if (!empty($options['json_mode'])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $headers = array_merge(
            ['Authorization' => 'Bearer ' . $this->apiKey],
            $this->extraHeaders()
        );

        $response = Http::withHeaders($headers)
            ->timeout($options['timeout'] ?? 60)
            ->post($this->baseUrl() . '/chat/completions', $payload)
            ->throw();

        return $response->json() ?? [];
    }

    /**
     * Extract answer text from OpenAI-format response.
     */
    public function extractContent(array $raw): string
    {
        return $raw['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Extract token usage from OpenAI-format response.
     */
    public function extractTokens(array $raw): int
    {
        return $raw['usage']['total_tokens'] ?? 0;
    }

    /**
     * Extract model name from OpenAI-format response.
     */
    public function extractModel(array $raw): string
    {
        return $raw['model'] ?? '';
    }

    /**
     * Extra headers specific to each provider (override in provider).
     */
    protected function extraHeaders(): array
    {
        return [];
    }
}
