<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Drivers\Driver;
use DragonCode\LaravelModelSettings\Drivers\Manager;
use DragonCode\LaravelModelSettings\Models\Laravel;
use Illuminate\Database\Eloquent\Model;

if (! function_exists('settings')) {
    function settings(?Model $model = null): Driver
    {
        return app(Manager::class)->driver(
            config('model-settings.default')
        )->forModel($model ?? new Laravel());
    }
}
