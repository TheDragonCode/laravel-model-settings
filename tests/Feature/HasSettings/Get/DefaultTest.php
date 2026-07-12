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

    $result1 = $user1->settings()->get('foo');
    $result2 = $user2->settings()->get('foo');

    expect($result1)->toBe(111);
    expect($result2)->toBe(111);
});
