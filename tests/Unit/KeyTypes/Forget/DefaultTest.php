<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;
use Workbench\App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    assertDatabaseEmpty(Settings::class);

    (new User)->defaultSettings()->set(IntBackedEnum::Foo, 111);
    (new User)->defaultSettings()->set(StringBackedEnum::Bar, 222);
    (new User)->defaultSettings()->set(UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    (new User)->defaultSettings()->forget(IntBackedEnum::Foo);
    (new User)->defaultSettings()->forget(StringBackedEnum::Bar);
    (new User)->defaultSettings()->forget(UnitEnum::Baz);

    assertDatabaseEmpty(Settings::class);
});
