<?php

declare(strict_types=1);

use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    $recorder = new QueryRecorder;

    $user = UserFactory::new()->create();

    $user->settings()->set('foo', 111);

    $recorder->start();

    $user->settings()->forget('foo');

    expect($recorder->calls())->toBe(1);
});
