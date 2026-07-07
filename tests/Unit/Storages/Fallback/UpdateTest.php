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
    app(ModelStorage::class)->set($user, 'foo', 123);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 123]);

    app(ModelStorage::class)->set($user, 'foo', 456);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 123]);
    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 2);
});
