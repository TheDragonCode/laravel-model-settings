<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('model purge uses one query', function (): void {
    $user = UserFactory::new()->create();

    $user->settings()->setMany([
        'first'  => 1,
        'second' => 2,
    ]);

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->purge();

    expect($recorder->calls())->toBe(1);
});

test('default purge uses one query', function (): void {
    (new User)->defaultSettings()->setMany([
        'first'  => 1,
        'second' => 2,
    ]);

    $recorder = new QueryRecorder;
    $recorder->start();

    (new User)->defaultSettings()->purge();

    expect($recorder->calls())->toBe(1);
});
