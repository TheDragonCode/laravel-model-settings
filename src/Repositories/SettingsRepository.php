<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Repositories;

use DragonCode\LaravelModelSettings\Scopes\PriorityScope;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

class SettingsRepository
{
    /** @param  class-string<Model>  $modelClass */
    public function __construct(
        #[Config('model_settings.model')]
        protected string $modelClass,
        #[Config('model_settings.table')]
        protected string $table,
    ) {}

    public function store(Model $model, int|string|UnitEnum $key, mixed $value): Model
    {
        return $this->modelClass::query()->updateOrCreate([
            'item_type' => $model->getMorphClass(),
            'item_id'   => $model->getKey(),
            'key'       => $key,
        ], ['payload' => $value]);
    }

    public function all(Model $model): Collection
    {
        return $this->modelClass::query()
            ->where($this->table . '.item_type', $model->getMorphClass())
            ->tap(new PriorityScope($model->getKey()))
            ->get()
            ->pluck('payload', 'key');
    }

    public function get(Model $model, int|string|UnitEnum $key): mixed
    {
        return $this->modelClass::query()
            ->where($this->table . '.item_type', $model->getMorphClass())
            ->where($this->table . '.key', $key)
            ->tap(new PriorityScope($model->getKey()))
            ->get()
            ->value('payload');
    }

    public function delete(Model $model, int|string|UnitEnum $key): void
    {
        $this->modelClass::query()
            ->where('item_type', $model->getMorphClass())
            ->where('item_id', $model->getKey())
            ->where('key', $key)
            ->delete();
    }
}
