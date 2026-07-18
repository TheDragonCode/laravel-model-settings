<?php

declare(strict_types=1);

use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('stored values use one query regardless of batch size', function (int $size): void {
    $user   = UserFactory::new()->create();
    $values = [];

    for ($index = 1; $index <= $size; $index++) {
        $values['key-' . $index] = $index;
    }

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->setMany($values);

    expect($recorder->calls())->toBe(1);
})->with([1, 100]);

test('null values use one query regardless of batch size', function (): void {
    $user     = UserFactory::new()->create();
    $existing = [];
    $values   = [];

    for ($index = 1; $index <= 100; $index++) {
        $existing['key-' . $index] = $index;
        $values['key-' . $index]   = null;
    }

    $user->settings()->setMany($existing);

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->setMany($values);

    expect($recorder->calls())->toBe(1);
});

test('mixed values use one upsert', function (): void {
    $user     = UserFactory::new()->create();
    $existing = [];
    $values   = [];

    for ($index = 1; $index <= 100; $index++) {
        $existing['delete-' . $index] = $index;
        $values['delete-' . $index]   = null;
        $values['store-' . $index]    = $index;
    }

    $user->settings()->setMany($existing);

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->setMany($values);

    expect($recorder->calls())->toBe(1);
});

test('empty values use no queries', function (): void {
    $user = UserFactory::new()->create();

    $recorder = new QueryRecorder;
    $recorder->start();

    $user->settings()->setMany([]);

    expect($recorder->calls())->toBe(0);
});
