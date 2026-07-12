<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    | Supported: "openrouter", "openai", "claude", "gemini", "ollama",
    |            "groq", "deepseek", "mistral", "together", "fireworks",
    |            "xai", "cohere", "zai"
    */
    'provider' => env('AIAPI_PROVIDER', 'openrouter'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    | Set the default model for your chosen provider.
    | Each provider has its own model naming convention.
    */
    'model' => env('AIAPI_MODEL', 'z-ai/glm-5.2'),

    /*
    |--------------------------------------------------------------------------
    | API Keys per Provider
    |--------------------------------------------------------------------------
    */
    'keys' => [
        'openrouter' => env('OPENROUTER_API_KEY'),
        'openai'     => env('OPENAI_API_KEY'),
        'claude'     => env('ANTHROPIC_API_KEY'),
        'gemini'     => env('GEMINI_API_KEY'),
        'groq'       => env('GROQ_API_KEY'),
        'deepseek'   => env('DEEPSEEK_API_KEY'),
        'mistral'    => env('MISTRAL_API_KEY'),
        'together'   => env('TOGETHER_API_KEY'),
        'fireworks'  => env('FIREWORKS_API_KEY'),
        'xai'        => env('XAI_API_KEY'),
        'cohere'     => env('COHERE_API_KEY'),
        'zai'        => env('ZAI_API_KEY'),
        'ollama'     => null, // no key needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama Base URL
    |--------------------------------------------------------------------------
    */
    'ollama_url' => env('OLLAMA_URL', 'http://localhost:11434'),

    /*
    |--------------------------------------------------------------------------
    | Default Behaviour
    |--------------------------------------------------------------------------
    | System instruction — how the AI should behave by default.
    */
    'behaviour' => env('AIAPI_BEHAVIOUR', 'You are a helpful assistant.'),

    /*
    |--------------------------------------------------------------------------
    | Default Knowledge
    |--------------------------------------------------------------------------
    | Path to a .md or .txt file, or a raw string.
    | Can also be an array of file paths.
    | null = no default knowledge
    */
    'knowledge' => null,

    /*
    |--------------------------------------------------------------------------
    | Generation Parameters
    |--------------------------------------------------------------------------
    */
    'max_tokens'  => (int) env('AIAPI_MAX_TOKENS', 1024),
    'temperature' => (float) env('AIAPI_TEMPERATURE', 0.7),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('AIAPI_TIMEOUT', 60),

];
