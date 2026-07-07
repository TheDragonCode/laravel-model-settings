<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function () {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->set('foo', 111);
    app(ModelStorage::class)->set($user, 'foo', 222);

    $user->settings()->set('foo', 333);

    assertDatabaseHas(Settings::class, ['item_id' => $user->getKey(), 'key' => 'foo', 'payload' => 333]);
    assertDatabaseCount(Settings::class, 2);
});
