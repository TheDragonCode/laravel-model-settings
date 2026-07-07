<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use DragonCode\LaravelModelSettings\Services\SettingsService;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseEmpty;

test('success', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    assertDatabaseEmpty(Settings::class);

    (new \Workbench\App\Models\User)->defaultSettings()->set('foo', 111);

    $user1->settings()->set('foo', 333);
    $user2->settings()->set('foo', 444);

    $result1 = app(SettingsService::class, ['model' => $user1])->get('foo');
    $result2 = app(SettingsService::class, ['model' => $user2])->get('foo');

    expect($result1)->toBe(333);
    expect($result2)->toBe(444);
});
