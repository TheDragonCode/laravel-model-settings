<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('first', function () {
    $user = UserFactory::new()->create();

    $item = [
        'item_type' => $user->getMorphClass(),
        'item_id'   => $user->getKey(),
    ];

    assertDatabaseEmpty(Settings::class);

    app(ModelStorage::class)->set($user, 'foo', 123);
    app(ModelStorage::class)->set($user, 'bar', 456);

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 2);

    app(ModelStorage::class)->forget($user, 'foo');

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 1);
});

test('second', function () {
    $user = UserFactory::new()->create();

    $item = [
        'item_type' => $user->getMorphClass(),
        'item_id'   => $user->getKey(),
    ];

    assertDatabaseEmpty(Settings::class);

    app(ModelStorage::class)->set($user, 'foo', 123);
    app(ModelStorage::class)->set($user, 'bar', 456);

    assertDatabaseCount(Settings::class, 2);

    app(ModelStorage::class)->forget($user, 'foo');
    app(ModelStorage::class)->forget($user, 'foo');

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 1);
});
