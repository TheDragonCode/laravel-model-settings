<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function (UnitEnum|string|int $key) {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    $user->settings()->set($key, 111);

    assertDatabaseCount(Settings::class, 1);

    $result = $user->settings()->get($key);

    expect($result)->toBe(111);
})->with('setting keys');
