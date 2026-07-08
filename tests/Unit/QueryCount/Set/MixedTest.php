<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('default', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);

    $recorder->start();

    $user->settings()->set('foo', 333);

    expect($recorder->calls())->toBe(2);
});

test('model', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', 222);

    $recorder->start();

    $user->settings()->set('foo', 333);

    expect($recorder->calls())->toBe(2);
});

test('zero', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', 222);

    $recorder->start();

    $user->settings()->set('foo', 0);

    expect($recorder->calls())->toBe(2);
});

test('empty', function () {
    $recorder = new QueryRecorder();

    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', 222);

    $recorder->start();

    $user->settings()->set('foo', null);

    expect($recorder->calls())->toBe(1);
});
