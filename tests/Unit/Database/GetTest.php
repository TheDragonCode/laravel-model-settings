<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Laravel;
use Workbench\App\Data\SettingsData;
use Workbench\App\Models\User;

it('application', function (?array $settings) {
    createSettings(new Laravel(), $settings);

    expect(settings()->get('foo'))->toBe(
        $settings ? 'q1' : 'Foo Value'
    );
})->with('settings');

it('array', function (?array $settings) {
    $model = User::orderBy('id')->skip(0)->firstOrFail();

    createSettings($model, $settings);

    expect($model->settings()->get('foo'))->toBe($settings ? 'q1' : 'Foo Value');
    expect($model->settings()->get('bar.baz'))->toBe($settings ? 'q2' : 'Baz Value');

    expect($model->settings()->get('custom'))->toBeString()->toBeIn(['q2', 'q4']);
})->with('settings');

it('cast', function (?array $settings) {
    config()->set('model-settings.repositories.database.cast', SettingsData::class);

    $model = User::orderBy('id')->skip(0)->firstOrFail();

    createSettings($model, $settings);

    expect($model->settings()->get('foo'))->toBe($settings ? 'q1' : 'Foo Value');
    expect($model->settings()->get('bar.baz'))->toBe($settings ? 'q2' : 'Baz Value');

    expect($model->settings()->get('custom'))->toBeNull();
})->with('settings');
