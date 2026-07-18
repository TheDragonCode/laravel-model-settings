<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Relations\SettingsRelation;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use function app;
use function config;

/** @mixin Model */
trait HasSettings
{
    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class, ['model' => $this]);
    }

    public function defaultSettings(): SettingsService
    {
        return app()->make(SettingsService::class, [
            'model'        => new static,
            'defaultScope' => true,
        ]);
    }

    public function modelSettings(): Relation
    {
        $instance = $this->newRelatedInstance(
            config()->string('model_settings.model')
        );

        return new SettingsRelation(
            $instance->newQuery(),
            $this,
            $instance->qualifyColumn('item_type'),
            $instance->qualifyColumn('item_id'),
            $this->getKeyName()
        );
    }
}
