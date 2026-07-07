<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('new item', function () {
    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->set('foo', 123);
    app(DefaultStorage::class)->set('bar', 456);

    assertDatabaseCount(Settings::class, 2);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, ['key' => 'bar', 'payload' => 456]);
});
