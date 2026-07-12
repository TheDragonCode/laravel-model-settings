<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function (): void {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set('foo', 111);
    (new User)->defaultSettings()->set('bar', 222);

    $user1->settings()->set('bar', 333);
    $user2->settings()->set('bar', 444);

    $result1 = $user1->settings()->all()->toArray();
    $result2 = $user2->settings()->all()->toArray();

    ksort($result1);
    ksort($result2);

    expect($result1)->toBe(['bar' => 333, 'foo' => 111]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
});
