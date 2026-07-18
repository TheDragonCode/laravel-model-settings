<?php

declare(strict_types=1);

use DragonCode\LaravelModelSettings\Models\Settings;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    $cast    = new class implements CastsAttributes {
        public function get(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            return Json::decode($value);
        }

        public function set(Model $model, string $key, mixed $value, array $attributes): mixed
        {
            DB::table(config()->string('model_settings.table'))
                ->where('key', 'keep')
                ->update(['payload' => Json::encode('mutated')]);

            throw new RuntimeException('Serialization failed for ' . $value);
        }
    };

    config()->set('model_settings.casts.' . User::class, $cast::class);

    Log::spy();

    $recorder = new QueryRecorder;
    $recorder->start();

    expect(fn () => $user->settings()->setMany([
        'stored'   => $payload,
        'nullable' => null,
    ]))->toThrow(RuntimeException::class);

    expect($recorder->calls())->toBe(1);
    expect($user->relationLoaded('modelSettings'))->toBeTrue();

    assertDatabaseHas(Settings::class, ['key' => 'keep']);
    assertDatabaseHas(Settings::class, ['key' => 'delete']);
    assertDatabaseMissing(Settings::class, ['key' => 'stored']);
    assertDatabaseMissing(Settings::class, ['key' => 'nullable']);

    expect(Json::decode(
        DB::table(config()->string('model_settings.table'))->where('key', 'keep')->value('payload')
    ))->toBe('original');

    Log::shouldHaveReceived('error')
        ->withArgs(static function (string $message, array $context) use ($payload): bool {
            $logged = json_encode([$message, $context], JSON_THROW_ON_ERROR);

            return ! str_contains($logged, $payload);
        })
        ->once();
});

test('setMany preserves existing rows when the upsert fails', function (): void {
    $user = UserFactory::new()->create();

    $user->settings()->setMany([
        'keep'   => 'original',
        'delete' => 'original',
    ]);
    $user->load('modelSettings');

    $queries = 0;

    DB::connection()->beforeExecuting(static function () use (&$queries): void {
        if (++$queries === 1) {
            throw new RuntimeException('Forced persistence failure.');
        }
    });

    expect(fn () => $user->settings()->setMany([
        'stored'   => 'new',
        'nullable' => null,
    ]))->toThrow(RuntimeException::class, 'Forced persistence failure.');

    expect($user->relationLoaded('modelSettings'))->toBeTrue();

    assertDatabaseHas(Settings::class, ['key' => 'keep']);
    assertDatabaseHas(Settings::class, ['key' => 'delete']);
    assertDatabaseMissing(Settings::class, ['key' => 'stored']);
    assertDatabaseMissing(Settings::class, ['key' => 'nullable']);
});
