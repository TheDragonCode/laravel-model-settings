<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;

test('model purge removes only that owners overrides', function (): void {
    [$first, $second] = UserFactory::new()->count(2)->create();

    (new User)->defaultSettings()->setMany([
        'default' => 'value',
        'shared'  => 'default',
    ]);

    $first->settings()->setMany([
        'first'  => 'value',
        'shared' => 'first',
    ]);

    $second->settings()->setMany([
        'second' => 'value',
        'shared' => 'second',
    ]);

    $first->load('modelSettings');
    $first->settings()->purge();

    expect($first->relationLoaded('modelSettings'))->toBeFalse();
    expect($first->settings()->all()->all())->toBe([
        'default' => 'value',
        'shared'  => 'default',
    ]);
    expect($second->settings()->all()->sortKeys()->all())->toBe([
        'default' => 'value',
        'second'  => 'value',
        'shared'  => 'second',
    ]);

    assertDatabaseCount(Settings::class, 4);
});

test('default purge removes defaults without deleting model overrides', function (): void {
    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->setMany([
        'default' => 'value',
        'shared'  => 'default',
    ]);

    $user->settings()->setMany([
        'model'  => 'value',
        'shared' => 'override',
    ]);

    (new User)->defaultSettings()->purge();

    expect((new User)->defaultSettings()->all())->toBeEmpty();
    expect($user->settings()->all()->all())->toBe([
        'model'  => 'value',
        'shared' => 'override',
    ]);

    assertDatabaseCount(Settings::class, 2);
});
