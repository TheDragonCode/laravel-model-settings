<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException;
use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\SomeInteger;
use Workbench\App\Models\SomeString;
use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;

test('bulk mutations reject unsaved owners before SQL or iterable consumption', function (string $operation): void {
    $owner    = new User;
    $settings = $owner->settings();
    $consumed = false;

    $input = (static function () use (&$consumed): iterable {
        $consumed = true;

        yield 'foo' => 111;
    })();

    $recorder = new QueryRecorder;
    $recorder->start();

    $mutation = static function () use ($operation, $settings, $input): void {
        if ($operation === 'setMany') {
            $settings->setMany($input);

            return;
        }

        if ($operation === 'forgetMany') {
            $settings->forgetMany($input);

            return;
        }

        $settings->purge();
    };

    expect($mutation)
        ->toThrow(InvalidSettingsOwnerException::class, InvalidSettingsOwnerException::unsaved($owner)->getMessage());

    expect($recorder->calls())->toBe(0);
    expect($consumed)->toBeFalse();
})->with(['setMany', 'forgetMany', 'purge']);

test('bulk mutations isolate zero-valued owner overrides from defaults', function (
    string $modelClass,
    string $table,
    string $keyName,
    int|string $identifier,
): void {
    DB::table($table)->insert([$keyName => $identifier]);

    $owner    = $modelClass::query()->findOrFail($identifier);
    $defaults = (new $modelClass)->defaultSettings();
    $settings = $owner->settings();

    $defaults->setMany([
        'foo' => 111,
        'bar' => 222,
    ]);

    $settings->setMany([
        'foo' => 333,
        'baz' => 444,
    ]);

    expect($settings->all()->sortKeys()->all())->toBe([
        'bar' => 222,
        'baz' => 444,
        'foo' => 333,
    ])->and(Settings::query()
        ->where('item_type', $owner->getMorphClass())
        ->where('item_id', '0')
        ->where('is_default', true)
        ->count())->toBe(2)
        ->and(Settings::query()
            ->where('item_type', $owner->getMorphClass())
            ->where('item_id', '0')
            ->where('is_default', false)
            ->count())->toBe(2);

    $settings->forgetMany(['foo', 'bar']);

    expect($settings->all()->sortKeys()->all())->toBe([
        'bar' => 222,
        'baz' => 444,
        'foo' => 111,
    ]);

    $settings->setMany([
        'foo' => 555,
        'bar' => null,
    ]);

    expect($settings->all()->sortKeys()->all())->toBe([
        'bar' => 222,
        'baz' => 444,
        'foo' => 555,
    ]);

    $settings->purge();

    expect($settings->all()->sortKeys()->all())->toBe([
        'bar' => 222,
        'foo' => 111,
    ])->and(Settings::query()
        ->where('item_type', $owner->getMorphClass())
        ->where('item_id', '0')
        ->where('is_default', false)
        ->exists())->toBeFalse();
})->with([
    'integer zero' => [SomeInteger::class, 'some_integers', 'id', 0],
    'string zero'  => [SomeString::class, 'some_strings', 'key', '0'],
]);
