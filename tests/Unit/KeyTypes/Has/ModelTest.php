<?php

declare(strict_types=1);

use Workbench\Database\Factories\UserFactory;

test('success', function (int|string|UnitEnum $key): void {
    $settings = UserFactory::new()->create()->settings();

    $settings->set($key, null);

    expect($settings->has($key))->toBeTrue();
})->with('setting keys');
