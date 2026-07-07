<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function (UnitEnum|string|int $key) {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set($key, 111);

    $user->settings()->set($key, 222);
    $user->settings()->set($key, 333);

    assertDatabaseHas(Settings::class, ['item_id' => $user->getKey(), 'key' => $key, 'payload' => 333]);
    assertDatabaseCount(Settings::class, 2);
})->with('setting keys');
