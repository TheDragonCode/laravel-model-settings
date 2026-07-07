<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Services\SettingsService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use function app;
use function config;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasSettings
{
    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class, ['model' => $this]);
    }

    public function defaultSettings(): SettingsService
    {
        $clone = $this->replicateQuietly(['id']);
        $clone->setAttribute($clone->getKeyName(), 0);

        return app()->make(SettingsService::class, ['model' => $clone]);
    }

    public function modelSettings(): MorphMany
    {
        return $this->morphMany(config()->string('model-settings.model'), 'item')
            ->orderByDesc('id');
    }
}
