<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function (int|string|UnitEnum $key): void {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    $user->settings()->set($key, 111);

    assertDatabaseCount(Settings::class, 1);

    assertDatabaseHas(Settings::class, ['key' => $key, 'payload' => 111]);
})->with('setting keys');
