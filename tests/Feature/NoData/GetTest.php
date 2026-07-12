<?php

declare(strict_types=1);

use Workbench\Database\Factories\UserFactory;

test('success', function (): void {
    $user = UserFactory::new()->create();

    $result = $user->defaultSettings()->get('foo');

    expect($result)->toBeNull();
});
