<?php

declare(strict_types=1);

use Workbench\App\Models\User;

test('success', function (int|string|UnitEnum $key): void {
    $settings = (new User)->defaultSettings();

    $settings->set($key, null);

    expect($settings->has($key))->toBeTrue();
})->with('setting keys');
