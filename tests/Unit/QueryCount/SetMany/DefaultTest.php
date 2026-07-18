<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;

test('default stored values use one query', function (): void {
    $values = [];

    for ($index = 1; $index <= 100; $index++) {
        $values['key-' . $index] = $index;
    }

    $recorder = new QueryRecorder;
    $recorder->start();

    (new User)->defaultSettings()->setMany($values);

    expect($recorder->calls())->toBe(1);
});

test('default mixed values use one query', function (): void {
    $existing = [];
    $values   = [];

    for ($index = 1; $index <= 100; $index++) {
        $existing['delete-' . $index] = $index;
        $values['delete-' . $index]   = null;
        $values['store-' . $index]    = $index;
    }

    (new User)->defaultSettings()->setMany($existing);

    $recorder = new QueryRecorder;
    $recorder->start();

    (new User)->defaultSettings()->setMany($values);

    expect($recorder->calls())->toBe(1);
});
