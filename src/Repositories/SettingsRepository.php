<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Repositories;

use DragonCode\LaravelModelSettings\Concerns\HasModelResolver;
use DragonCode\LaravelModelSettings\Internal\SettingKey;
use DragonCode\LaravelModelSettings\Internal\SettingsScope;
use DragonCode\LaravelModelSettings\Scopes\PriorityScope;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

class SettingsRepository
{
    use HasModelResolver;

    /** @param  class-string<Model>  $modelClass */
    public function __construct(
        #[Config('model_settings.model')]
        protected string $modelClass,
    ) {}

    public function store(SettingsScope $scope, int|string|UnitEnum $key, mixed $value): Model
    {
        $scope->ensureMutable();

        return $this->modelClass::query()->updateOrCreate([
            'item_type' => $scope->itemType(),
            'item_id'   => $scope->requiredItemId(),
            'key'       => SettingKey::normalize($key),
        ], ['payload' => $value]);
    }

    public function storeMany(SettingsScope $scope, array $values, array $deletedKeys): void
    {
        $scope->ensureMutable();

        $operation = function () use ($scope, $values, $deletedKeys): void {
            $this->upsert($this->serialize($scope, $values));
            $this->deleteKeys($scope, $deletedKeys);
        };

        if ($values !== [] && $deletedKeys !== []) {
            $this->settingsModel()->getConnection()->transaction($operation);

            return;
        }

        $operation();
    }

    public function all(SettingsScope $scope): Collection
    {
        return $this->settings($scope)->pluck('payload', 'key');
    }

    public function get(SettingsScope $scope, int|string|UnitEnum $key): mixed
    {
        return $this->settings($scope)
            ->whereStrict('key', SettingKey::normalize($key))
            ->value('payload');
    }

    public function delete(SettingsScope $scope, int|string|UnitEnum $key): void
    {
        $scope->ensureMutable();

        $this->modelClass::query()
            ->where('item_type', $scope->itemType())
            ->where('item_id', $scope->requiredItemId())
            ->where('key', SettingKey::normalize($key))
            ->delete();
    }

    public function deleteMany(SettingsScope $scope, array $keys): void
    {
        $scope->ensureMutable();

        $this->deleteKeys($scope, $keys);
    }

    public function purge(SettingsScope $scope): void
    {
        $scope->ensureMutable();

        $this->scopeQuery($scope)->delete();
    }

    protected function settings(SettingsScope $scope): Collection
    {
        if (! $scope->isReadable()) {
            return $this->settingsModel()->newCollection();
        }

        if (! $scope->isDefault() && $scope->owner()->relationLoaded('modelSettings')) {
            return $scope->owner()->getRelation('modelSettings');
        }

        return $this->query($scope)->get();
    }

    protected function query(SettingsScope $scope): Builder
    {
        $query = $this->modelClass::query()
            ->where($this->settingsModel()->qualifyColumn('item_type'), $scope->itemType());

        if ($scope->isDefault()) {
            return $query
                ->where('item_id', $scope->requiredItemId())
                ->orderBy('key');
        }

        return $query->tap(
            new PriorityScope($scope->owner(), $scope->requiredItemId())
        );
    }

    protected function serialize(SettingsScope $scope, array $values): array
    {
        $rows = [];

        foreach ($values as $key => $value) {
            $model = $this->settingsModel()->newInstance();

            $model->setAttribute('item_type', $scope->itemType());
            $model->setAttribute('item_id', $scope->requiredItemId());
            $model->setAttribute('key', (string) $key);
            $model->setAttribute('payload', $value);

            $rows[] = $model->getAttributes();
        }

        return $rows;
    }

    protected function upsert(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->modelClass::query()->upsert(
            $rows,
            ['item_type', 'item_id', 'key'],
            ['payload']
        );
    }

    protected function deleteKeys(SettingsScope $scope, array $keys): void
    {
        if ($keys === []) {
            return;
        }

        $this->scopeQuery($scope)
            ->whereIn('key', $keys)
            ->delete();
    }

    protected function scopeQuery(SettingsScope $scope): Builder
    {
        return $this->modelClass::query()
            ->where('item_type', $scope->itemType())
            ->where('item_id', $scope->requiredItemId());
    }
}
