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

    assertDatabaseCount(Settings::class, 2);

    $user->settings()->forget('foo');
    $user->settings()->forget('foo');

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 111]);
    assertDatabaseCount(Settings::class, 1);
});
