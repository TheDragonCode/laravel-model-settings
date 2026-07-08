<?php

declare(strict_types=1);

use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('success', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    $user->settings()->set('foo', 111);
    $user->settings()->set('bar', 222);

    $recorder->start();

    $user->settings()->get('foo');

    expect($recorder->calls())->toBe(1);
});
