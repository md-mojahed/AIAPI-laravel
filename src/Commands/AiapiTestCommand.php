<?php

namespace Mojahed\AIAPI\Commands;

use Illuminate\Console\Command;
use Mojahed\AIAPI\AI;

class AiapiTestCommand extends Command
{
    protected $signature   = 'aiapi:test {--provider= : Provider name to test} {--question= : Test question}';
    protected $description = 'Test your AI API connection';

    public function handle(): void
    {
        $provider = $this->option('provider') ?? config('aiapi.provider');
        $question = $this->option('question') ?? 'Say hello in one sentence.';

        $this->info('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  mojahed/aiapi — Connection Test');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("  Provider : {$provider}");
        $this->info("  Model    : " . config('aiapi.model'));
        $this->info("  Question : {$question}");
        $this->info('');

        try {
            $ai = (new AI())->setProvider($provider);
            $response = $ai->ask($question);

            $this->info('✅ Success!');
            $this->info('');
            $this->line("Answer     : " . $response->answer);
            $this->line("Summary    : " . $response->summary);
            $this->line("Model      : " . $response->model);
            $this->line("Tokens     : " . $response->tokensUsed);

        } catch (\Exception $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
        }

        $this->info('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
