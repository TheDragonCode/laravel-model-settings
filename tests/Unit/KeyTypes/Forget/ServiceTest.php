<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
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

    app(ModelStorage::class)->set($user1, IntBackedEnum::Foo, 111);
    app(ModelStorage::class)->set($user2, StringBackedEnum::Bar, 222);
    app(ModelStorage::class)->set($user3, UnitEnum::Baz, 333);

    assertDatabaseCount(Settings::class, 3);

    app(SettingsService::class, ['model' => $user1])->forget(IntBackedEnum::Foo);
    app(SettingsService::class, ['model' => $user2])->forget(StringBackedEnum::Bar);
    app(SettingsService::class, ['model' => $user3])->forget(UnitEnum::Baz);

    assertDatabaseEmpty(Settings::class);
});
