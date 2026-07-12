<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->schema()->create($this->table(), static function (Blueprint $table) {
            $table->id();

            $table->string('item_type');
            $table->string('item_id', 36);

            $table->string('key');
            $table->jsonb('payload');

            $table->timestamps();

            $table->unique(['item_type', 'item_id', 'key']);
        });
    }

    public function down(): void
    {
        $this->schema()->dropIfExists($this->table());
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
