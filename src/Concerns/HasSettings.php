<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Drivers\Driver;
use Illuminate\Database\Eloquent\Relations\Relation;

use function config;
use function settings;

/**
 * @property \DragonCode\LaravelModelSettings\Models\Settings $modelSettings
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSettings
{
    public function settings(): Driver
    {
        return settings($this);
    }

    public function modelSettings(): Relation
    {
        return $this->morphOne(config('model-settings.repositories.database.model'), 'item');
    }
}
