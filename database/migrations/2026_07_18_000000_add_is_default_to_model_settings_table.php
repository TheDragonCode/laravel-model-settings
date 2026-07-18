<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected const array LEGACY_UNIQUE = ['item_type', 'item_id', 'key'];
    protected const array SCOPE_UNIQUE  = ['item_type', 'item_id', 'is_default', 'key'];
    protected const array SCOPE_LOOKUP  = ['item_type', 'is_default', 'item_id'];

    public function up(): void
    {
        $schema = $this->schema();
        $table  = $this->table();

        if (! $schema->hasTable($table)) {
            throw new LogicException('The model settings table must exist before adding the default discriminator.');
        }

        $hasLegacyUnique = $schema->hasIndex($table, self::LEGACY_UNIQUE, 'unique');
        $hasScopeUnique  = $schema->hasIndex($table, self::SCOPE_UNIQUE, 'unique');

        if (! $schema->hasColumn($table, 'is_default')) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->boolean('is_default')->default(false);
            });

            $this->classifyLegacyDefaults();
        } elseif ($hasLegacyUnique && ! $hasScopeUnique) {
            $this->classifyLegacyDefaults();
        }

        if (! $hasScopeUnique) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->unique(self::SCOPE_UNIQUE);
            });
        }

        if (! $schema->hasIndex($table, self::SCOPE_LOOKUP)) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->index(self::SCOPE_LOOKUP);
            });
        }

        if ($hasLegacyUnique) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->dropUnique(self::LEGACY_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        $schema = $this->schema();
        $table  = $this->table();

        if (! $schema->hasTable($table) || ! $schema->hasColumn($table, 'is_default')) {
            return;
        }

        if ($this->hasOwnerZeroOverrides() || $this->hasLegacyIdentityCollisions()) {
            throw new LogicException(
                'The default discriminator cannot be removed while settings exist that the legacy schema cannot represent.'
            );
        }

        if (! $schema->hasIndex($table, self::LEGACY_UNIQUE, 'unique')) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->unique(self::LEGACY_UNIQUE);
            });
        }

        if ($schema->hasIndex($table, self::SCOPE_LOOKUP)) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->dropIndex(self::SCOPE_LOOKUP);
            });
        }

        if ($schema->hasIndex($table, self::SCOPE_UNIQUE, 'unique')) {
            $schema->table($table, static function (Blueprint $table): void {
                $table->dropUnique(self::SCOPE_UNIQUE);
            });
        }

        $schema->table($table, static function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }

    protected function classifyLegacyDefaults(): void
    {
        $this->database()
            ->table($this->table())
            ->where('item_id', '0')
            ->update(['is_default' => true]);
    }

    protected function hasOwnerZeroOverrides(): bool
    {
        return $this->database()
            ->table($this->table())
            ->where('item_id', '0')
            ->where('is_default', false)
            ->exists();
    }

    protected function hasLegacyIdentityCollisions(): bool
    {
        return $this->database()
            ->table($this->table())
            ->select(self::LEGACY_UNIQUE)
            ->groupBy(self::LEGACY_UNIQUE)
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    protected function schema(): Builder
    {
        return Schema::connection($this->connectionName());
    }

    protected function database(): Connection
    {
        return DB::connection($this->connectionName());
    }

    protected function connectionName(): ?string
    {
        return config('model_settings.connection');
    }

    protected function table(): string
    {
        return config()->string('model_settings.table');
    }
};
