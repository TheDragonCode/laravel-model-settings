<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    assertDatabaseEmpty(Settings::class);

    (new \Workbench\App\Models\User)->defaultSettings()->set(IntBackedEnum::Foo, 111);
    (new \Workbench\App\Models\User)->defaultSettings()->set(StringBackedEnum::Bar, 222);
    (new \Workbench\App\Models\User)->defaultSettings()->set(UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    $result1 = (new \Workbench\App\Models\User)->defaultSettings()->get(IntBackedEnum::Foo);
    $result2 = (new \Workbench\App\Models\User)->defaultSettings()->get(StringBackedEnum::Bar);
    $result3 = (new \Workbench\App\Models\User)->defaultSettings()->get(UnitEnum::Baz);

    expect($result1)->toBe(111);
    expect($result2)->toBe(222);
    expect($result3)->toBe(333);
});
