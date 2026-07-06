<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function getTableData(string|Model $table): array
{
    $model = $table instanceof Model ? $table : new $table;

    $name    = $model->getTable();
    $primary = $model->getKeyName();

    $columns = Schema::getColumns($name);

    $filtered = new Collection($columns)
        ->pluck('name')
        ->diff([$primary])
        ->values()
        ->all();

    return DB::table($name)
        ->get($filtered)
        ->all();
}
