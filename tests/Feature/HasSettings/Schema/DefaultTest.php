<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\SchemaUser;
use Workbench\App\Models\User;
use Workbench\App\Settings\UserSettings;

use function Pest\Laravel\assertDatabaseEmpty;

test('get falls back to the schema default when nothing is stored', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    assertDatabaseEmpty(Settings::class);

    expect($user->settings()->get('localization_code'))->toBe('ru');
    expect($user->settings()->get('default_agreement'))->toBe(3);
    expect($user->settings()->get('order_card_payment'))->toBeFalse();
    expect($user->settings()->get('ttb_command_index'))->toBeNull();

    // Keys absent from the schema still resolve to null.
    expect($user->settings()->get('unknown_key'))->toBeNull();
});

test('the database default overrides the schema default', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    (new SchemaUser)->defaultSettings()->set('localization_code', 'en');

    expect($user->settings()->get('localization_code'))->toBe('en');
});

test('the model value overrides both the database and schema defaults', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    (new SchemaUser)->defaultSettings()->set('localization_code', 'en');
    $user->settings()->set('localization_code', 'fr');

    expect($user->settings()->get('localization_code'))->toBe('fr');
});

test('all merges the schema defaults underneath the stored values', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    (new SchemaUser)->defaultSettings()->set('default_agreement', 5);
    $user->settings()->set('localization_code', 'fr');

    $result = $user->settings()->all()->toArray();

    expect($result)
        ->toHaveKey('ttb_command_index', null)   // schema default
        ->toHaveKey('po_box', '')                // schema default
        ->toHaveKey('default_agreement', 5)      // database default wins over schema
        ->toHaveKey('localization_code', 'fr');  // model value wins over schema
});

test('schema returns a typed object with resolved values', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    (new SchemaUser)->defaultSettings()->set('default_agreement', 5);
    $user->settings()->set('localization_code', 'fr');
    $user->settings()->set('order_card_payment', true);

    // No argument: hydrates the model's declared schema (generic object).
    $settings = $user->settings()->schema();

    expect($settings)->toBeInstanceOf(UserSettings::class);

    // Explicit class-string: IDE and static analysis resolve the concrete type.
    $typed = $user->settings()->schema(UserSettings::class);

    expect($typed)->toBeInstanceOf(UserSettings::class);
    expect($typed->localization_code)->toBe('fr');
    expect($settings->localization_code)->toBe('fr');    // model value
    expect($settings->default_agreement)->toBe(5);       // database default
    expect($settings->order_card_payment)->toBeTrue();   // model value
    expect($settings->po_box)->toBe('');                 // schema default
    expect($settings->ttb_command_index)->toBeNull();    // schema default
});

test('a model without a schema keeps returning null and no schema object', function () {
    $user = User::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    expect($user->settings()->get('localization_code'))->toBeNull();
    expect($user->settings()->schema())->toBeNull();
});
