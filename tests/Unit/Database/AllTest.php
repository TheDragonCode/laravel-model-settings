<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Laravel;
use Workbench\App\Data\SettingsData;
use Workbench\App\Models\User;

it('application', function (?array $settings) {
    createSettings(new Laravel(), $settings);

    expect(settings()->all())
        ->toBeArray()
        ->not->toBeEmpty()
        ->toMatchSnapshot();
})->with('settings');

it('array', function (?array $settings) {
    $model = User::firstOrFail();

    createSettings($model, $settings);

    expect($model->settings()->all())
        ->toBeArray()
        ->not->toBeEmpty()
        ->toMatchSnapshot();
})->with('settings');

it('cast', function (?array $settings) {
    config()->set('model-settings.repositories.database.cast', SettingsData::class);

    $model = User::firstOrFail();

    createSettings($model, $settings);

    expect($model->settings()->all())
        ->toBeArray()
        ->not->toBeEmpty()
        ->toMatchSnapshot();
})->with('settings');
