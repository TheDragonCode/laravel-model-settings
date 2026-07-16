<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Relations;

use DragonCode\LaravelModelSettings\Enums\IdentifierEnum;
use DragonCode\LaravelModelSettings\Scopes\PriorityScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection as BaseCollection;

class SettingsRelation extends MorphMany
{
    protected array $eagerKeys = [];

    public function addConstraints(): void
    {
        if (! static::$constraints) {
            return;
        }

        if ($this->getParentKey() === null) {
            parent::addConstraints();

            return;
        }

        $this->getRelationQuery()
            ->where($this->morphType, $this->morphClass)
            ->tap(new PriorityScope($this->parent, $this->getParentKey()));
    }

    protected function whereInMethod(Model $model, $key): string
    {
        return 'whereIn';
    }

    protected function getKeys(array $models, $key = null): array
    {
        if ($this->eagerKeys) {
            return $this->eagerKeys;
        }

        return $this->eagerKeys = (new BaseCollection(parent::getKeys($models, $key)))
            ->push((int) IdentifierEnum::Default->value)
            ->values()
            ->unique(null, true)
            ->sort()
            ->all();
    }

    public function getEager(): Collection
    {
        $eagerKeys = new BaseCollection($this->eagerKeys);

        $items = parent::getEager()
            ->groupBy('item_type')
            ->flatMap(function (Collection $groups) use ($eagerKeys) {
                [$defaults, $specific] = $groups->partition(
                    fn (Model $item): bool => (string) $item->getAttribute('item_id') === IdentifierEnum::Default->value
                );

                $defaults = $defaults->keyBy('key');
                $specific = $specific->groupBy('item_id');

                return $eagerKeys
                    ->flatMap(function (int|string $itemId) use ($defaults, $specific): Collection {
                        if ((string) $itemId === IdentifierEnum::Default->value) {
                            return $defaults->values();
                        }

                        $overrides = $specific
                            ->get($itemId, new Collection)
                            ->keyBy('key');

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

        return $this->related->newCollection($items->all());
    }
}
