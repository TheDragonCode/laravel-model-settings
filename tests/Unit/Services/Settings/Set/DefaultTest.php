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

    app(SettingsService::class, ['model' => $user])->set('foo', 333);

    assertDatabaseHas(Settings::class, ['item_id' => $user->getKey(), 'key' => 'foo', 'payload' => 333]);
    assertDatabaseCount(Settings::class, 2);
});
