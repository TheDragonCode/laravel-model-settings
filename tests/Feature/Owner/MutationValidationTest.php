<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\SomeId;
use Workbench\App\Models\SomeString;
use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\SomeStringFactory;

test('set rejects unsaved owners before a query', function (bool $hasKey, mixed $value): void {
    $owner = new User;

    if ($hasKey) {
        $owner->setAttribute($owner->getKeyName(), 123);
    }

    $recorder = new QueryRecorder;
    $recorder->start();

    expect(fn () => $owner->settings()->set('foo', $value))
        ->toThrow(InvalidSettingsOwnerException::class, InvalidSettingsOwnerException::unsaved($owner)->getMessage());

    expect($recorder->calls())->toBe(0);
})->with([
    'missing key'     => false,
    'preassigned key' => true,
])->with([
    'filled value' => 111,
    'blank value'  => null,
]);

test('forget rejects unsaved owners before a query', function (bool $hasKey): void {
    $owner = new User;

    if ($hasKey) {
        $owner->setAttribute($owner->getKeyName(), 123);
    }

    $recorder = new QueryRecorder;
    $recorder->start();

    expect(fn () => $owner->settings()->forget('foo'))
        ->toThrow(InvalidSettingsOwnerException::class, InvalidSettingsOwnerException::unsaved($owner)->getMessage());

    expect($recorder->calls())->toBe(0);
})->with([
    'missing key'     => false,
    'preassigned key' => true,
]);

test('set rejects reserved owner identifiers before a query', function (
    string $modelClass,
    string $table,
    string $keyName,
    int|string $identifier,
    mixed $value,
): void {
    DB::table($table)->insert([$keyName => $identifier]);

    $owner = $modelClass::query()->findOrFail($identifier);

    (new $modelClass)->defaultSettings()->set('foo', 111);

    $recorder = new QueryRecorder;
    $recorder->start();

    expect(fn () => $owner->settings()->set('foo', $value))
        ->toThrow(
            InvalidSettingsOwnerException::class,
            InvalidSettingsOwnerException::reservedIdentifier($owner)->getMessage()
        );

    expect($recorder->calls())->toBe(0);
    expect((new $modelClass)->defaultSettings()->get('foo'))->toBe(111);
})->with([
    'integer zero' => [SomeId::class, 'some_ids', 'id', 0],
    'string zero'  => [SomeString::class, 'some_strings', 'key', '0'],
])->with([
    'filled value' => 222,
    'blank value'  => null,
]);

test('forget rejects reserved owner identifiers before a query', function (
    string $modelClass,
    string $table,
    string $keyName,
    int|string $identifier,
): void {
    DB::table($table)->insert([$keyName => $identifier]);

    $owner = $modelClass::query()->findOrFail($identifier);

    (new $modelClass)->defaultSettings()->set('foo', 111);

    $recorder = new QueryRecorder;
    $recorder->start();

    expect(fn () => $owner->settings()->forget('foo'))
        ->toThrow(
            InvalidSettingsOwnerException::class,
            InvalidSettingsOwnerException::reservedIdentifier($owner)->getMessage()
        );

    expect($recorder->calls())->toBe(0);
    expect((new $modelClass)->defaultSettings()->get('foo'))->toBe(111);
})->with([
    'integer zero' => [SomeId::class, 'some_ids', 'id', 0],
    'string zero'  => [SomeString::class, 'some_strings', 'key', '0'],
]);

test('reserved owner identifiers can read defaults lazily and eagerly', function (
    string $modelClass,
    string $table,
    string $keyName,
    int|string $identifier,
): void {
    DB::table($table)->insert([$keyName => $identifier]);

    (new $modelClass)->defaultSettings()->set('foo', 111);

    $owner = $modelClass::query()->findOrFail($identifier);

    expect($owner->settings()->get('foo'))->toBe(111);
    expect($owner->settings()->all()->all())->toBe(['foo' => 111]);
    expect($owner->relationLoaded('modelSettings'))->toBeFalse();

    $eagerOwner = $modelClass::query()
        ->with('modelSettings')
        ->findOrFail($identifier);

    expect($eagerOwner->settings()->get('foo'))->toBe(111);
    expect($eagerOwner->settings()->all()->all())->toBe(['foo' => 111]);
    expect($eagerOwner->modelSettings)->toHaveCount(1);
})->with([
    'integer zero' => [SomeId::class, 'some_ids', 'id', 0],
    'string zero'  => [SomeString::class, 'some_strings', 'key', '0'],
]);

test('non-reserved falsey string identifiers can mutate settings', function (): void {
    DB::table('some_strings')->insert(['key' => '00']);

    $owner = SomeString::query()->findOrFail('00');

    $owner->settings()->set('foo', 111);

    expect($owner->settings()->get('foo'))->toBe(111);
});

test('settings service resolves the owner identifier when an operation runs', function (): void {
    $owner    = SomeStringFactory::new()->make();
    $settings = $owner->settings();

    $owner->save();
    $settings->set('foo', 111);

    expect($settings->get('foo'))->toBe(111);
});
