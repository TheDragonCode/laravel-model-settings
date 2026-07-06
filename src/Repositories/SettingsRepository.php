<?php

declare(strict_types=1);

namespace DragonCode\LaravelModelSettings\Repositories;

use BackedEnum;
use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Database\Eloquent\Model;

readonly class SettingsRepository
{
    public function store(string $type, int|string $id, BackedEnum|string $key, mixed $value): Model
    {
        return Settings::query()->updateOrCreate([
            'item_type' => $type,
            'item_id'   => $id,
            'key'       => $key,
        ], ['payload' => $value]);
    }

    public function delete(string $type, int|string $id, BackedEnum|string $key): void
    {
        Settings::query()
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->where('key', $key)
            ->delete();
    }
}
