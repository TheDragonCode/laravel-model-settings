<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

class SettingsRepository
{
    public function store(Model $model, UnitEnum|string|int $key, mixed $value): Model
    {
        return $model->modelSettings()->updateOrCreate([
            'key' => $key,
        ], ['payload' => $value]);
    }

    public function all(Model $model): Collection
    {
        return $model->modelSettings()->pluck('payload', 'key');
    }

    public function get(Model $model, UnitEnum|string|int $key): mixed
    {
        return $model->modelSettings()
            ->where('key', $key)
            ->value('payload');
    }

    public function delete(Model $model, UnitEnum|string|int $key): void
    {
        $model->modelSettings()
            ->where('key', $key)
            ->delete();
    }
}
