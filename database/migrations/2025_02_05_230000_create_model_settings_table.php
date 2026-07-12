<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->schema()->create($this->table(), static function (Blueprint $table) {
            $table->id();

            $table->string('item_type');

            $table->unsignedBigInteger('item_id')->nullable();
            $table->uuid('item_uuid')->nullable();
            $table->ulid('item_ulid')->nullable();

            $table->string('key');
            $table->jsonb('payload');

            $table->timestamps();
        });

        $this->uniqueIndex(['item_type', 'item_id', 'key'], 'item_id');
        $this->uniqueIndex(['item_type', 'item_uuid', 'key'], 'item_uuid');
        $this->uniqueIndex(['item_type', 'item_ulid', 'key'], 'item_ulid');
    }

    public function down(): void
    {
        $this->schema()->dropIfExists($this->table());
    }

    protected function uniqueIndex(array $columns, string $idColumn): void
    {
        $table = $this->table();
        $name  = $this->indexName($columns);
        $value = implode(',', $columns);

        DB::connection($this->connection())->statement(
            "CREATE UNIQUE INDEX $name ON $table ($value) WHERE $idColumn IS NOT NULL"
        );
    }

    protected function indexName(array $columns): string
    {
        return (new Collection($columns))
            ->prepend($this->table())
            ->push('unique')
            ->implode('_');
    }

    protected function schema(): Builder
    {
        return Schema::connection(
            $this->connection()
        );
    }

    protected function connection(): ?string
    {
        return config('model_settings.connection');
    }

    protected function table(): string
    {
        return config()->string('model_settings.table');
    }
};
