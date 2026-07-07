<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    app(ModelStorage::class)->set($user1, 'foo', 111);
    app(ModelStorage::class)->set($user2, 'foo', 222);

    app(SettingsService::class, ['model' => $user1])->set('foo', 333);

    assertDatabaseHas(Settings::class, ['item_id' => $user1->getKey(), 'key' => 'foo', 'payload' => 333]);
    assertDatabaseHas(Settings::class, ['item_id' => $user2->getKey(), 'key' => 'foo', 'payload' => 222]);

    assertDatabaseCount(Settings::class, 2);
});
