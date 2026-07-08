<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Models\RequiredSchemaUser;
use Workbench\App\Models\SchemaUser;
use Workbench\App\Models\User;
use Workbench\App\Settings\UserSettings;

use function Pest\Laravel\assertDatabaseEmpty;

test('an explicit schema class hydrates with its own defaults on a model without a declared schema', function () {
    $user = User::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    assertDatabaseEmpty(Settings::class);

    $settings = $user->settings()->schema(UserSettings::class);

    expect($settings)->toBeInstanceOf(UserSettings::class);
    expect($settings->localization_code)->toBe('ru');
    expect($settings->po_box)->toBe('');
    expect($settings->ttb_command_index)->toBeNull();
});

test('an explicit schema class still receives stored values', function () {
    $user = User::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    $user->settings()->set('localization_code', 'fr');

    $settings = $user->settings()->schema(UserSettings::class);

    expect($settings->localization_code)->toBe('fr');
    expect($settings->default_agreement)->toBe(3);
});

test('a schema with a required constructor parameter does not break reads', function () {
    $user = RequiredSchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    // No stored value and no constructor default: resolves to null instead of crashing.
    expect($user->settings()->get('currency'))->toBeNull();
    expect($user->settings()->get('timezone'))->toBe('UTC');
    expect($user->settings()->all()->toArray())->toBe(['timezone' => 'UTC']);

    $user->settings()->set('currency', 'EUR');

    $settings = $user->settings()->schema();

    expect($settings->currency)->toBe('EUR');
    expect($settings->timezone)->toBe('UTC');
});

test('a stored blank value falls back to the schema default during hydration', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    // Seed a blank payload directly, bypassing set()'s blank guard.
    Settings::query()->create([
        'item_type' => SchemaUser::class,
        'item_id'   => $user->getKey(),
        'key'       => 'localization_code',
        'payload'   => '',
    ]);

    expect($user->settings()->schema()->localization_code)->toBe('ru');
});

test('property access to internal service property names fails loudly', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    $service = $user->settings();

    expect(fn () => $service->model)->toThrow(LogicException::class);
    expect(fn () => $service->repository = 'oops')->toThrow(LogicException::class);

    assertDatabaseEmpty(Settings::class);

    // The colliding names remain reachable through the method API.
    $service->set('model', 'value');
    expect($service->get('model'))->toBe('value');
});

test('isset agrees with property reads for blank schema defaults', function () {
    $user = SchemaUser::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    // po_box defaults to '' — resolved and not null, so isset()/?? see it.
    expect($user->settings()->po_box)->toBe('');
    expect(isset($user->settings()->po_box))->toBeTrue();
    expect($user->settings()->po_box ?? 'fallback')->toBe('');

    // ttb_command_index defaults to null — consistently reported as not set.
    expect($user->settings()->ttb_command_index)->toBeNull();
    expect(isset($user->settings()->ttb_command_index))->toBeFalse();
});

test('a model defining modelSettings without the trait does not crash on the schema layer', function () {
    $user = User::query()->create([
        'name'     => 'Foo',
        'email'    => 'foo@example.com',
        'password' => 'secret',
    ]);

    $service = app()->make(
        DragonCode\LaravelModelSettings\Services\SettingsService::class,
        ['model' => new class extends Illuminate\Database\Eloquent\Model {
            protected $table = 'users';

            public function modelSettings()
            {
                return $this->morphMany(config()->string('model-settings.model'), 'item');
            }
        }],
    );

    expect($service->get('missing_key'))->toBeNull();
});
