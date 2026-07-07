<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Services\SettingsService;

use function app;

trait HasSettings
{
    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class, ['model' => $this]);
    }
}
