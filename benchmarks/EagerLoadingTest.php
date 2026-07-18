<?php

declare(strict_types=1);

use DragonCode\Benchmark\Benchmark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

uses(TestCase::class, WithWorkbench::class, RefreshDatabase::class);

test('eager relation replication benchmark', function (int $ownerCount, int $defaultCount): void {
    ini_set('memory_limit', '1G');

    UserFactory::new()->count($ownerCount)->create();

    $defaults = (new User)->defaultSettings();

    foreach (range(1, $defaultCount) as $index) {
        $defaults->set('setting-' . $index, $index);
    }

    Benchmark::make()
        ->warmup()
        ->iterations(10)
        ->disableProgressBar()
        ->afterEach(static fn (): int => gc_collect_cycles())
        ->compare(
            eager: static function (): int {
                return User::query()
                    ->with('modelSettings')
                    ->get()
                    ->sum(static fn (User $owner): int => $owner->modelSettings->count());
            }
        )
        ->toConsole();

    expect(true)->toBeTrue();
})->with([
    '100 owners with 10 defaults'   => [100, 10],
    '100 owners with 100 defaults'  => [100, 100],
    '1000 owners with 10 defaults'  => [1000, 10],
    '1000 owners with 100 defaults' => [1000, 100],
]);
