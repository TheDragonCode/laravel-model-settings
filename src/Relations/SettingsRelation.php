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
    protected ?array $cachedModelKeys = null;

    protected function whereInMethod(Model $model, $key): string
    {
        return 'whereIn';
    }

    protected function getKeys(array $models, $key = null): array
    {
        return $this->cachedModelKeys ??= (new BaseCollection($models))
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

                if ($specific->isEmpty()) {
                    return $defaults->values();
                }

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

    protected function matchOneOrMany(array $models, Collection $results, $relation, $type): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $modelId = $model->getAttribute($this->localKey);

            $key1 = $this->getDictionaryKey($modelId);
            $key2 = $this->getDictionaryKey(IdentifierEnum::Default->value);

            if ($key1 === null && $key2 === null) {
                continue;
            }

            $related = match (true) {
                isset($dictionary[$key1]) => $this->getRelationValue($dictionary, $key1, $type),
                isset($dictionary[$key2]) => $this->getRelationValue($dictionary, $key2, $type),
                default                   => null
            };

            if ($related === null) {
                continue;
            }

            $related = $related
                ->map(function (Model $model) use ($modelId): ?Model {
                    $localId = $model->getAttribute($this->localKey);

                    if ($localId === $modelId) {
                        return $model;
                    }

                    if ((string) $localId === IdentifierEnum::Default->value) {
                        return $model
                            ->replicate(['id', 'item_id'])
                            ->setAttribute('item_id', $modelId);
                    }

                    return null;
                })
                ->filter()
                ->values();

            $model->setRelation($relation, $related);

            $type === 'one'
                ? $this->applyInverseRelationToModel($related, $model)
                : $this->applyInverseRelationToCollection($related, $model);
        }

        return $models;
    }
}
