<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey;
use Illuminate\Support\Facades\Log;
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

test('bulk invalid-key logs contain no rejected key or payload', function (string $operation): void {
    $settings = UserFactory::new()->create()->settings();
    $key      = "\t\r\n";
    $payload  = 'private-setting-payload';

    Log::spy();

    $call = $operation === 'setMany'
        ? fn () => $settings->setMany((static function () use ($key, $payload): iterable {
            yield $key => $payload;
        })())
        : fn () => $settings->forgetMany((static function () use ($key): iterable {
            yield $key;
        })());

    expect($call)->toThrow(InvalidSettingKey::class, InvalidSettingKey::blank()->getMessage());

    Log::shouldHaveReceived('debug')
        ->withArgs(static fn (string $message, array $context): bool => array_keys($context) === ['operation', 'owner', 'scope'])
        ->once();

    Log::shouldHaveReceived('error')
        ->withArgs(static fn (string $message, array $context): bool => array_keys($context) === ['operation', 'owner', 'scope', 'exception'])
        ->once();
})->with(['setMany', 'forgetMany']);
