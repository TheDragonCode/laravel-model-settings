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

    settings()->set('foo', 'manual');

    expect(settings()->get('foo'))->toBe(
        'manual'
    );
})->with('settings');

it('array', function (?array $settings) {
    $model1 = User::orderBy('id')->skip(0)->firstOrFail();
    $model2 = User::orderBy('id')->skip(1)->firstOrFail();

    expect($model1->id)->not->toBe($model2->id);

    createSettings($model1, $settings);
    createSettings($model2, $settings);

    expect($model1->settings()->get('foo'))->toBe($settings ? 'q1' : 'Foo Value');
    expect($model2->settings()->get('foo'))->toBe($settings ? 'q1' : 'Foo Value');

    $model1->settings()->set('foo', 'manual');

    expect($model1->settings()->get('foo'))->toBe('manual');
    expect($model2->settings()->get('foo'))->toBe($settings ? 'q1' : 'Foo Value');
})->with('settings');

it('cast', function (?array $settings) {
    config()->set('model-settings.repositories.database.cast', SettingsData::class);

    $model1 = User::orderBy('id')->skip(0)->firstOrFail();
    $model2 = User::orderBy('id')->skip(1)->firstOrFail();

    expect($model1->id)->not->toBe($model2->id);

    createSettings($model1, $settings);
    createSettings($model2, $settings);

    expect($model1->settings()->all()?->foo)->toBe($settings ? 'q1' : 'Foo Value');
    expect($model2->settings()->all()?->foo)->toBe($settings ? 'q1' : 'Foo Value');

    $model1->settings()->set('foo', 'manual');

    expect($model1->settings()->all()?->foo)->toBe('manual');
    expect($model2->settings()->all()?->foo)->toBe($settings ? 'q1' : 'Foo Value');
})->with('settings');
