<?php

namespace Mojahed\AIAPI\Providers;

interface ProviderInterface
{
    /**
     * Send messages to the provider and return raw response array.
     */
    public function send(array $messages, array $options): array;

    /**
     * Extract the answer text from the raw response.
     */
    public function extractContent(array $raw): string;

    /**
     * Extract total tokens used from the raw response.
     */
    public function extractTokens(array $raw): int;

    /**
     * Extract model name from the raw response.
     */
    public function extractModel(array $raw): string;
}
