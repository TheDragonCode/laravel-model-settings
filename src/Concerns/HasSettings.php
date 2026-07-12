<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Enums\IdentifierEnum;
use DragonCode\LaravelModelSettings\Relations\SettingsRelation;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use function app;

/** @mixin Model */
trait HasSettings
{
    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class, ['model' => $this]);
    }

    public function defaultSettings(): SettingsService
    {
        $clone = new static;
        $clone->setAttribute($clone->getKeyName(), IdentifierEnum::Default->value);

        return app()->make(SettingsService::class, ['model' => $clone]);
    }

    public function modelSettings(): Relation
    {
        return new SettingsRelation();
    }
}
