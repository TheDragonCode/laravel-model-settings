<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Services\SettingsService;

use function app;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasSettings
{
    use HasIdentifier;

    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class, ['model' => $this]);
    }

    public function defaultSettings(): SettingsService
    {
        $clone = new static;
        $clone->setAttribute($clone->getKeyName(), $this->defaultId($this));

        return app()->make(SettingsService::class, ['model' => $clone]);
    }
}
