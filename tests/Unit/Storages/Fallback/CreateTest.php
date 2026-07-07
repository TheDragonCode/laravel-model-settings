<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('new item', function () {
    $user = UserFactory::new()->create();

    $item = [
        'item_type' => $user->getMorphClass(),
        'item_id'   => $user->getKey(),
    ];

    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->set('foo', 123);
    app(DefaultStorage::class)->set('baz', 789);

    app(ModelStorage::class)->set($user, 'foo', 123);
    app(ModelStorage::class)->set($user, 'bar', 456);

    assertDatabaseCount(Settings::class, 4);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, ['key' => 'baz', 'payload' => 789]);

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, [...$item, 'key' => 'bar', 'payload' => 456]);
});
