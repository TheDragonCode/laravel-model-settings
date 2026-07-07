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

    $user1->settings()->set('foo', 111);
    $user1->settings()->set('bar', 222);

    $user2->settings()->set('foo', 333);
    $user2->settings()->set('bar', 444);

    $result1 = app(SettingsService::class, ['model' => $user1])->all()->toArray();
    $result2 = app(SettingsService::class, ['model' => $user2])->all()->toArray();

    ksort($result1);
    ksort($result2);

    expect($result1)->toBe(['bar' => 222, 'foo' => 111]);
    expect($result2)->toBe(['bar' => 444, 'foo' => 333]);
});
