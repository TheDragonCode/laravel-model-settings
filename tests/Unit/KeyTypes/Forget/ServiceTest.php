<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();
    $user3 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    $user1->settings()->set(IntBackedEnum::Foo, 111);
    $user2->settings()->set(StringBackedEnum::Bar, 222);
    $user3->settings()->set(UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    $user1->settings()->forget(IntBackedEnum::Foo);
    $user2->settings()->forget(StringBackedEnum::Bar);
    $user3->settings()->forget(UnitEnum::Baz);

    assertDatabaseEmpty(Settings::class);
});
