<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function (): void {
    $user = UserFactory::new()->create();

    $user->defaultSettings()->forget('foo');
    $user->settings()->forget('foo');

    assertDatabaseEmpty(Settings::class);
});
