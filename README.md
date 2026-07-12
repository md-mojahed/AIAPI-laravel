# mojahed/aiapi

A Laravel AI API package — connect to any LLM provider with a clean, unified interface.  
Supports OpenAI, Claude, Gemini, Ollama, OpenRouter, Groq, DeepSeek, Mistral, Together AI, Fireworks, xAI, Cohere, Z.AI, and custom providers.

## Requirements

- PHP >= 8.1
- Laravel >= 9.x

## Installation

```bash
composer require mojahed/aiapi
```

Publish config:

```bash
php artisan aiapi:setup
```

## Configuration

Add to your `.env`:

```env
AIAPI_PROVIDER=openrouter
AIAPI_MODEL=z-ai/glm-5.2
# Note: "z-ai/glm-5.2" is the OpenRouter slug.
# For the native "zai" provider use the model name directly, e.g. glm-5.
AIAPI_BEHAVIOUR="You are a helpful assistant. Answer clearly and formally."

OPENROUTER_API_KEY=sk-or-...
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GEMINI_API_KEY=...
GROQ_API_KEY=...
DEEPSEEK_API_KEY=...
MISTRAL_API_KEY=...
TOGETHER_API_KEY=...
FIREWORKS_API_KEY=...
XAI_API_KEY=...
COHERE_API_KEY=...
ZAI_API_KEY=...
```

## Basic Usage

```php
use Mojahed\AIAPI\AI;

$ai = new AI();
$response = $ai->ask('What is Laravel?');

echo $response->answer;      // the answer
echo $response->summary;     // updated conversation summary
echo $response->tokensUsed;  // total tokens used
echo $response->model;       // model that responded
$response->raw;              // full raw API response array
```

## Fluent Chaining

```php
use Mojahed\AIAPI\AI;

$response = (new AI())
    ->setProvider('openrouter')
    ->setModel('z-ai/glm-5.2')
    ->setBehaviour('You are an Eduplus school assistant. Reply formally.')
    ->setKnowledge('/path/to/eduplus-docs.md')
    ->setSummary($summaryFromDB)
    ->setMaxTokens(1024)
    ->setTemperature(0.7)
    ->ask('How does the attendance system work?');

echo $response->answer;
```

## Knowledge Injection

Pass a file path, raw string, or array of both:

```php
// Single file
$ai->setKnowledge('/knowledge/eduplus.md');

// Raw string
$ai->setKnowledge('MdsChatBot is a floating chatbot widget...');

// Multiple files
$ai->setKnowledge([
    '/knowledge/git.md',
    '/knowledge/php.md',
    '/knowledge/eduplus.md',
]);
```

## Summary Memory (Cost-Efficient)

Each response returns a compact summary. Pass it back on the next question:

```php
// Load summary from DB
$summary = AiSession::find($sessionId)?->summary ?? '';

$response = (new AI())
    ->setSummary($summary)
    ->ask($question);

// Save updated summary to DB
AiSession::updateOrCreate(
    ['id' => $sessionId],
    ['summary' => $response->summary]
);

return $response->answer;
```

This gives the AI conversation memory at minimal token cost — no full history needed.

## Switch Provider Per Instance

```php
// Different providers for different purposes
$fast    = (new AI())->setProvider('groq');     // fastest
$smart   = (new AI())->setProvider('claude');   // most capable
$cheap   = (new AI())->setProvider('deepseek'); // cheapest
$local   = (new AI())->setProvider('ollama');   // free, local
```

## Custom Provider

Implement `ProviderInterface` with 4 methods:

```php
use Mojahed\AIAPI\Providers\ProviderInterface;

class MyCustomProvider implements ProviderInterface
{
    public function __construct(protected string $apiKey) {}

    public function send(array $messages, array $options): array
    {
        // Make your API call here
        // Return the raw response as array
    }

    public function extractContent(array $raw): string
    {
        return $raw['my_answer_field'] ?? '';
    }

    public function extractTokens(array $raw): int
    {
        return $raw['usage']['total'] ?? 0;
    }

    public function extractModel(array $raw): string
    {
        return $raw['model'] ?? 'custom';
    }
}
```

Register in `AppServiceProvider::boot()`:

```php
use Mojahed\AIAPI\AI;

public function boot(): void
{
    AI::registerProvider('myprovider', MyCustomProvider::class);
}
```

Use it:

```php
$ai = (new AI())->setProvider('myprovider');
```

## Test Connection

```bash
php artisan aiapi:test
php artisan aiapi:test --provider=claude
php artisan aiapi:test --provider=ollama --question="What is PHP?"
```

## Supported Providers

| Key | Provider | Format |
|---|---|---|
| `openrouter` | OpenRouter | OpenAI compatible |
| `openai` | OpenAI | OpenAI |
| `claude` | Anthropic Claude | Anthropic |
| `gemini` | Google Gemini | Google |
| `ollama` | Ollama (local) | Ollama |
| `groq` | Groq | OpenAI compatible |
| `deepseek` | DeepSeek | OpenAI compatible |
| `mistral` | Mistral AI | OpenAI compatible |
| `together` | Together AI | OpenAI compatible |
| `fireworks` | Fireworks AI | OpenAI compatible |
| `xai` | xAI (Grok) | OpenAI compatible |
| `cohere` | Cohere | Cohere |
| `zai` | Z.AI | OpenAI compatible |
| `custom` | Your own | Implement interface |

## Default Config (config/aiapi.php)

```php
return [
    'provider'    => env('AIAPI_PROVIDER', 'openrouter'),
    'model'       => env('AIAPI_MODEL', 'z-ai/glm-5.2'),
    'behaviour'   => env('AIAPI_BEHAVIOUR', 'You are a helpful assistant.'),
    'knowledge'   => null,
    'max_tokens'  => 1024,
    'temperature' => 0.7,
    'timeout'     => 60,
    'keys'        => [
        'openrouter' => env('OPENROUTER_API_KEY'),
        // ...
    ],
];
```

## Example: Full Chat Application

A complete, copy-paste-ready chat app lives in [`example/`](example):

- `example/migrations/` — `chats` + `chat_messages` tables
- `example/Models/` — `Chat` & `ChatMessage` Eloquent models
- `example/Http/Controllers/ChatController.php` — chat with **history** + **summary memory**
- `example/routes/chat.php` — API routes

### The optimization

Full message history is stored in the database (for the UI), but **only the rolling `summary`** is sent to the LLM on each turn. Token cost stays roughly flat per message instead of growing with the conversation length.

```php
$ai = (new AI())
    ->setSummary($chat->summary)            // memory from previous turns (cheap)
    ->setBehaviour(config('aiapi.behaviour'));

$response = $ai->ask($message);

$chat->update(['summary' => $response->summary]);   // roll memory forward
```

### Setup

1. Copy the files from `example/` into the matching app folders.
2. Run `php artisan migrate`.
3. Register the routes from `example/routes/chat.php` inside `routes/api.php`.
4. Wrap the `ask()` call in a `try/catch` (see `ChatController::ask`) to show users a friendly error on rate limits / outages.

## License

MIT
