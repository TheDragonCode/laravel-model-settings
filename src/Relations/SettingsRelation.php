<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Relations;

use DragonCode\LaravelModelSettings\Enums\IdentifierEnum;
use DragonCode\LaravelModelSettings\Scopes\PriorityScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection as BaseCollection;
use Override;

class SettingsRelation extends MorphMany
{
    protected array $eagerKeys = [];

    #[Override]
    public function addConstraints(): void
    {
        if (! static::$constraints) {
            return;
        }

        $parentKey = $this->getParentKey();

        if ($parentKey === null) {
            parent::addConstraints();

            return;
        }

        $this->getRelationQuery()
            ->where($this->morphType, $this->morphClass)
            ->tap(new PriorityScope($this->parent, $parentKey));
    }

    #[Override]
    public function getEager(): Collection
    {
        $eagerKeys = new BaseCollection($this->eagerKeys);

        $items = parent::getEager()
            ->groupBy($this->getMorphType())
            ->flatMap(
                fn (Collection $settings): BaseCollection => $this->resolveSettings($settings, $eagerKeys)
            )
            ->values();

        return $this->related->newCollection($items->all());
    }

    #[Override]
    protected function whereInMethod(Model $model, $key): string
    {
        return 'whereIn';
    }

    #[Override]
    protected function getKeys(array $models, $key = null): array
    {
        if ($this->eagerKeys !== []) {
            return $this->eagerKeys;
        }

        return $this->eagerKeys = (new BaseCollection(parent::getKeys($models, $key)))
            ->push((int) IdentifierEnum::Default->value)
            ->values()
            ->unique(null, true)
            ->sort()
            ->all();
    }

    protected function resolveSettings(Collection $settings, BaseCollection $eagerKeys): BaseCollection
    {
        [$defaults, $overrides] = $settings->partition(
            fn (Model $setting): bool => $this->isDefaultIdentifier(
                $setting->getAttribute($this->getForeignKeyName())
            )
        );

        $defaults  = $defaults->keyBy('key');
        $overrides = $overrides->groupBy($this->getForeignKeyName());

        return $eagerKeys->flatMap(
            fn (int|string $itemId): Collection => $this->resolveItemSettings($itemId, $defaults, $overrides)
        );
    }

    protected function resolveItemSettings(
        int|string $itemId,
        Collection $defaults,
        Collection $overrides,
    ): Collection {
        if ($this->isDefaultIdentifier($itemId)) {
            return $defaults->values();
        }

        $itemOverrides = $overrides
            ->get($itemId, $this->related->newCollection())
            ->keyBy('key');

        $inherited = $defaults
            ->reject(fn (Model $setting, int|string $key): bool => $itemOverrides->has($key))
            ->map(fn (Model $setting): Model => $this->inheritSetting($setting, $itemId));

        $results = $inherited->concat($itemOverrides)->values();

        return new Collection($results);
    }

    protected function inheritSetting(Model $setting, int|string $itemId): Model
    {
        return $setting
            ->replicate([$this->getForeignKeyName()])
            ->setAttribute($this->getForeignKeyName(), $itemId);
    }

    protected function isDefaultIdentifier(mixed $itemId): bool
    {
        return (string) $itemId === IdentifierEnum::Default->value;
    }
}
