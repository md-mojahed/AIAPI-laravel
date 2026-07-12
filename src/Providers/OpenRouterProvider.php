<?php

namespace Mojahed\AIAPI\Providers;

use Mojahed\AIAPI\Providers\Concerns\OpenAICompatible;

class OpenRouterProvider implements ProviderInterface
{
    use OpenAICompatible;

    public function __construct(protected string $apiKey) {}

    protected function baseUrl(): string
    {
        return 'https://openrouter.ai/api/v1';
    }

    protected function extraHeaders(): array
    {
        return [
            'HTTP-Referer' => config('app.url', 'https://localhost'),
            'X-Title'      => config('app.name', 'Laravel App'),
        ];
    }
}
