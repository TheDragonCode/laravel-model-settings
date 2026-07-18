<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Support\Facades\Log;
use Workbench\App\Casts\ContainerCast;
use Workbench\App\Data\SomeData;
use Workbench\App\Enums\IntBackedEnum;
use Workbench\App\Enums\StringBackedEnum;
use Workbench\App\Enums\UnitEnum;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseCount;

test('setMany stores exact defaults and overrides without implicit deletion', function (): void {
    $user = UserFactory::new()->create();

    (new User)->defaultSettings()->setMany([
        'fallback' => 'default',
        'shared'   => 'default',
    ]);

    $user->settings()->setMany([
        'shared'     => 'override',
        'model-only' => 'value',
        'fallback'   => 'override',
    ]);

    $user->settings()->setMany([
        'fallback'     => null,
        'model-only'   => [],
        'empty-string' => '',
        'whitespace'   => '   ',
        'zero'         => 0,
        'false'        => false,
    ]);

    expect((new User)->defaultSettings()->all()->all())->toBe([
        'fallback' => 'default',
        'shared'   => 'default',
    ]);

    expect($user->settings()->all()->sortKeys()->all())->toBe([
        'empty-string' => '',
        'fallback'     => null,
        'false'        => false,
        'model-only'   => [],
        'shared'       => 'override',
        'whitespace'   => '   ',
        'zero'         => 0,
    ]);

    assertDatabaseCount(Settings::class, 9);
});

test('setMany uses the last value for duplicate normalized keys', function (): void {
    $user = UserFactory::new()->create();

    $values = static function (): iterable {
        yield IntBackedEnum::Foo => 'enum';
        yield 11 => 'integer';
        yield '11' => 'string';
        yield StringBackedEnum::Bar => 'backed';
        yield UnitEnum::Baz => 'pure';
        yield '01' => 'numeric-string';
        yield 1 => 'integer-one';
    };

    $user->settings()->setMany($values());

    expect($user->settings()->get(IntBackedEnum::Foo))->toBe('string');
    expect($user->settings()->get(StringBackedEnum::Bar))->toBe('backed');
    expect($user->settings()->get(UnitEnum::Baz))->toBe('pure');
    expect($user->settings()->get('01'))->toBe('numeric-string');
    expect($user->settings()->get(1))->toBe('integer-one');

    assertDatabaseCount(Settings::class, 5);
});

test('setMany resolves the payload cast for every setting key', function (): void {
    config()->set('model_settings.casts.' . User::class, [
        'first'  => ContainerCast::class,
        'second' => SomeData::class,
    ]);

    $data = SomeData::from([
        'foo' => 'Foo',
        'bar' => 'Bar',
        'baz' => 'Baz',

        'item' => [
            'firstName' => 'John',
            'lastName'  => 'Doe',
        ],

        'collection' => [
            ['id' => 1, 'comment' => 'Comment'],
        ],
    ]);

    $user = UserFactory::new()->create();

    $user->settings()->setMany([
        'first'  => ['foo' => 'bar'],
        'second' => $data,
        'plain'  => ['baz' => 'qux'],
    ]);

    $settings = $user->settings()->all();

    expect($settings->get('first'))->toBe([
        'table' => config()->string('model_settings.table'),
        'value' => ['foo' => 'bar'],
    ])->and($settings->get('second'))
        ->toBeInstanceOf(SomeData::class)
        ->toArray()->toBe($data->toArray())
        ->and($settings->get('plain'))->toBe(['baz' => 'qux']);
});

test('setMany invalidates the loaded relation and never logs keys or payloads', function (): void {
    $user = UserFactory::new()->create();
    $user->load('modelSettings');

    $key     = 'private-setting-key';
    $payload = 'private-setting-payload';

    Log::spy();

    $user->settings()->setMany([$key => $payload]);

    expect($user->relationLoaded('modelSettings'))->toBeFalse();

    Log::shouldHaveReceived('debug')->times(3);
    Log::shouldHaveReceived('debug')
        ->withArgs(static function (string $message, array $context) use ($key, $payload): bool {
            $logged = json_encode([$message, $context], JSON_THROW_ON_ERROR);

            return ! str_contains($logged, $key) && ! str_contains($logged, $payload);
        })
        ->times(3);
});
