<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @throws JsonException
 */
function createSettings(Model $model, ?array $settings): void
{
    $table = DB::table(config('model-settings.repositories.database.table'));

    $data = [
        'item_type' => $model->getMorphClass(),
        'item_id'   => $model->getKey(),
        'payload'   => json_encode($settings, JSON_THROW_ON_ERROR),
    ];

    blank($settings)
        ? $table->delete()
        : $table->insert($data);
}
