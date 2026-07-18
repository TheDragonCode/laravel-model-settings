<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\SomeInteger;
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

test('bulk mutations reject reserved owner identifiers before SQL', function (string $operation): void {
    DB::table('some_integers')->insert(['id' => 0]);

    $owner    = SomeInteger::query()->findOrFail(0);
    $settings = $owner->settings();

    (new SomeInteger)->defaultSettings()->set('foo', 111);

    $recorder = new QueryRecorder;
    $recorder->start();

    $mutation = static function () use ($operation, $settings): void {
        if ($operation === 'setMany') {
            $settings->setMany(['foo' => 222]);

            return;
        }

        if ($operation === 'forgetMany') {
            $settings->forgetMany(['foo']);

            return;
        }

        $settings->purge();
    };

    expect($mutation)->toThrow(
        InvalidSettingsOwnerException::class,
        InvalidSettingsOwnerException::reservedIdentifier($owner)->getMessage()
    );

    expect($recorder->calls())->toBe(0);
    expect((new SomeInteger)->defaultSettings()->get('foo'))->toBe(111);
})->with(['setMany', 'forgetMany', 'purge']);
