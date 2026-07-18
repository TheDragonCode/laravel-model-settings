<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Relations;

use DragonCode\LaravelModelSettings\Scopes\PriorityScope;
use Illuminate\Database\Eloquent\Builder;
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

        if (! $this->parent->exists) {
            parent::addConstraints();
            $this->getRelationQuery()->whereRaw('0 = 1');

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
    public function addEagerConstraints(array $models): void
    {
        $keys = $this->getKeys($models, $this->localKey);

        if ($keys === []) {
            $this->eagerKeysWereEmpty = true;

            return;
        }

        $isDefault = $this->related->qualifyColumn('is_default');
        $itemId    = $this->getQualifiedForeignKeyName();

        $this->getRelationQuery()
            ->where($this->morphType, $this->morphClass)
            ->where(
                fn (Builder $query) => $query
                    ->where($isDefault, true)
                    ->orWhere(
                        fn (Builder $query) => $query
                            ->where($isDefault, false)
                            ->whereIn($itemId, $keys)
                    )
            );
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
        $models = (new BaseCollection($models))
            ->filter(fn (Model $model): bool => $model->exists)
            ->all();

        $keys = (new BaseCollection(parent::getKeys($models, $key)))
            ->map(fn (int|string $itemId): string => (string) $itemId);

        if ($keys->isEmpty()) {
            return [];
        }

        return $this->eagerKeys = $keys
            ->values()
            ->unique(null, true)
            ->sort()
            ->all();
    }

    protected function resolveSettings(Collection $settings, BaseCollection $eagerKeys): BaseCollection
    {
        [$defaults, $overrides] = $settings->partition(
            fn (Model $setting): bool => $setting->getAttribute('is_default') === true
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
}
