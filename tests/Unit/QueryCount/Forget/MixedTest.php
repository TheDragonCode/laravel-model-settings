<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('default', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', 111);

    $recorder->start();

    (new User)->defaultSettings()->forget('foo');

    expect($recorder->calls())->toBe(1);
});

test('model', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', 111);

    $recorder->start();

    $user->settings()->forget('foo');

    expect($recorder->calls())->toBe(1);
});
