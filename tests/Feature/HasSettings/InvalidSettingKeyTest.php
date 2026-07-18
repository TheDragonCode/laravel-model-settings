<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

test('keyed APIs reject invalid normalized keys before SQL', function (
    string $operation,
    int|string|UnitEnum $key,
): void {
    $settings = UserFactory::new()->create()->settings();

    $values = static function () use ($key): iterable {
        yield $key => 'value';
    };

    $keys = static function () use ($key): iterable {
        yield $key;
    };

    $call = match ($operation) {
        'get'        => fn () => $settings->get($key),
        'has'        => fn () => $settings->has($key),
        'set'        => fn () => $settings->set($key, 'value'),
        'setMany'    => fn () => $settings->setMany($values()),
        'forget'     => fn () => $settings->forget($key),
        'forgetMany' => fn () => $settings->forgetMany($keys()),
    };

    $recorder = new QueryRecorder;
    $recorder->start();

    expect($call)->toThrow(InvalidSettingKey::class, InvalidSettingKey::blank()->getMessage())
        ->and($recorder->calls())->toBe(0);
})->with(['get', 'has', 'set', 'setMany', 'forget', 'forgetMany'])
    ->with('invalid setting keys');

test('bulk invalid-key exceptions contain no rejected key or payload', function (string $operation): void {
    $settings = UserFactory::new()->create()->settings();
    $key      = "\t\r\n";
    $payload  = 'private-setting-payload';

    $call = $operation === 'setMany'
        ? fn () => $settings->setMany((static function () use ($key, $payload): iterable {
            yield $key => $payload;
        })())
        : fn () => $settings->forgetMany((static function () use ($key): iterable {
            yield $key;
        })());

    try {
        $call();
    } catch (InvalidSettingKey $exception) {
        expect($exception->getMessage())
            ->toBe(InvalidSettingKey::blank()->getMessage())
            ->not->toContain($key, $payload);

        return;
    }

    throw new RuntimeException('Expected InvalidSettingKey was not thrown.');
})->with(['setMany', 'forgetMany']);
