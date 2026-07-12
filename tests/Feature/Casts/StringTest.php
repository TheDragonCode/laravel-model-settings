<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    config()->set('model_settings.casts.' . User::class, 'string');

    $user = UserFactory::new()->create();

    $user->defaultSettings()->set('foo', '111');
    $user->settings()->set('foo', '222');

    $result1 = $user->defaultSettings()->get('foo');
    $result2 = $user->settings()->get('foo');

    expect($result1)->toBe('111');
    expect($result2)->toBe('222');
});
