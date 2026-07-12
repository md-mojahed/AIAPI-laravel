<?php

namespace Mojahed\AIAPI\Providers;

use Illuminate\Support\Facades\Http;

class ClaudeProvider implements ProviderInterface
{
    public function __construct(protected string $apiKey) {}

    public function send(array $messages, array $options): array
    {
        // Claude separates system message from messages array
        $system   = '';
        $filtered = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $system = $msg['content'];
            } else {
                $filtered[] = $msg;
            }
        }

        $payload = [
            'model'      => $options['model'],
            'max_tokens' => $options['max_tokens']  ?? 1024,
            'messages'   => $filtered,
        ];

        if ($system !== '') {
            $payload['system'] = $system;
        }

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])
        ->timeout($options['timeout'] ?? 60)
        ->post('https://api.anthropic.com/v1/messages', $payload)
        ->throw();

        return $response->json() ?? [];
    }

    public function extractContent(array $raw): string
    {
        return $raw['content'][0]['text'] ?? '';
    }

    public function extractTokens(array $raw): int
    {
        return ($raw['usage']['input_tokens'] ?? 0) + ($raw['usage']['output_tokens'] ?? 0);
    }

    public function extractModel(array $raw): string
    {
        return $raw['model'] ?? '';
    }
}
