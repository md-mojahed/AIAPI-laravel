<?php

namespace Mojahed\AIAPI;

class AIResponse
{
    public function __construct(
        public readonly string $answer,
        public readonly string $summary,
        public readonly int    $tokensUsed,
        public readonly string $model,
        public readonly array  $raw,
    ) {}

    /**
     * Check if the response has a valid answer.
     */
    public function hasAnswer(): bool
    {
        return $this->answer !== '';
    }

    /**
     * Check if a summary was returned.
     */
    public function hasSummary(): bool
    {
        return $this->summary !== '';
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'answer'     => $this->answer,
            'summary'    => $this->summary,
            'tokensUsed' => $this->tokensUsed,
            'model'      => $this->model,
        ];
    }
}
