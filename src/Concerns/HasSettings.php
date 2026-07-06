<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Relations\Relation;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasSettings
{
    public function settings(): Relation
    {
        return $this
            ->morphMany(Settings::class, 'item')
            ->orderByDesc('sort_order');
    }
}
