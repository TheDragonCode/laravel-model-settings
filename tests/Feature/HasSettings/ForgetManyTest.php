<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;

test('forgetMany deletes only normalized keys from the current scope', function (): void {
    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->setMany([
        'keep' => 'default',
        'bar'  => 'default-bar',
    ]);

    $values = static function (): iterable {
        yield IntBackedEnum::Foo => 'integer enum';
        yield StringBackedEnum::Bar => 'string enum';
        yield UnitEnum::Baz => 'pure enum';
        yield 'keep' => 'override';
        yield 'remain' => 'value';
    };

    $user->settings()->setMany($values());
    $user->load('modelSettings');

    $keys = static function (): iterable {
        yield IntBackedEnum::Foo;
        yield 11;
        yield StringBackedEnum::Bar;
        yield UnitEnum::Baz;
        yield 'keep';
    };

    $user->settings()->forgetMany($keys());

    expect($user->relationLoaded('modelSettings'))->toBeFalse();
    expect($user->settings()->all()->sortKeys()->all())->toBe([
        'bar'    => 'default-bar',
        'keep'   => 'default',
        'remain' => 'value',
    ]);

    assertDatabaseCount(Settings::class, 3);
});

test('forgetMany accepts an empty iterable without issuing a mutation', function (): void {
    $user = UserFactory::new()->create();

    $user->settings()->set('keep', 'value');
    $user->load('modelSettings');

    $user->settings()->forgetMany([]);

    expect($user->relationLoaded('modelSettings'))->toBeFalse();
    expect($user->settings()->get('keep'))->toBe('value');
});
