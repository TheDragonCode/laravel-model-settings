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

    $result1 = $user1->settings()->get(IntBackedEnum::Foo);
    $result2 = $user2->settings()->get(StringBackedEnum::Bar);
    $result3 = $user3->settings()->get(UnitEnum::Baz);

    expect($result1)->toBe(111);
    expect($result2)->toBe(222);
    expect($result3)->toBe(333);
});
