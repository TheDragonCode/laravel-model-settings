<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Laravel;
use Workbench\App\Data\SettingsData;
use Workbench\App\Models\User;

it('application', function (?array $settings) {
    createSettings(new Laravel(), $settings);

    expect(settings()->has('foo'))->toBeTrue();
})->with('settings');

it('array', function (?array $settings) {
    $model = User::orderBy('id')->skip(0)->firstOrFail();

    createSettings($model, $settings);

    expect($model->settings()->has('foo'))->toBeTrue();
    expect($model->settings()->has('bar.baz'))->toBeTrue();
    expect($model->settings()->has('custom'))->toBeTrue();
})->with('settings');

it('cast', function (?array $settings) {
    config()->set('model-settings.repositories.database.cast', SettingsData::class);

    $model = User::orderBy('id')->skip(0)->firstOrFail();

    createSettings($model, $settings);

    expect($model->settings()->has('foo'))->toBeTrue();
    expect($model->settings()->has('bar.baz'))->toBeTrue();

    expect($model->settings()->has('custom'))->toBeFalse();
})->with('settings');
