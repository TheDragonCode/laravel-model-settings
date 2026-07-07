<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

test('success', function () {
    $user = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    (new \Workbench\App\Models\User)->defaultSettings()->set('foo', 111);
    $user->settings()->set('foo', 222);

    assertDatabaseCount(Settings::class, 2);

    app(SettingsService::class, ['model' => $user])->forget('foo');
    app(SettingsService::class, ['model' => $user])->forget('foo');

    assertDatabaseHas(Settings::class, ['key' => 'foo', 'payload' => 111]);
    assertDatabaseCount(Settings::class, 1);
});
