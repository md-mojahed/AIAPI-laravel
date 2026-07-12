<?php

namespace Mojahed\AIAPI;

use Illuminate\Support\ServiceProvider;
use Mojahed\AIAPI\Commands\AiapiSetupCommand;
use Mojahed\AIAPI\Commands\AiapiTestCommand;

class AIAPIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/aiapi.php',
            'aiapi'
        );

        // Bind AI as a fresh instance per resolution (avoids state leaks in long-running workers)
        $this->app->bind(AI::class, fn () => new AI());
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/aiapi.php' => config_path('aiapi.php'),
            ], 'aiapi-config');

            // Register artisan commands
            $this->commands([
                AiapiSetupCommand::class,
                AiapiTestCommand::class,
            ]);
        }
    }
}
