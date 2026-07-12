<?php

namespace Mojahed\AIAPI\Providers;

use Illuminate\Support\Facades\Http;

class OllamaProvider implements ProviderInterface
{
    protected string $baseUrl;

    public function __construct(string $apiKey = '')
    {
        $this->baseUrl = config('aiapi.ollama_url', 'http://localhost:11434');
    }

    public function send(array $messages, array $options): array
    {
        $response = Http::timeout($options['timeout'] ?? 120)
            ->post($this->baseUrl . '/api/chat', [
                'model'    => $options['model'],
                'messages' => $messages,
                'stream'   => false,
                'options'  => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'num_predict' => $options['max_tokens']  ?? 1024,
                ],
            ])
            ->throw();

        return $response->json() ?? [];
    }

    public function extractContent(array $raw): string
    {
        return $raw['message']['content'] ?? '';
    }

    public function extractTokens(array $raw): int
    {
        return ($raw['prompt_eval_count'] ?? 0) + ($raw['eval_count'] ?? 0);
    }

    public function extractModel(array $raw): string
    {
        return $raw['model'] ?? '';
    }
}
