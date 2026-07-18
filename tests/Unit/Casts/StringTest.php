<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

test('missing cast class throws a typed exception without exposing the payload', function (): void {
    $cast    = 'string';
    $key     = 'private-setting-key';
    $payload = 'private-setting-payload';

    config()->set('model_settings.casts.' . User::class, $cast);

    $user = UserFactory::new()->create();

    try {
        $user->settings()->set($key, $payload);
    } catch (InvalidPayloadCast $exception) {
        expect($exception->getMessage())
            ->toContain(User::class, $key, $cast)
            ->not->toContain($payload);

        return;
    }

    throw new RuntimeException('Expected InvalidPayloadCast was not thrown.');
});

test('class without a supported cast contract throws a typed exception', function (): void {
    config()->set('model_settings.casts.' . User::class, [
        'foo' => stdClass::class,
    ]);

    $user = UserFactory::new()->create();

    expect(fn () => $user->settings()->set('foo', 'value'))
        ->toThrow(InvalidPayloadCast::class, 'must implement CastsAttributes or extend Data');
});

test('non-string cast configuration throws a typed exception', function (): void {
    config()->set('model_settings.casts.' . User::class, [
        'foo' => null,
    ]);

    $user = UserFactory::new()->create();

    expect(fn () => $user->settings()->set('foo', 'value'))
        ->toThrow(InvalidPayloadCast::class, 'must be a class-string');
});

test('unresolvable cast dependency throws a typed exception', function (): void {
    $cast = new class ('dependency') implements CastsAttributes {
        public function __construct(string $dependency) {}

        public function get(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            return $value;
        }

        public function set(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            return $value;
        }
    };

    config()->set('model_settings.casts.' . User::class, [
        'foo' => $cast::class,
    ]);

    $user = UserFactory::new()->create();

    expect(fn () => $user->settings()->set('foo', 'value'))
        ->toThrow(InvalidPayloadCast::class, 'could not be resolved by the container');
});
