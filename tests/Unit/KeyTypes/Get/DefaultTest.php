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

    $result1 = app(DefaultStorage::class)->get(IntBackedEnum::Foo);
    $result2 = app(DefaultStorage::class)->get(StringBackedEnum::Bar);
    $result3 = app(DefaultStorage::class)->get(UnitEnum::Baz);

    expect($result1)->toBe(111);
    expect($result2)->toBe(222);
    expect($result3)->toBe(333);
});
