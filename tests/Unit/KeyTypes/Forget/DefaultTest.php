<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->set(IntBackedEnum::Foo, 111);
    app(DefaultStorage::class)->set(StringBackedEnum::Bar, 222);
    app(DefaultStorage::class)->set(UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    app(DefaultStorage::class)->forget(IntBackedEnum::Foo);
    app(DefaultStorage::class)->forget(StringBackedEnum::Bar);
    app(DefaultStorage::class)->forget(UnitEnum::Baz);

    assertDatabaseEmpty(Settings::class);
});
