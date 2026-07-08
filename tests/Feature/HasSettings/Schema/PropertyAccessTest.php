<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\SchemaUser;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

test('reading a property resolves through the value layers', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    // Nothing stored: schema default.
    expect($user->settings()->localization_code)->toBe('ru');
    expect($user->settings()->ttb_command_index)->toBeNull();

    $user->settings()->set('localization_code', 'fr');

    expect($user->settings()->localization_code)->toBe('fr');
});

test('writing a property stores the setting', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    assertDatabaseEmpty(Settings::class);

    $user->settings()->localization_code = 'fr';
    $user->settings()->order_card_payment = true;

    assertDatabaseCount(Settings::class, 2);

    expect($user->settings()->get('localization_code'))->toBe('fr');
    expect($user->settings()->get('order_card_payment'))->toBeTrue();
});

test('assigning a blank value removes the setting and falls back to the default', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    $user->settings()->localization_code = 'fr';
    assertDatabaseCount(Settings::class, 1);

    $user->settings()->localization_code = null;

    assertDatabaseEmpty(Settings::class);
    expect($user->settings()->localization_code)->toBe('ru'); // back to schema default
});

test('isset and unset work through property access', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    $user->settings()->localization_code = 'fr';

    expect(isset($user->settings()->localization_code))->toBeTrue();

    unset($user->settings()->localization_code);

    assertDatabaseEmpty(Settings::class);
    expect($user->settings()->get('localization_code'))->toBe('ru');
});
