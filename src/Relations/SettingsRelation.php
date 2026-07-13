<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Relations;

use DragonCode\LaravelModelSettings\Enums\IdentifierEnum;
use DragonCode\LaravelModelSettings\Scopes\PriorityScope;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Override;

class SettingsRelation extends MorphMany
{
    #[Override]
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()
                ->where($this->morphType, $this->morphClass)
                ->tap(new PriorityScope($this->parent, $this->getParentKey()));
        }
    }

    #[Override]
    public function addEagerConstraints(array $models): void
    {
        $whereIn = $this->whereInMethod($this->parent, $this->localKey);

        $keys = $this->getKeys($models, $this->localKey);
        array_unshift($keys, IdentifierEnum::Default->value);

        $this->whereInEager($whereIn, $this->foreignKey, $keys);
        $this->getRelationQuery()->where($this->morphType, $this->morphClass);
    }

    #[Override]
    public function match(array $models, EloquentCollection $results, $relation): array
    {
        $foreignKey = $this->getForeignKeyName();

        $defaults = $this->newRelatedCollection(
            $results
                ->where($foreignKey, IdentifierEnum::Default->value)
                ->all()
        );

        $overrides = $results
            ->where($foreignKey, '!=', IdentifierEnum::Default->value)
            ->groupBy($foreignKey);

        foreach ($models as $model) {
            $settings = $defaults
                ->merge($overrides->get($this->getDictionaryKey($model->getAttribute($this->localKey)), []))
                ->keyBy('key')
                ->values()
                ->all();

            $model->setRelation($relation, $this->newRelatedCollection($settings));
        }

        return $models;
    }

    /**
     * @param array<int, Model> $models
     */
    #[Override]
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->newRelatedCollection());
        }

        return $models;
    }

    protected function newRelatedCollection(array $models = []): EloquentCollection
    {
        return $this->related->newCollection($models);
    }
}
