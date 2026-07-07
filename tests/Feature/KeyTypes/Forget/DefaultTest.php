<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function (UnitEnum|string|int $key) {
    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set($key, 111);

    assertDatabaseCount(Settings::class, 1);

    (new User)->defaultSettings()->forget($key);

    assertDatabaseEmpty(Settings::class);
})->with('setting keys');
