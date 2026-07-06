<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasSettings
{
    public function settings(): Relation
    {
        return $this
            ->morphMany(Settings::class, 'item')
            ->orWhere(static fn (Builder $query) => $query
                ->where('item_type', '_default')
                ->where('item_id', 0)
            )
            ->orderByDesc('item_id');
    }
}
