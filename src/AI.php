<?php

namespace Mojahed\AIAPI;

use Mojahed\AIAPI\Providers\ProviderInterface;
use Mojahed\AIAPI\Providers\ProviderRegistry;
use RuntimeException;

class AI
{
    protected ?string $provider    = null;
    protected ?string $model       = null;
    protected ?string $apiKey      = null;
    protected ?string $behaviour   = null;
    protected string|array $knowledge = '';
    protected string  $summary     = '';
    protected ?int    $maxTokens   = null;
    protected ?float  $temperature = null;
    protected ?int    $timeout     = null;
    protected bool    $jsonMode    = true;

    protected ?ProviderInterface $customProvider = null;

    // ─── Setters (fluent) ─────────────────────────────────────────────────────

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function setApiKey(string $key): static
    {
        $this->apiKey = $key;
        return $this;
    }

    public function setBehaviour(string $behaviour): static
    {
        $this->behaviour = $behaviour;
        return $this;
    }

    public function setKnowledge(string|array $knowledge): static
    {
        $this->knowledge = $knowledge;
        return $this;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function setMaxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setTimeout(int $seconds): static
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function setJsonMode(bool $enabled = true): static
    {
        $this->jsonMode = $enabled;
        return $this;
    }

    /**
     * Use a custom provider instance directly.
     */
    public function useProvider(ProviderInterface $provider): static
    {
        $this->customProvider = $provider;
        return $this;
    }

    /**
     * Register a custom provider class globally.
     */
    public static function registerProvider(string $name, string $class): void
    {
        ProviderRegistry::register($name, $class);
    }

    // ─── Ask ──────────────────────────────────────────────────────────────────

    /**
     * Send a question and get an AIResponse back.
     */
    public function ask(string $question): AIResponse
    {
        // Resolve provider
        $provider = $this->customProvider ?? ProviderRegistry::resolve(
            $this->provider ?? config('aiapi.provider', 'openrouter'),
            $this->apiKey
        );

        // Build prompt
        $builder = (new PromptBuilder())
            ->setBehaviour($this->behaviour ?? config('aiapi.behaviour', ''))
            ->setSummary($this->summary);

        // Set knowledge
        $knowledge = $this->knowledge ?: config('aiapi.knowledge');
        if (!empty($knowledge)) {
            $builder->setKnowledge($knowledge);
        }

        $messages = $builder->build($question);

        // Build options
        $options = [
            'model'       => $this->model       ?? config('aiapi.model'),
            'max_tokens'  => $this->maxTokens   ?? config('aiapi.max_tokens',  1024),
            'temperature' => $this->temperature ?? config('aiapi.temperature', 0.7),
            'timeout'     => $this->timeout     ?? config('aiapi.timeout',     60),
            'json_mode'   => $this->jsonMode,
        ];

        // Send to provider
        $raw = $provider->send($messages, $options);

        // Extract content
        $content = $provider->extractContent($raw);

        // Parse JSON response
        [$answer, $summary] = $this->parseJsonResponse($content);

        return new AIResponse(
            answer:     $answer,
            summary:    $summary,
            tokensUsed: $provider->extractTokens($raw),
            model:      $provider->extractModel($raw),
            raw:        $raw,
        );
    }

    // ─── JSON Parser ──────────────────────────────────────────────────────────

    /**
     * Parse the JSON response from the model.
     * Returns [answer, summary].
     * Gracefully handles non-JSON or malformed responses.
     */
    protected function parseJsonResponse(string $content): array
    {
        $content = trim($content);

        // Strip markdown code fences if model ignores instructions
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        $content = trim($content);

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['answer'])) {
            return [
                (string) ($decoded['answer']  ?? ''),
                (string) ($decoded['summary'] ?? ''),
            ];
        }

        // Fallback — return raw content as answer, no summary
        return [$content, ''];
    }
}
