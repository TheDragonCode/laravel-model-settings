<?php

declare(strict_types=1);

use Workbench\App\Models\User;

test('unsaved model does not inherit default settings', function (): void {
    (new User)->defaultSettings()->set('foo', 111);

    $settings = (new User)->settings()->all();

    expect($settings)->toBeEmpty();
});
