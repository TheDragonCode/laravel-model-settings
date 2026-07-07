<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use DragonCode\LaravelModelSettings\Storages\ModelStorage;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    app(ModelStorage::class)->set($user1, 'foo', 111);
    app(ModelStorage::class)->set($user2, 'foo', 333);

    $result1 = app(SettingsService::class, ['model' => $user1])->get('foo');
    $result2 = app(SettingsService::class, ['model' => $user2])->get('foo');

    expect($result1)->toBe(111);
    expect($result2)->toBe(333);
});
