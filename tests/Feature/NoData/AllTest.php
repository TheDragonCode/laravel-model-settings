<?php

declare(strict_types=1);

use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    $user = UserFactory::new()->create();

    $result1 = $user->defaultSettings()->all();
    $result2 = $user->settings()->all();

    expect($result1)->toBeEmpty();
    expect($result2)->toBeEmpty();
});
