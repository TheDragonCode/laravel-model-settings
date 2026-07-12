<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function (): void {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    $user->settings()->set('foo', 111);

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 111]);
    assertDatabaseCount(Settings::class, 1);
});
