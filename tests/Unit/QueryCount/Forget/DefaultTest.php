<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;

test('success', function (): void {
    $recorder = new QueryRecorder;

    (new User)->defaultSettings()->set('foo', 111);

    $recorder->start();

    (new User)->defaultSettings()->forget('foo');

    expect($recorder->calls())->toBe(1);
});
