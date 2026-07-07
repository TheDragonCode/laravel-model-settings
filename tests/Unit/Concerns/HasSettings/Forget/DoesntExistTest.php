<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    $user->settings()->forget('foo');

    assertDatabaseEmpty(Settings::class);
});
