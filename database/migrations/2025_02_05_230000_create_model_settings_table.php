<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->connection()->create($this->table(), static function (Blueprint $table) {
            $table->id();

            $table->string('item_type');
            $table->unsignedBigInteger('item_id');

            $table->jsonb('payload');

            $table->timestamps();

            $table->unique(['item_type', 'item_id']);
        });
    }

    protected function connection(): Builder
    {
        return Schema::connection(
            config('model-settings.repositories.database.connection')
        );
    }

    protected function table(): string
    {
        return config('model-settings.repositories.database.table');
    }
};
