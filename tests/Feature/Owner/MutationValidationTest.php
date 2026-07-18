<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException;
use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\SomeInteger;
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

test('zero-valued owners can override defaults lazily and eagerly', function (
    string $modelClass,
    string $table,
    string $keyName,
    int|string $identifier,
): void {
    DB::table($table)->insert([$keyName => $identifier]);

    $owner = $modelClass::query()->findOrFail($identifier);

    expect($owner->getKey())->toBe($identifier);

    $defaults = (new $modelClass)->defaultSettings();

    $defaults->set('foo', 111);
    $defaults->set('bar', 222);

    $owner->settings()->set('foo', 333);

    $rows = Settings::query()
        ->where('item_type', $owner->getMorphClass())
        ->where('item_id', '0')
        ->where('key', 'foo')
        ->orderBy('is_default')
        ->get();

    expect($rows)->toHaveCount(2)
        ->and($rows[0]->getAttribute('is_default'))->toBeFalse()
        ->and($rows[0]->getAttribute('payload'))->toBe(333)
        ->and($rows[1]->getAttribute('is_default'))->toBeTrue()
        ->and($rows[1]->getAttribute('payload'))->toBe(111)
        ->and($defaults->get('foo'))->toBe(111)
        ->and($owner->settings()->get('foo'))->toBe(333);

    $owner->settings()->set('foo', null);

    expect($owner->settings()->get('foo'))->toBe(111);

    $owner->settings()->set('foo', 444);

    $lazy = $owner->settings()->all()->sortKeys()->all();

    $eagerOwner = $modelClass::query()
        ->with('modelSettings')
        ->findOrFail($identifier);

    expect($lazy)->toBe(['bar' => 222, 'foo' => 444])
        ->and($eagerOwner->settings()->all()->sortKeys()->all())->toBe($lazy)
        ->and($eagerOwner->modelSettings)->toHaveCount(2)
        ->and($eagerOwner->modelSettings->pluck('is_default')->sort()->values()->all())->toBe([false, true]);

    $owner->settings()->forget('foo');

    expect($owner->settings()->get('foo'))->toBe(111)
        ->and(Settings::query()
            ->where('item_type', $owner->getMorphClass())
            ->where('item_id', '0')
            ->where('is_default', false)
            ->where('key', 'foo')
            ->exists())->toBeFalse();
})->with([
    'integer zero' => [SomeInteger::class, 'some_integers', 'id', 0],
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
