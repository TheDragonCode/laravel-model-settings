<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('success', function (): void {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    $user2->settings()->set('foo', 'bar');

    $user1->defaultSettings()->forget('foo');
    $user1->settings()->forget('foo');

    assertDatabaseMissing(Settings::class, ['item_id' => $user1->id]);
    assertDatabaseHas(Settings::class, ['item_id' => $user2->id]);
});
