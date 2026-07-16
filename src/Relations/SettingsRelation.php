<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Relations;

use DragonCode\LaravelModelSettings\Enums\IdentifierEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection as BaseCollection;

class SettingsRelation extends MorphMany
{
    protected function whereInMethod(Model $model, $key): string
    {
        return 'whereIn';
    }

    protected function getKeys(array $models, $key = null)
    {
        return (new BaseCollection($models))
            ->map(fn (Model $value) => $key ? $value->getAttribute($key) : $value->getKey())
            ->push((int) IdentifierEnum::Default->value)
            ->values()
            ->unique(null, true)
            ->sort()
            ->all();
    }

    public function getEager(): Collection
    {
        $items = parent::getEager()
            ->groupBy('item_type')
            ->flatMap(function (Collection $groups) {
                [$defaults, $specific] = $groups->partition(
                    fn (Model $item): bool => (string) $item->getAttribute('item_id') === IdentifierEnum::Default->value
                );

                $defaults = $defaults->keyBy('key');

                return $specific
                    ->groupBy('item_id')
                    ->flatMap(function (Collection $items) use ($defaults): Collection {

                        $overrides = $items->keyBy('key');

                        $itemId = $items->first()->getAttribute('item_id');

                        $inherited = $defaults
                            ->reject(fn (Model $item, string $key): bool => $overrides->has($key))
                            ->map(
                                fn (Model $item): Model => $item
                                    ->replicate(['id', 'item_id'])
                                    ->setAttribute('item_id', $itemId)
                            );

                        return $inherited->concat($overrides)->values();
                    });
            })
            ->values();

        return new Collection($items);
    }
}
