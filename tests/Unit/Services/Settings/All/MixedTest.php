<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use DragonCode\LaravelModelSettings\Storages\DefaultStorage;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    app(DefaultStorage::class)->set('foo', 111);
    app(DefaultStorage::class)->set('bar', 222);

    app(ModelStorage::class)->set($user1, 'bar', 333);
    app(ModelStorage::class)->set($user2, 'bar', 444);

    $result1 = app(SettingsService::class, ['model' => $user1])->all()->toArray();
    $result2 = app(SettingsService::class, ['model' => $user2])->all()->toArray();

    ksort($result1);
    ksort($result2);

    expect($result1)->toBe(['bar' => 333, 'foo' => 111]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 111]);
});
