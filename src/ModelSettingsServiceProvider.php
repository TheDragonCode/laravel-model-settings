<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings;

use Illuminate\Support\ServiceProvider;
use Override;

final class ModelSettingsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/model_settings.php',
            'model_settings'
        );
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/model_settings.php' => $this->app->configPath('model_settings.php'),
        ], 'model_settings');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
        ], 'model_settings');
    }
}
