<?php

namespace Mojahed\AIAPI\Providers;

use Mojahed\AIAPI\Providers\Concerns\OpenAICompatible;

class OpenAIProvider implements ProviderInterface
{
    use OpenAICompatible;

    public function __construct(protected string $apiKey) {}

    protected function baseUrl(): string
    {
        return 'https://api.openai.com/v1';
    }
}
