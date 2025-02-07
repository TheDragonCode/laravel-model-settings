<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Laravel;
use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Data\SettingsData;
use Workbench\App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('application', function (?array $settings) {
    createSettings(new Laravel(), $settings);

    blank($settings)
        ? assertDatabaseCount(Settings::class, 0)
        : assertDatabaseCount(Settings::class, 1);

    expect(settings()->all())->not->toBeEmpty();

    settings()->clear();

    assertDatabaseCount(Settings::class, 0);

    expect(settings()->all())->not->toBeEmpty();
})->with('settings');

it('array', function (?array $settings) {
    $model1 = User::orderBy('id')->skip(0)->firstOrFail();
    $model2 = User::orderBy('id')->skip(1)->firstOrFail();

    expect($model1->id)->not->toBe($model2->id);

    createSettings($model1, $settings);
    createSettings($model2, $settings);

    blank($settings)
        ? assertDatabaseCount(Settings::class, 0)
        : assertDatabaseCount(Settings::class, 2);

    expect($model1->settings()->all())->not->toBeEmpty();
    expect($model2->settings()->all())->not->toBeEmpty();

    $model1->settings()->clear();

    assertDatabaseCount(Settings::class, 1);

    assertDatabaseMissing(Settings::class, ['item_id' => $model1->getKey()]);
    assertDatabaseHas(Settings::class, ['item_id' => $model2->getKey()]);

    expect($model1->settings()->all())->not->toBeEmpty();
    expect($model2->settings()->all())->not->toBeEmpty();
})->with('settings');

it('cast', function (?array $settings) {
    config()->set('model-settings.repositories.database.cast', SettingsData::class);

    $model1 = User::orderBy('id')->skip(0)->firstOrFail();
    $model2 = User::orderBy('id')->skip(1)->firstOrFail();

    expect($model1->id)->not->toBe($model2->id);

    createSettings($model1, $settings);
    createSettings($model2, $settings);

    blank($settings)
        ? assertDatabaseCount(Settings::class, 0)
        : assertDatabaseCount(Settings::class, 2);

    expect($model1->settings()->all()?->toArray())->not->toBeEmpty();
    expect($model2->settings()->all()?->toArray())->not->toBeEmpty();

    $model1->settings()->clear();

    assertDatabaseCount(Settings::class, 1);

    assertDatabaseMissing(Settings::class, ['item_id' => $model1->getKey()]);
    assertDatabaseHas(Settings::class, ['item_id' => $model2->getKey()]);

    expect($model1->settings()->all()?->toArray())->not->toBeEmpty();
    expect($model2->settings()->all()?->toArray())->not->toBeEmpty();
})->with('settings');
