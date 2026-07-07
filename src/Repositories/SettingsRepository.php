<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Repositories;

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

readonly class SettingsRepository
{
    public function store(string $type, int|string $id, UnitEnum|string $key, mixed $value): Model
    {
        return Settings::query()->updateOrCreate([
            'item_type' => $type,
            'item_id'   => $id,
            'key'       => $key,
        ], ['payload' => $value]);
    }

    public function all(string $type, int|string $id): Collection
    {
        return Settings::query()
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->pluck('payload', 'key');
    }

    public function get(string $type, int|string $id, UnitEnum|string $key): mixed
    {
        return Settings::query()
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->where('key', $key)
            ->value('payload');
    }

    public function delete(string $type, int|string $id, UnitEnum|string $key): void
    {
        Settings::query()
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->where('key', $key)
            ->delete();
    }
}
