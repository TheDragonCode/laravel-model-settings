<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('success', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);
    (new User)->defaultSettings()->set('bar', 222);

    $user->settings()->set('foo', 111);
    $user->settings()->set('bar', 222);

    $recorder->start();

    $user->settings()->all();

    expect($recorder->calls())->toBe(1);
});
