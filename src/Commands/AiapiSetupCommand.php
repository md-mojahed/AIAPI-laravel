<?php

namespace Mojahed\AIAPI\Commands;

use Illuminate\Console\Command;

class AiapiSetupCommand extends Command
{
    protected $signature   = 'aiapi:setup';
    protected $description = 'Publish the mojahed/aiapi config file';

    public function handle(): void
    {
        $this->call('vendor:publish', [
            '--tag'   => 'aiapi-config',
            '--force' => false,
        ]);

        $this->info('');
        $this->info('✅ mojahed/aiapi config published to config/aiapi.php');
        $this->info('');
        $this->info('Next steps:');
        $this->info('  1. Add your API keys to .env');
        $this->info('  2. Set AIAPI_PROVIDER in .env (default: openrouter)');
        $this->info('  3. Set AIAPI_MODEL in .env');
        $this->info('  4. Run: php artisan aiapi:test');
        $this->info('');
        $this->line('Example .env entries:');
        $this->line('  AIAPI_PROVIDER=openrouter');
        $this->line('  AIAPI_MODEL=z-ai/glm-5.2');
        $this->line('  OPENROUTER_API_KEY=sk-or-...');
    }
}
