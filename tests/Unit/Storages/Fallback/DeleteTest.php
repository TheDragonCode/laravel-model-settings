<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
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

    app(DefaultStorage::class)->store('foo', 123);
    app(DefaultStorage::class)->store('bar', 456);

    app(ModelStorage::class)->store($user, 'foo', 123);
    app(ModelStorage::class)->store($user, 'bar', 456);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, ['key' => 'bar', 'payload' => 456]);

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 4);

    app(ModelStorage::class)->delete($user, 'foo');

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, ['key' => 'bar', 'payload' => 456]);

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 3);
});

test('second', function () {
    $user = UserFactory::new()->create();

    $item = [
        'item_type' => $user->getMorphClass(),
        'item_id'   => $user->getKey(),
    ];

    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->store('foo', 123);
    app(DefaultStorage::class)->store('bar', 456);

    app(ModelStorage::class)->store($user, 'foo', 123);
    app(ModelStorage::class)->store($user, 'bar', 456);

    assertDatabaseCount(Settings::class, 4);

    app(ModelStorage::class)->delete($user, 'foo');
    app(ModelStorage::class)->delete($user, 'foo');

    assertDatabaseHas(Settings::class, ['key' => 'bar', 'payload' => 456]);
    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 3);
});
