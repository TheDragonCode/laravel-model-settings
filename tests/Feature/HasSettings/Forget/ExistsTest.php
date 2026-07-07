<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function () {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set('foo', 111);

    $user->settings()->set('foo', 222);

    assertDatabaseCount(Settings::class, 2);

    $user->settings()->forget('foo');

    assertDatabaseHas(Settings::class, [
        'item_type' => $user->getMorphClass(),
        'item_id'   => 0,
        'key'       => 'foo',
        'payload'   => 111,
    ]);

    assertDatabaseCount(Settings::class, 1);
});
