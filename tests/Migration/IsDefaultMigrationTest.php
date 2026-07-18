<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function defaultDiscriminatorMigration(): Migration
{
    return require dirname(__DIR__, 2) . '/database/migrations/2026_07_18_000000_add_is_default_to_model_settings_table.php';
}

function migrationTestSchema(): Builder
{
    return Schema::connection(config('model_settings.connection'));
}

function migrationTestDatabase(): Connection
{
    return DB::connection(config('model_settings.connection'));
}

function createLegacySettingsTable(string $table): void
{
    migrationTestSchema()->create($table, static function (Blueprint $table): void {
        $table->id();
        $table->string('item_type');
        $table->string('item_id', 36);
        $table->string('key');
        $table->jsonb('payload');
        $table->timestamps();
        $table->unique(['item_type', 'item_id', 'key']);
    });
}

function legacySettingsRows(): array
{
    return [
        ['item_type' => 'default-owner', 'item_id' => '0', 'key' => 'setting', 'payload' => '111'],
        ['item_type' => 'integer-owner', 'item_id' => '42', 'key' => 'setting', 'payload' => '222'],
        ['item_type' => 'uuid-owner', 'item_id' => '80a42f70-8934-4b65-a966-131bf3f63dba', 'key' => 'setting', 'payload' => '333'],
        ['item_type' => 'ulid-owner', 'item_id' => '01K0G7Z7W5G4M6B4VJHN94T8P8', 'key' => 'setting', 'payload' => '444'],
        ['item_type' => 'string-owner', 'item_id' => '00', 'key' => 'setting', 'payload' => '555'],
    ];
}

beforeEach(function (): void {
    $this->originalSettingsTable = config()->string('model_settings.table');
    $this->migrationTable        = 'settings_migration';

    config()->set('model_settings.table', $this->migrationTable);

    migrationTestSchema()->dropIfExists($this->migrationTable);
    createLegacySettingsTable($this->migrationTable);

    migrationTestDatabase()->table($this->migrationTable)->insert(legacySettingsRows());
});

afterEach(function (): void {
    migrationTestSchema()->dropIfExists($this->migrationTable);

    config()->set('model_settings.table', $this->originalSettingsTable);
});

test('upgrade classifies legacy defaults without changing stored data', function (): void {
    $columns = ['item_type', 'item_id', 'key', 'payload'];
    $before  = migrationTestDatabase()->table($this->migrationTable)->orderBy('item_type')->get($columns)->all();

    defaultDiscriminatorMigration()->up();

    $after = migrationTestDatabase()->table($this->migrationTable)->orderBy('item_type')->get($columns)->all();
    $flags = migrationTestDatabase()->table($this->migrationTable)->pluck('is_default', 'item_type');

    expect($after)->toEqual($before)
        ->and((bool) $flags['default-owner'])->toBeTrue()
        ->and((bool) $flags['integer-owner'])->toBeFalse()
        ->and((bool) $flags['uuid-owner'])->toBeFalse()
        ->and((bool) $flags['ulid-owner'])->toBeFalse()
        ->and((bool) $flags['string-owner'])->toBeFalse()
        ->and(migrationTestSchema()->hasIndex(
            $this->migrationTable,
            ['item_type', 'item_id', 'is_default', 'key'],
            'unique'
        ))->toBeTrue()
        ->and(migrationTestSchema()->hasIndex(
            $this->migrationTable,
            ['item_type', 'is_default', 'item_id']
        ))->toBeTrue()
        ->and(migrationTestSchema()->hasIndex(
            $this->migrationTable,
            ['item_type', 'item_id', 'key'],
            'unique'
        ))->toBeFalse();
});

test('discriminator indexes allow valid zero-owner pairs and reject scope duplicates', function (): void {
    defaultDiscriminatorMigration()->up();

    $row = [
        'item_type'  => 'default-owner',
        'item_id'    => '0',
        'is_default' => false,
        'key'        => 'setting',
        'payload'    => '999',
    ];

    migrationTestDatabase()->table($this->migrationTable)->insert($row);

    expect(migrationTestDatabase()->table($this->migrationTable)
        ->where('item_type', 'default-owner')
        ->where('item_id', '0')
        ->where('key', 'setting')
        ->count())->toBe(2);

    expect(fn () => migrationTestDatabase()->transaction(
        fn () => migrationTestDatabase()->table($this->migrationTable)->insert($row)
    ))->toThrow(QueryException::class);

    $row['is_default'] = true;

    expect(fn () => migrationTestDatabase()->transaction(
        fn () => migrationTestDatabase()->table($this->migrationTable)->insert($row)
    ))->toThrow(QueryException::class);
});

test('rollback restores the legacy schema when no zero-owner override exists', function (): void {
    $migration = defaultDiscriminatorMigration();

    $migration->up();
    $migration->down();

    expect(migrationTestSchema()->hasColumn($this->migrationTable, 'is_default'))->toBeFalse()
        ->and(migrationTestSchema()->hasIndex(
            $this->migrationTable,
            ['item_type', 'item_id', 'key'],
            'unique'
        ))->toBeTrue()
        ->and(migrationTestDatabase()->table($this->migrationTable)->count())->toBe(count(legacySettingsRows()));
});

test('rollback stops before schema changes when a zero-owner override exists', function (): void {
    $migration = defaultDiscriminatorMigration();

    $migration->up();

    migrationTestDatabase()->table($this->migrationTable)->insert([
        'item_type'  => 'default-owner',
        'item_id'    => '0',
        'is_default' => false,
        'key'        => 'owner-only-setting',
        'payload'    => '999',
    ]);

    expect(fn () => $migration->down())->toThrow(
        LogicException::class,
        'The default discriminator cannot be removed while settings exist that the legacy schema cannot represent.'
    );

    expect(migrationTestSchema()->hasColumn($this->migrationTable, 'is_default'))->toBeTrue()
        ->and(migrationTestSchema()->hasIndex(
            $this->migrationTable,
            ['item_type', 'item_id', 'is_default', 'key'],
            'unique'
        ))->toBeTrue()
        ->and(migrationTestSchema()->hasIndex(
            $this->migrationTable,
            ['item_type', 'item_id', 'key'],
            'unique'
        ))->toBeFalse();
});
