<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function (): void {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    $user1->settings()->set('foo', 111);
    $user2->settings()->set('foo', 333);

    $result1 = $user1->settings()->get('foo');
    $result2 = $user2->settings()->get('foo');

    expect($result1)->toBe(111);
    expect($result2)->toBe(333);
});
