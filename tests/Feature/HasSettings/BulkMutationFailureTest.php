<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Exceptions\BulkMutationException;
use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\User;
use Workbench\App\Services\QueryRecorder;
use Workbench\Database\Factories\UserFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('mixed-value setMany rolls back database work when payload serialization fails', function (): void {
    $user = UserFactory::new()->create();

    $user->settings()->setMany([
        'keep'   => 'original',
        'delete' => 'original',
    ]);
    $user->load('modelSettings');

    $payload = 'private-failing-payload';
    $failure = new RuntimeException('Serialization failed for ' . $payload);
    $cast    = new class ($failure) implements CastsAttributes {
        public function __construct(
            private RuntimeException $failure,
        ) {}

        public function get(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            return Json::decode($value);
        }

        public function set(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            DB::table(config()->string('model_settings.table'))
                ->where('key', 'keep')
                ->update(['payload' => Json::encode('mutated')]);

            throw $this->failure;
        }
    };

    app()->instance($cast::class, $cast);
    config()->set('model_settings.casts.' . User::class, $cast::class);

    $recorder = new QueryRecorder;
    $recorder->start();

    $exception = null;

    try {
        $user->settings()->setMany([
            'stored'   => $payload,
            'nullable' => null,
        ]);
    } catch (BulkMutationException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())
        ->toBe('Model settings [setMany] failed for [Workbench\\App\\Models\\User] in [model] scope.')
        ->not->toContain($payload);
    expect($exception->getPrevious())->toBe($failure);

    expect($recorder->calls())->toBe(1);
    expect($user->relationLoaded('modelSettings'))->toBeTrue();

    assertDatabaseHas(Settings::class, ['key' => 'keep']);
    assertDatabaseHas(Settings::class, ['key' => 'delete']);
    assertDatabaseMissing(Settings::class, ['key' => 'stored']);
    assertDatabaseMissing(Settings::class, ['key' => 'nullable']);

    expect(Json::decode(
        DB::table(config()->string('model_settings.table'))->where('key', 'keep')->value('payload')
    ))->toBe('original');
});

test('setMany preserves existing rows when the upsert fails', function (): void {
    $user = UserFactory::new()->create();

    $user->settings()->setMany([
        'keep'   => 'original',
        'delete' => 'original',
    ]);
    $user->load('modelSettings');

    $failure = new RuntimeException('Forced persistence failure.');
    $queries = 0;

    DB::connection()->beforeExecuting(static function () use (&$queries, $failure): void {
        if (++$queries === 1) {
            throw $failure;
        }
    });

    $exception = null;

    try {
        $user->settings()->setMany([
            'stored'   => 'new',
            'nullable' => null,
        ]);
    } catch (BulkMutationException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getPrevious())->toBe($failure);

    expect($user->relationLoaded('modelSettings'))->toBeTrue();

    assertDatabaseHas(Settings::class, ['key' => 'keep']);
    assertDatabaseHas(Settings::class, ['key' => 'delete']);
    assertDatabaseMissing(Settings::class, ['key' => 'stored']);
    assertDatabaseMissing(Settings::class, ['key' => 'nullable']);
});

test('forgetMany and purge wrap persistence failures', function (string $operation): void {
    $user = UserFactory::new()->create();

    $user->settings()->setMany([
        'keep'   => 'original',
        'delete' => 'original',
    ]);
    $user->load('modelSettings');

    $failure = new RuntimeException('Forced persistence failure.');
    $failed  = false;

    DB::connection()->beforeExecuting(static function () use (&$failed, $failure): void {
        if (! $failed) {
            $failed = true;

            throw $failure;
        }
    });

    $exception = null;

    try {
        if ($operation === 'forgetMany') {
            $user->settings()->forgetMany(['delete']);
        } else {
            $user->settings()->purge();
        }
    } catch (BulkMutationException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain("[$operation]", User::class, '[model]');
    expect($exception->getPrevious())->toBe($failure);
    expect($user->relationLoaded('modelSettings'))->toBeTrue();

    assertDatabaseHas(Settings::class, ['key' => 'keep']);
    assertDatabaseHas(Settings::class, ['key' => 'delete']);
})->with(['forgetMany', 'purge']);

test('setMany and forgetMany wrap iterable failures', function (string $operation): void {
    $user = UserFactory::new()->create();
    $user->load('modelSettings');

    $failure = new RuntimeException('Forced iterable failure.');
    $input   = (static function () use ($failure): iterable {
        throw $failure;
        yield 'key' => 'value';
    })();

    $exception = null;

    try {
        if ($operation === 'setMany') {
            $user->settings()->setMany($input);
        } else {
            $user->settings()->forgetMany($input);
        }
    } catch (BulkMutationException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())->toContain("[$operation]", User::class, '[model]');
    expect($exception->getPrevious())->toBe($failure);
    expect($user->relationLoaded('modelSettings'))->toBeTrue();
})->with(['setMany', 'forgetMany']);

test('bulk mutation failures identify the default scope', function (): void {
    $owner   = new User;
    $failure = new RuntimeException('Forced iterable failure.');
    $input   = (static function () use ($failure): iterable {
        throw $failure;
        yield 'key' => 'value';
    })();

    $exception = null;

    try {
        $owner->defaultSettings()->setMany($input);
    } catch (BulkMutationException $caught) {
        $exception = $caught;
    }

    expect($exception)->not->toBeNull();
    expect($exception->getMessage())
        ->toBe('Model settings [setMany] failed for [Workbench\\App\\Models\\User] in [default] scope.');
    expect($exception->getPrevious())->toBe($failure);
});
