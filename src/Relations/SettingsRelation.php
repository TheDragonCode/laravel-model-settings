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
        $results = parent::getEager();

        $defaults = $results
            ->where('item_id', IdentifierEnum::Default->value)
            ->keyBy('key');

        $changed = $results
            ->where('item_id', '!=', IdentifierEnum::Default->value)
            ->groupBy('item_id')
            ->map(function (Collection $items) use ($defaults) {
                $mapped = $items->keyBy('key');
                
                $modelId = $items->first()->getAttribute('item_id');

                return $defaults
                    ->merge($mapped)
                    ->dd()
                    ->map(function (Model $item)  {
                        if ($item->getKey() !== IdentifierEnum::Default->value) {
                            return $item;
                        }
                        
                        return $item->setAttribute('item_id', $modelId);
                    })
                    ->sortKeys()
                    ->values();
            })
            ->flatten()
            ->values();
        
        dd(
            $changed->toArray()
        );

        return new Collection($changed);
    }
}
