<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function () {
    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->set(IntBackedEnum::Foo, 111);
    app(DefaultStorage::class)->set(StringBackedEnum::Bar, 222);
    app(DefaultStorage::class)->set(UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    assertDatabaseHas(Settings::class, ['key' => IntBackedEnum::Foo, 'payload' => 111]);
    assertDatabaseHas(Settings::class, ['key' => StringBackedEnum::Bar, 'payload' => 222]);
    assertDatabaseHas(Settings::class, ['key' => UnitEnum::Baz, 'payload' => 333]);
});
