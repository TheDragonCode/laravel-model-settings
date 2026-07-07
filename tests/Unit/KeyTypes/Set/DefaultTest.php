<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function () {
    assertDatabaseEmpty(Settings::class);

    (new \Workbench\App\Models\User)->defaultSettings()->set(IntBackedEnum::Foo, 111);
    (new \Workbench\App\Models\User)->defaultSettings()->set(StringBackedEnum::Bar, 222);
    (new \Workbench\App\Models\User)->defaultSettings()->set(UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    assertDatabaseHas(Settings::class, ['key' => IntBackedEnum::Foo, 'payload' => 111]);
    assertDatabaseHas(Settings::class, ['key' => StringBackedEnum::Bar, 'payload' => 222]);
    assertDatabaseHas(Settings::class, ['key' => UnitEnum::Baz, 'payload' => 333]);
});
