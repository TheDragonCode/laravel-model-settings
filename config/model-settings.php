<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;

return [
    'default' => env('MODEL_SETTINGS_REPOSITORY'),

    'repositories' => [
        'database' => [
            'model' => Settings::class,

            'connection' => env('MODEL_SETTINGS_DATABASE_CONNECTION', env('DATABASE_CONNECTION')),
            'table'      => env('MODEL_SETTINGS_DATABASE_TABLE', 'settings'),
            'cast'       => env('MODEL_SETTINGS_DATABASE_CAST', 'json'),
        ],

        'redis' => [
            'connection' => env('MODEL_SETTINGS_REDIS_CONNECTION'),
            'prefix'     => env('MODEL_SETTINGS_REDIS_PREFIX', '_settings_'),
            'cast'       => env('MODEL_SETTINGS_REDIS_CAST', 'json'),
        ],

        'file' => [
            'disk'      => env('MODEL_SETTINGS_FILE_DISK', 'local'),
            'directory' => env('MODEL_SETTINGS_FILE_DIRECTORY', 'settings'),
            'cast'      => env('MODEL_SETTINGS_REDIS_CAST', 'json'),
        ],
    ],

    'cache' => [
        'enabled'   => env('MODEL_SETTINGS_CACHE_ENABLED', false),
        'prefix'    => env('MODEL_SETTINGS_CACHE_PREFIX', 'settings'),
        'ttl'       => env('MODEL_SETTINGS_CACHE_TTL', 60),
        'hash_keys' => env('MODEL_SETTINGS_CACHE_HASH_KEYS', false),
    ],

    'settings' => [
        // Default Application Settings

        'laravel' => [],

        // Default User Settings

        'users' => [],
    ],
];
