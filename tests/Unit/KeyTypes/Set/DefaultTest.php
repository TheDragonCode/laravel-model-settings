<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function (UnitEnum $key) {
    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set($key, 111);

    assertDatabaseCount(Settings::class, 1);

    assertDatabaseHas(Settings::class, ['key' => $key, 'payload' => 111]);
})->with('enums');
