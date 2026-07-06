<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Concerns;

use DragonCode\LaravelModelSettings\Constants\DefaultConstant;
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
                ->where('item_type', DefaultConstant::Type)
                ->where('item_id', DefaultConstant::Id)
            )
            ->orderByDesc('item_id');
    }
}
