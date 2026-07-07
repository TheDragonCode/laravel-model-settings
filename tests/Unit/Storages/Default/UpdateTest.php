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

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);

    app(DefaultStorage::class)->set('foo', 456);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 1);
});
