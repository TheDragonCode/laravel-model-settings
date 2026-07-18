<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('model forgetMany uses one query regardless of key count', function (): void {
    $user   = UserFactory::new()->create();
    $values = [];

    for ($index = 1; $index <= 100; $index++) {
        $values['key-' . $index] = $index;
    }

    $user->settings()->setMany($values);

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->forgetMany(array_keys($values));

    expect($recorder->calls())->toBe(1);
});

test('default forgetMany uses one query regardless of key count', function (): void {
    $values = [];

    for ($index = 1; $index <= 100; $index++) {
        $values['key-' . $index] = $index;
    }

    (new User)->defaultSettings()->setMany($values);

    $recorder = new QueryRecorder;
    $recorder->start();

    (new User)->defaultSettings()->forgetMany(array_keys($values));

    expect($recorder->calls())->toBe(1);
});

test('empty forgetMany uses no queries', function (): void {
    $user = UserFactory::new()->create();

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->forgetMany([]);

    expect($recorder->calls())->toBe(0);
});
