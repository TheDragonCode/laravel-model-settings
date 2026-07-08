<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;

return [

    /*
    |--------------------------------------------------------------------------
    | Settings Model
    |--------------------------------------------------------------------------
    |
    | This option controls the Eloquent model that will be used to store and
    | retrieve model settings. You may use your own model when your
    | application needs custom behavior for persisted settings records.
    |
    */

    'model' => Settings::class,

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify which database connection should be used to store
    | model settings. When this option is null, the default database
    | connection for your application will be used by the package.
    |
    */

    'connection' => env('MODEL_SETTINGS_DATABASE_CONNECTION', env('DATABASE_CONNECTION')),

    /*
    |--------------------------------------------------------------------------
    | Database Table
    |--------------------------------------------------------------------------
    |
    | Here you may specify the database table that will store the settings for
    | your models. The package migration will use this table name when
    | creating or dropping the model settings table.
    |
    */

    'table' => env('MODEL_SETTINGS_DATABASE_TABLE', 'settings'),

    /*
     * By default, json.
     */
    'casts' => [
        // App\Models\User::class => App\Data\Settings\UserPayload::class,
        // App\Models\Post::class => App\Data\Settings\PostPayload::class,
    ],
];
