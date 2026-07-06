<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;

return [
    'database' => [
        'model' => Settings::class,

        'connection' => env('MODEL_SETTINGS_DATABASE_CONNECTION', env('DATABASE_CONNECTION')),
        'table'      => env('MODEL_SETTINGS_DATABASE_TABLE'),
    ],
];
