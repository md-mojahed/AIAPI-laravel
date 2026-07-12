<?php

namespace Mojahed\AIAPI\Providers;

use InvalidArgumentException;

class ProviderRegistry
{
    /**
     * Built-in providers map.
     */
    protected static array $providers = [
        'openai'     => OpenAIProvider::class,
        'openrouter' => OpenRouterProvider::class,
        'claude'     => ClaudeProvider::class,
        'gemini'     => GeminiProvider::class,
        'ollama'     => OllamaProvider::class,
        'groq'       => GroqProvider::class,
        'deepseek'   => DeepSeekProvider::class,
        'mistral'    => MistralProvider::class,
        'together'   => TogetherProvider::class,
        'fireworks'  => FireworksProvider::class,
        'xai'        => XAIProvider::class,
        'cohere'     => CohereProvider::class,
        'zai'        => ZAIProvider::class,
    ];

    /**
     * Register a custom provider.
     *
     * Usage (in AppServiceProvider::boot):
     *   ProviderRegistry::register('myprovider', MyProvider::class);
     */
    public static function register(string $name, string $class): void
    {
        if (!in_array(ProviderInterface::class, class_implements($class))) {
            throw new InvalidArgumentException(
                "Provider [{$class}] must implement ProviderInterface."
            );
        }

        static::$providers[strtolower($name)] = $class;
    }

    /**
     * Resolve a provider instance by name.
     */
    public static function resolve(string $name, ?string $apiKey = null): ProviderInterface
    {
        $name  = strtolower($name);
        $class = static::$providers[$name] ?? null;

        if (!$class) {
            throw new InvalidArgumentException(
                "Unknown AI provider [{$name}]. Register it with ProviderRegistry::register()."
            );
        }

        $key = $apiKey ?? config("aiapi.keys.{$name}", '');

        return new $class($key);
    }

    /**
     * List all registered provider names.
     */
    public static function all(): array
    {
        return array_keys(static::$providers);
    }
}
