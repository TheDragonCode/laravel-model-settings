<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
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

    $user->settings()->set('foo', 123);

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 123]);

    $user->settings()->set('foo', 456);

    assertDatabaseHas(Settings::class, [...$item, 'key' => 'foo', 'payload' => 456]);

    assertDatabaseCount(Settings::class, 1);
});
