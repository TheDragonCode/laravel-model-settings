<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Repositories;

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
            ->where('item_type', $model->getMorphClass())
            ->whereIn('item_id', [$model->getKey(), 0])
            ->pluck('payload', 'key');
    }

    public function get(Model $model, int|string|UnitEnum $key): mixed
    {
        return $this->modelClass::query()
            ->where('item_type', $model->getMorphClass())
            ->whereIn('item_id', [$model->getKey(), 0])
            ->where('key', $key)
            ->orderByDesc('item_id')
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
