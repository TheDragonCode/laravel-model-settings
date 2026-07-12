<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;

test('filled', function (): void {
    $recorder = new QueryRecorder;

    (new User)->defaultSettings()->set('foo', 111);

    $recorder->start();

    (new User)->defaultSettings()->set('foo', 222);

    expect($recorder->calls())->toBe(2);
});

test('zero', function (): void {
    $recorder = new QueryRecorder;

    (new User)->defaultSettings()->set('foo', 111);

    $recorder->start();

    (new User)->defaultSettings()->set('foo', 0);

    expect($recorder->calls())->toBe(2);
});

test('empty', function (): void {
    $recorder = new QueryRecorder;

    (new User)->defaultSettings()->set('foo', 111);

    $recorder->start();

    (new User)->defaultSettings()->set('foo', null);

    expect($recorder->calls())->toBe(1);
});
