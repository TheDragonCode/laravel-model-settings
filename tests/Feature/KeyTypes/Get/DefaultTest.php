<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function (int|string|UnitEnum $key) {
    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set($key, 111);

    assertDatabaseCount(Settings::class, 1);

    $result = (new User)->defaultSettings()->get($key);

    expect($result)->toBe(111);
})->with('setting keys');
