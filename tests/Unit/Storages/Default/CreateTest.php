<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('new item', function () {
    assertDatabaseEmpty(Settings::class);

    (new \Workbench\App\Models\User)->defaultSettings()->set('foo', 123);
    (new \Workbench\App\Models\User)->defaultSettings()->set('bar', 456);

    assertDatabaseCount(Settings::class, 2);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, ['key' => 'bar', 'payload' => 456]);
});
